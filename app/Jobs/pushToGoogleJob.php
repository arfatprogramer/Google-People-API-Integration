<?php

namespace App\Jobs;

use App\Models\clientContatSyncHistory;
use App\Services\CrmApiServices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\GoogleService;
use Google\Service\PeopleService;
use Google\Service\PeopleService\BatchCreateContactsRequest;
use Google\Service\PeopleService\BatchUpdateContactsRequest;
use Google\Service\PeopleService\ContactToCreate;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class pushToGoogleJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $createDataList;
    protected $GoogleToken;
    protected $updateIdDataList;
    protected $syncHistoryEmptyRowId;
    protected $apiToken;

    public function __construct($GoogleToken, $createDataList=[],$updateDataList=[],$syncHistoryEmptyRowId=null,$apiToken)
    {
        $this->GoogleToken = $GoogleToken;
        $this->createDataList = $createDataList;
        $this->updateIdDataList = $updateDataList;
        $this->syncHistoryEmptyRowId = $syncHistoryEmptyRowId;
        $this->apiToken=$apiToken;

    }

    public function handle()
    {
        sleep(5);
        $syncHistory = clientContatSyncHistory::find($this->syncHistoryEmptyRowId);

        if (!$syncHistory) {
            Log::error("Sync history record not found: ID {$this->syncHistoryEmptyRowId}");
            return;
        }

        $syncHistory->status = 'Processing';
        $syncHistory->startTime = $syncHistory->startTime ?? time();
        $syncHistory->save();

        $client = (new GoogleService())->getGoogleCient($this->GoogleToken);
        $peopleService = new PeopleService($client);

        // === BATCH CREATE ===
        if (!empty($this->createDataList)) {
            $contacts = $this->createDataList;
            $requests = [];
            $contactId = [];
            foreach ($contacts as $index => $contact) {
                try {
                    $person = (new GoogleService())->getPerson($contact);

                    $contactToCreate = new ContactToCreate();
                    $contactToCreate->setContactPerson($person);

                    $requests[] = $contactToCreate;
                    $contactId[$index] = $contact->id;

                } catch (\Exception $e) {
                    Log::error("Error preparing create for contact ID {$contact->id}: {$e->getMessage()}");
                }
            }

            if (!empty($requests)) {
                $batchCreateRequest = new BatchCreateContactsRequest();
                $batchCreateRequest->setContacts($requests);
                $batchCreateRequest->setReadMask('names,emailAddresses,phoneNumbers'); ;

                try {
                    Log::info("Geting Response");
                    $response = $peopleService->people->batchCreateContacts($batchCreateRequest);
                    $response=$response->toSimpleObject();

                    foreach ($response->createdPeople as $index => $createdPerson) {
                        if (isset($contactId[$index])) {
                            $id = $contactId[$index];
                            $data=(new CrmApiServices($this->apiToken))->updateSyncStatus($id, $createdPerson->person->resourceName,$createdPerson->person->etag,'Synced');
                            // dump($data);
                            $syncHistory->increment('createdAtGoogle');
                            $syncHistory->increment('synced');
                            $syncHistory->decrement('pending');
                        }
                    }
                } catch (\Exception $e) {
                    $message=json_decode($e->getMessage());
                    $errorCode=$message->error->code;
                    if ($errorCode==429) {
                        $syncHistory->error +=count($contactId);
                        $syncHistory->decrement('pending');
                    }
                }
            }
        }

        // === BATCH UPDATE ===
        if (!empty($this->updateIdDataList)) {
            $contacts =$this->updateIdDataList;

            $batchUpdateRequest = new BatchUpdateContactsRequest();
            $updateMap = [];
            $updateId = [];

            $updateMask = 'names,phoneNumbers,emailAddresses,userDefined,organizations,biographies,addresses,birthdays,urls,relations';
            $readMask = 'names,emailAddresses,phoneNumbers';

            foreach ($contacts as  $index => $contact) {
                try {
                    $person = (new GoogleService())->getPerson($contact);
                    $person->setEtag($contact->etag_c); // Ensure etag is fresh before batch
                    $updateMap[$contact->resource_name_c] = $person;
                    $updateId[$contact->resource_name_c] = $contact->id;

                } catch (\Exception $e) {
                    Log::error("Error preparing update for contact ID {$contact->id}: {$e->getMessage()}");
                }
            }

            $return=false;
            do {
                $return=false;
                try {
                    $batchUpdateRequest->setContacts($updateMap);
                    $batchUpdateRequest->setUpdateMask($updateMask);
                    $batchUpdateRequest->setReadMask($readMask);
                    $response = $peopleService->people->batchUpdateContacts($batchUpdateRequest);
                } catch (\Exception $e) {

                    $message=json_decode($e->getMessage());
                    $Resoucres=$message->error->details[0]->fieldViolations[0]->field;
                    $resourceName = Str::between($Resoucres, 'contacts[', ']');
                    if (!empty($resourceName)) {
                        $return=true;
                        sleep(1);
                        $id=$updateId[$resourceName];
                        unset($updateMap[$resourceName]);
                        $updateMap=$updateMap;
                        if ($id) {
                            (new CrmApiServices($this->apiToken))->updateSyncStatus($id,null,null,'Deleted');
                            $syncHistory->increment('deleted');
                            $syncHistory->increment('synced');
                            $syncHistory->decrement('pending');
                        }
                    }else{
                        dump($message);
                    }
                }
            } while ($return);

            try {
                $results = $response->getUpdateResult(); // Must call getter method

                foreach ($results as $resourceName => $updateResult) {
                    $updatedPerson = $updateResult->getPerson();
                    if ($updatedPerson) {
                        $id = $updateId[$resourceName];
                        if ($id) {
                            (new CrmApiServices($this->apiToken))->updateSyncStatus($id, $resourceName,$updatedPerson->getEtag(),'Synced');
                            $syncHistory->increment('updatedAtGoogle');
                            $syncHistory->increment('synced');
                            $syncHistory->decrement('pending');
                        } else {
                            Log::warning("No local contact found for resourceName: {$resourceName}");
                        }
                    } else {
                        Log::warning("No updated person returned for resourceName: {$resourceName}");
                    }
                }

            } catch (\Exception $e) {
                Log::error("Batch update failed: {$e->getMessage()}");
            }
        }

        // Finalize
        $syncHistory->save();

    }

}



