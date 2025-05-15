<?php

namespace App\Jobs;

use App\Models\clientContatSyncHistory;
use App\Services\CrmApiServices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Services\GoogleService;
use Google\Service\PeopleService;
use Google\Service\PeopleService\BatchCreateContactsRequest;
use Google\Service\PeopleService\BatchUpdateContactsRequest;
use Google\Service\PeopleService\ContactToCreate;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

class pushToGoogleJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ceateDataList;
    protected $GoogleToken;
    protected $updateIdDataList;
    protected $clientSyncHistoyEmptyRowId;
    protected $apiToken;

    public function __construct($GoogleToken, $ceateIdDataList=[],$updateDataList=[],$clientSyncHistoyEmptyRowId=null,$apiToken)
    {
        $this->GoogleToken = $GoogleToken;
        $this->ceateDataList = $ceateIdDataList;
        $this->updateIdDataList = $updateDataList;
        $this->clientSyncHistoyEmptyRowId = $clientSyncHistoyEmptyRowId;
        $this->apiToken=$apiToken;

        Log::info('Before Push to Google Constructor: ' . (memory_get_usage(true)/1024/1024)." MB");

    }

    // public function handle()
    // {
    //     $syncHistory = clientContatSyncHistory::find($this->clientSyncHistoyEmptyRowId);

    //     if (!$syncHistory) {
    //         Log::error("Sync history record not found: ID {$this->clientSyncHistoyEmptyRowId}");
    //         return;
    //     }


    //     $syncHistory->status = 'Processing';
    //     $syncHistory->startTime = $syncHistory->startTime==null ? time() : $syncHistory->startTime;
    //     Log::error( "sync currnt time start".$syncHistory->startTime==null ? time() : $syncHistory->startTime);
    //     $syncHistory->save();

    //     $client = (new GoogleService())->getGoogleCient($this->GoogleToken);
    //     $peopleService = new PeopleService($client);
    //     $timeStamp = Carbon::now();

    //     if (!empty($this->ceateIdList)) {
    //         $contacts = Client::whereIn('id', $this->ceateIdList)->get();

    //         foreach ($contacts as $contact) {
    //             try {

    //                 $person = (new GoogleService())->getPerson($contact);

    //                 $created = $peopleService->people->createContact($person, [
    //                     'personFields' => 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies',
    //                 ]);

    //                 $contact->resourceName = $created->resourceName;
    //                     $contact->etag = $created->etag;
    //                     $contact->lastSync = $timeStamp;
    //                     $contact->syncStatus = 'Synced';
    //                     $contact->save();

    //                 $syncHistory->increment('createdAtGoogle');
    //                 $syncHistory->increment('synced');
    //                 $syncHistory->decrement('pending');

    //             } catch (\Exception $e) {
    //                 Log::error("Create failed for contact ID {$contact->id}: {$e->getMessage()}");
    //             }

    //             Log::info('Push to Google (create): ' . round(memory_get_usage(true) / 1024 / 1024, 2) . " MB");
    //         }
    //     }

    //     if (!empty($this->updateIdList)) {
    //         $crmContacts = Client::whereIn('id', $this->updateIdList)->get();

    //         foreach ($crmContacts as $contact) {
    //             try {
    //                 $person = (new GoogleService())->getPerson($contact);
    //                 $person->setEtag($contact->etag);

    //                 $updated = $peopleService->people->updateContact($contact->resourceName, $person, [
    //                     'updatePersonFields' => 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies',
    //                 ]);

    //                 $contact->etag = $updated->etag;
    //                 $contact->lastSync = $timeStamp;
    //                 $contact->syncStatus = 'Synced';
    //                 $contact->save();

    //                 $syncHistory->increment('updatedAtGoogle');
    //                 $syncHistory->increment('synced');
    //                 $syncHistory->decrement('pending');

    //             } catch (\Exception $e) {
    //                 Log::error("Update failed for contact ID {$contact->id}: {$e->getMessage()}");

    //                 if ($e->getCode() === 404) {
    //                     $contact->update(['syncStatus' => 'Deleted']);
    //                     Log::error("Marked as Deleted: Contact ID {$contact->id}");
    //                 }
    //             }

    //             Log::info('Push to Google (update): ' . round(memory_get_usage(true) / 1024 / 1024, 2) . " MB");
    //         }
    //     }

    //     // Optional: Final save or status update
    //     $syncHistory->decrement('batches');
    //     $syncHistory->save();
    // }

    public function handle()
    {
        sleep(5);
        $syncHistory = clientContatSyncHistory::find($this->clientSyncHistoyEmptyRowId);

        if (!$syncHistory) {
            Log::error("Sync history record not found: ID {$this->clientSyncHistoyEmptyRowId}");
            return;
        }

        $syncHistory->status = 'Processing';
        $syncHistory->startTime = $syncHistory->startTime ?? time();
        $syncHistory->save();

        $client = (new GoogleService())->getGoogleCient($this->GoogleToken);
        $peopleService = new PeopleService($client);
        $timeStamp = Carbon::now();

        // === BATCH CREATE ===
        if (!empty($this->ceateDataList)) {
            $contacts = $this->ceateDataList;
            $requests = [];
            $contactId = [];
            dump("Contas that hase been create on google");
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
                $batchCreateRequest->setReadMask('names,emailAddresses,phoneNumbers,userDefined,organizations,biographies'); ;

                try {
                    Log::info("Geting Response");
                    $response = $peopleService->people->batchCreateContacts($batchCreateRequest);
                    $response=$response->toSimpleObject();

                    foreach ($response->createdPeople as $index => $createdPerson) {
                        if (isset($contactId[$index])) {
                            $id = $contactId[$index];
                            $data=(new CrmApiServices($this->apiToken))->updateSyncStatus($id, $createdPerson->person->resourceName,$createdPerson->person->etag,'Synced');

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

            $updateMask = 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies';
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
                    dump($message);
                    $Resoucres=$message->error->details[0]->fieldViolations[0]->field;
                    $resourceName = Str::between($Resoucres, 'contacts[', ']');
                    print_r($resourceName);
                    if (isEmpty($resourceName)) {
                        $return=true;
                        $id=$updateId[$resourceName];
                        unset($updateMap[$resourceName]);
                        $updateMap=$updateMap;
                        if ($id) {
                            $data=(new CrmApiServices($this->apiToken))->updateSyncStatus($id, $createdPerson->person->resourceName,$createdPerson->person->etag,'Deleted');
                            $syncHistory->increment('deleted');
                            $syncHistory->increment('synced');
                            $syncHistory->decrement('pending');
                        }
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
                            $data=(new CrmApiServices($this->apiToken))->updateSyncStatus($id, $resourceName,$updatedPerson->getEtag(),'Synced');
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
        $syncHistory->decrement('batches');
        $syncHistory->save();

    }

}



