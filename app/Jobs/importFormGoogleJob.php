<?php

namespace App\Jobs;

use App\Models\clientContatSyncHistory;
use App\Services\CrmApiServices;
use App\Services\GoogleService;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class importFormGoogleJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $googleToken;
    protected $id;
    protected $nextSynToken;
    protected $GoogleResourceName=[];
    protected $apiToken;
    /**
     * Create a new job instance.
     */
    public function __construct($googleToken,$id,$apiToken)
    {

        $this->googleToken=$googleToken;
        $this->id=$id;
        $this->apiToken=$apiToken;

        $previousSync = clientContatSyncHistory::orderByDesc('id')->skip(1)->first();
        $this->nextSynToken = $previousSync ? $previousSync->synToken : null;

        Log::info("Import Form Google Starting Job constructor return: $this->nextSynToken " . (memory_get_usage(true)/1024/1024)." MB");
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {    sleep(5);
        try {
         $timeStamp=Carbon::now();
            $personFields=['names,phoneNumbers,emailAddresses,userDefined,organizations,biographies,addresses,birthdays'];
            $pageSize=1000;
            //contact come here ny the functon

            $nextPageToken=false;
            do {
                $googleContacts = (new GoogleService())->getContacts($this->googleToken, $pageSize, $personFields,$nextPageToken, $this->nextSynToken);
                $nextPageToken=$googleContacts->nextPageToken??false;

                //this writen to update contact History Table Data

                $lastRowInContactSyncHistoryTable=clientContatSyncHistory::where('id',$this->id)->first();
                $lastRowInContactSyncHistoryTable->synToken=$googleContacts->nextSyncToken??null;
                $lastRowInContactSyncHistoryTable->batches -=1;
                $lastRowInContactSyncHistoryTable->status ="Processing";
                $lastRowInContactSyncHistoryTable->startTime = $lastRowInContactSyncHistoryTable->startTime==null ? time() : $lastRowInContactSyncHistoryTable->startTime;
                $lastRowInContactSyncHistoryTable->save();


                if (empty($googleContacts->connections)) {
                    continue;
                }
                $contacts = [];
                foreach ($googleContacts->connections as $person) {
                    $resourceName = $person->resourceName;
                    $etag = $person->etag;
                    $this->GoogleResourceName[]=$resourceName;

                    $name = $person->names[0] ?? null;
                    $emails = collect($person->emailAddresses ?? []);
                    $phones = collect($person->phoneNumbers ?? []);
                    $biographies = $person->biographies[0]->value ?? '';
                    $organizations = $person->organizations[0] ?? null;
                    $addresses = collect($person->addresses ?? []);

                    $contacts[$resourceName] = [
                        'rest_data' => [
                            'module_name' => 'Contact',
                            'name_value_list' => [
                                'first_name' => $name->givenName ?? '',
                                'last_name' => $name->familyName ?? '',
                                'designation' => $organizations->title ?? '',
                                'birth_date' => '',
                                'anniversary' => '',
                                'customer_type' => '',
                                'hiddenPhone' => $phones->map(function ($phone) {
                                    return [
                                        'phone_number' => $phone->value ?? '',
                                        'verified_at' => '',
                                        'unsubscribed' => false,
                                        'invalid' => false,
                                        'primary' => $phone->metadata->primary ?? false,
                                    ];
                                })->values()->all(),
                                'hiddenEmail' => $emails->map(function ($email) {
                                    return [
                                        'email_address' => $email->value ?? '',
                                        'primary' => $email->metadata->primary ?? false,
                                        'status' => 'invalid',
                                        'suppression' => $email->value ?? null,
                                        'verified_at' => '',
                                    ];
                                })->values()->all(),
                                'hiddenAddress' => $addresses->map(function ($address) {
                                    return [
                                        'street' => $address->streetAddress ?? '',
                                        'city' => $address->city ?? '',
                                        'region' => $address->region ?? '',
                                        'postal_code' => $address->postalCode ?? '',
                                        'country' => $address->country ?? '',
                                        'type' => $address->type ?? '',
                                        'primary' => $address->metadata->primary ?? false,
                                    ];
                                })->values()->all(),
                                'comment' => $biographies ? [['description' => $biographies]] : [],
                                'etag_c'=>$etag,
                                'resource_name_c'=>$resourceName,
                                'sync_status_c'=>'Synced',
                                'last_sync_c'=>$timeStamp,
                                'assigned_user_id'=>1,
                                'duration_c' => '12:00 AM',
                                'hierarchy' => '',
                                'department' => '',
                                'lead_source' => '',
                                'teamsSet' => '1'
                            ]
                        ]
                    ];
                }

                $existingData = (new CrmApiServices($this->apiToken))->getExistingDataFromCrm($this->GoogleResourceName);
                foreach ($contacts as $resource => $payload) {
                    try {
                        // this for captur data in Sync History Table
                        $lastRowInContactSyncHistoryTable=clientContatSyncHistory::where('id',$this->id)->first();
                        $lastRowInContactSyncHistoryTable->synced+=1;
                        $lastRowInContactSyncHistoryTable->pending=$lastRowInContactSyncHistoryTable->pending==0?0:$lastRowInContactSyncHistoryTable->pending - 1;

                        if ( !array_key_exists($resource, $existingData)) {
                            // Create new contact in CRM
                            if (!empty($payload['rest_data']['name_value_list']['first_name']) || !empty($payload['rest_data']['name_value_list']['last_name'])) {
                               $data=(new CrmApiServices($this->apiToken))->createContact($payload);
                                // this for captur data in Sync History Table
                                $lastRowInContactSyncHistoryTable->created+=1;
                            }
                            $lastRowInContactSyncHistoryTable->save();
                            continue;

                        } elseif ( $existingData[$resource]['etag'] !== $payload['rest_data']['name_value_list']['etag_c']) {

                            if (empty($payload['rest_data']['name_value_list']['first_name']) && empty($payload['rest_data']['name_value_list']['last_name'])) {
                                (new CrmApiServices($this->apiToken))->updateSyncStatus( $existingData[$resource]['id'],$resource, $payload['rest_data']['name_value_list']['etag_c'],"Deleted");
                                $lastRowInContactSyncHistoryTable->deleted+=1;
                                $lastRowInContactSyncHistoryTable->save();
                                continue;
                            }

                            (new CrmApiServices($this->apiToken))->updateContact($existingData[$resource]['id'],$payload);
                            // this for captur data in Sync History Table
                            $lastRowInContactSyncHistoryTable->updated+=1;
                            $lastRowInContactSyncHistoryTable->save();
                            continue;
                        }else {
                            // No change, skip
                            // this for captur data in Sync History Table
                            dump("Skipng No update Found");
                            $lastRowInContactSyncHistoryTable->save();
                            continue;
                        }

                    } catch (\Throwable $th) {
                        Log::info(' error message in Import From google Job ' .$th );

                    }
                }
                Log::info(' Import Form Google loop Job: ' . (memory_get_usage(true)/1024/1024)." MB");
            } while ($nextPageToken);

        } catch (Exception $e) {
            Log::error("Update failed for contact ID : {$e->getMessage()}");
        }

    }
}
