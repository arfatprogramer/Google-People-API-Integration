<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\clientContatSyncHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Services\GoogleService;
use Google\Service\PeopleService;
use Illuminate\Support\Facades\Log;
class pushToGoogleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ceateIdList;
    protected $GoogleToken;
    protected $updateIdList;
    protected $clientSyncHistoyEmptyRowId;

    public function __construct($GoogleToken, $ceateIdList=[],$updateIdList=[],$clientSyncHistoyEmptyRowId=null)
    {
        $this->GoogleToken = $GoogleToken;
        $this->ceateIdList = $ceateIdList;
        $this->updateIdList = $updateIdList;
        $this->clientSyncHistoyEmptyRowId = $clientSyncHistoyEmptyRowId;

        Log::info('Before Push to Google Constructor: ' . (memory_get_usage(true)/1024/1024)." MB");

    }



    public function handle()
    {
        $lastRowInContactSyncHistoryTable=clientContatSyncHistory::where('id',$this->clientSyncHistoyEmptyRowId)->first();

        $lastRowInContactSyncHistoryTable->batches -=1;
        $lastRowInContactSyncHistoryTable->status =1;
        $lastRowInContactSyncHistoryTable->status = $lastRowInContactSyncHistoryTable->status==null ? time() : $lastRowInContactSyncHistoryTable->status;
        $lastRowInContactSyncHistoryTable->save();

        $client = (new GoogleService())->getGoogleCient($this->GoogleToken);
        $peopleService = new PeopleService($client);
        $timeStamp = Carbon::now();

        if (!empty($this->ceateIdList)) {
            $contacts = Client::whereIn('id', $this->ceateIdList)->get();
            foreach ($contacts as $contact) {
                try {
                    $person = (new GoogleService())->getPerson($contact);
                    $created = $peopleService->people->createContact($person, [
                        'personFields' => 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies',
                    ]);

                        $contact->resourceName = $created->resourceName;
                        $contact->etag = $created->etag;
                        $contact->lastSync = $timeStamp;
                        $contact->syncStatus = 'Synced';
                        $contact->save();

                    //  Update cache (key can include user/session if needed)

                } catch (\Exception $e) {

                    Log::error("Create failed for contact ID {$contact->id}");
                }
                Log::info(' Push to Google loop create: ' . (memory_get_usage(true)/1024/1024)." MB");
            }
        }

        if (!empty($this->updateIdList)) {
            //Modify according to prevent un neseeory API requests
            $crmContacts = Client::whereIn('id', $this->updateIdList)->get();
            foreach ($crmContacts as $contact) {
                try {

                    //This will come By Getperson Function
                    $person=(new GoogleService())->getPerson($contact);
                    $person->setEtag($contact->etag);
                    $updated = $peopleService->people->updateContact($contact->resourceName, $person, [
                        'updatePersonFields' => 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies',
                    ]);

                    $contact->etag = $updated->etag;
                    $contact->lastSync = $timeStamp;
                    $contact->syncStatus = 'Synced';
                    $contact->save();
                } catch (\Exception $e) {

                    Log::error("Update failed for contact ID {$contact->id}: {$e->getMessage()}");
                    $ErrorCode=$e->getCode();
                    if ($ErrorCode==404) { //404  Requested entity was not found
                        $contact->syncStatus = 'Deleted';
                        $contact->save();
                        Log::error("Deleted save");
                    }
                }
                Log::info(' Push to Google loop Update: ' . (memory_get_usage(true)/1024/1024)." MB");
            }
        }
    }
}
