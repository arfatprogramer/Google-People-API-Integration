<?php

namespace App\Jobs;

use App\Models\client;
use App\Models\clientContatSyncHistory;
use App\Services\GoogleService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class importFormGoogleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $googleToken;
    protected $id;
    protected $nextSynToken;
    /**
     * Create a new job instance.
     */
    public function __construct($googleToken,$id)
    {

        $this->googleToken=$googleToken;
        $this->id=$id;

        $previousSync = clientContatSyncHistory::orderByDesc('id')->skip(1)->first();
        $this->nextSynToken = $previousSync ? $previousSync->synToken : null;

        Log::info("Import Form Google Starting Job constructor return: $this->nextSynToken " . (memory_get_usage(true)/1024/1024)." MB");
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {   Log::info(' Import Form Google Starting Job: ' . (memory_get_usage(true)/1024/1024)." MB");
    try {
            $personFields=['names,emailAddresses,phoneNumbers,userDefined,organizations,biographies'];
            $pageSize=1000;
            //contact come here ny the functon


            $nextPageToken=false;
            do {
                $googleContacts = (new GoogleService())->getContacts($this->googleToken, $pageSize, $personFields,$nextPageToken, $this->nextSynToken);
                $nextPageToken=$googleContacts->nextPageToken??false;

                //this writen to update contact History Table Data
                Log::info(' error message in Import From google Job  before histoy table '  );

                $lastRowInContactSyncHistoryTable=clientContatSyncHistory::where('id',$this->id)->first();
                $lastRowInContactSyncHistoryTable->synToken=$googleContacts->nextSyncToken??null;
                $lastRowInContactSyncHistoryTable->batches -=1;
                $lastRowInContactSyncHistoryTable->status ="Processing";
                $lastRowInContactSyncHistoryTable->startTime = $lastRowInContactSyncHistoryTable->startTime==null ? time() : $lastRowInContactSyncHistoryTable->startTime;
                $lastRowInContactSyncHistoryTable->save();

                if (empty($googleContacts->connections)) {
                    continue;
                }


                $googleMap = collect($googleContacts->connections)->mapWithKeys(function ($person) {

                    $customFields = collect($person->userDefined??[])->mapWithKeys(function ($field) {
                        return [$field->key => $field->value];
                    });
                    return [
                        $person->resourceName => [
                            'etag' => $person->etag,
                            'firstName' => $person->names[0]->givenName ?? null,
                            'lastName' => $person->names[0]->familyName ?? null,
                            'number' => $person->phoneNumbers[0]->value ?? null,
                            'email' => $person->emailAddresses[0]->value ?? null,
                            'familyOrOrgnization' => $person->organizations[0]->name ?? null,
                            'panCardNumber' => $customFields['panCardNumber'] ?? null,
                            'aadharCardNumber' => $customFields['aadharCardNumber'] ?? null,
                            'occupation' => $customFields['occupation'] ?? 'Select',
                            'kycStatus' => $customFields['kycStatus'] ?? 'Select',
                            'anulIncome' => $customFields['anulIncome'] ?? null,
                            'referredBy' => $customFields['referredBy'] ?? null,
                            'totalInvestment' => $customFields['totalInvestment'] ?? null,
                            'comments' => $person->biographies[0]->value ?? null,
                            'relationshipManager' => $customFields['relationshipManager'] ?? null,
                            'serviceRM' => $customFields['serviceRM'] ?? null,
                            'totalSIP' => $customFields['totalSIP'] ?? null,
                            'primeryContactPerson' => $customFields['primeryContactPerson'] ?? null,
                            'meetinSchedule' => $customFields['meetinSchedule'] ?? 'Select',
                            'firstMeetingDate' => $customFields['firstMeetingDate'] ?? null,
                            'typeOfRelation' => $customFields['typeOfRelation'] ?? 'Select',
                            'maritalStatus' => $customFields['maritalStatus'] ?? 'Select',
                        ]
                    ];
                });

                $crmContacts = client::whereNotNull('resourceName')->select(['id', 'resourceName', 'etag'])->get()->keyBy('resourceName');

                foreach ($googleMap as $resource => $googleContact) {
                    try {

                        $crmContact = $crmContacts->get($resource);
                        // this for captur data in Sync History Table
                        $lastRowInContactSyncHistoryTable=clientContatSyncHistory::where('id',$this->id)->first();
                        $lastRowInContactSyncHistoryTable->synced+=1;
                        $lastRowInContactSyncHistoryTable->pending=$lastRowInContactSyncHistoryTable->pending==0?0:$lastRowInContactSyncHistoryTable->pending+1;

                        if (!$crmContact) {
                            // Create new contact in CRM
                            $contact = new client();
                            // this for captur data in Sync History Table
                            $lastRowInContactSyncHistoryTable->created+=1;
                            $lastRowInContactSyncHistoryTable->save();
                        } elseif ($crmContact->etag !== $googleContact['etag']) {
                            // Update existing contact
                            $contact = $crmContact;

                            if (empty($googleContact['firstName']) && empty($googleContact['lastName']) && empty($googleContact['number']) && empty($googleContact['email'])) {
                                $contact->syncStatus = 'Deleted';
                                $contact->lastSync = Carbon::now();
                                $contact->save();
                                $lastRowInContactSyncHistoryTable->deleted+=1;
                                $lastRowInContactSyncHistoryTable->save();
                                continue;
                            }
                            // this for captur data in Sync History Table
                            $lastRowInContactSyncHistoryTable->updated+=1;
                            $lastRowInContactSyncHistoryTable->save();
                        }else {
                            // No change, skip
                            // this for captur data in Sync History Table
                            $lastRowInContactSyncHistoryTable->save();
                            continue;
                        }

                        // Common assignment for both Create and Update
                        $contact->firstName = $googleContact['firstName'] ?? null;
                        $contact->lastName = $googleContact['lastName'] ?? null;
                        $contact->number = $googleContact['number'] ?? null;
                        $contact->email = $googleContact['email'] ?? null;
                        $contact->resourceName = $resource;
                        $contact->etag = $googleContact['etag'];

                        // Fill additional CRM fields with defaults or nulls
                        $contact->familyOrOrgnization = $googleContact['familyOrOrgnization'] ?? null;
                        $contact->panCardNumber = $googleContact['panCardNumber'] ?? null;
                        $contact->aadharCardNumber = $googleContact['aadharCardNumber'] ?? null;
                        $contact->occupation = $googleContact['occupation'] ?? 'Select';
                        $contact->kycStatus = $googleContact['kycStatus'] ?? 'Select';
                        $contact->anulIncome = $googleContact['anulIncome'] ?? null;
                        $contact->referredBy = $googleContact['referredBy'] ?? null;
                        $contact->totalInvestment = $googleContact['totalInvestment'] ?? null;
                        $contact->comments = $googleContact['comments'] ?? null;
                        $contact->relationshipManager = $googleContact['relationshipManager'] ?? null;
                        $contact->serviceRM = $googleContact['serviceRM'] ?? null;
                        $contact->totalSIP = $googleContact['totalSIP'] ?? null;
                        $contact->primeryContactPerson = $googleContact['primeryContactPerson'] ?? null;
                        $contact->meetinSchedule = $googleContact['meetinSchedule'] ?? 'Select';
                        $contact->firstMeetingDate = $googleContact['firstMeetingDate'] ?? null;
                        $contact->typeOfRelation = $googleContact['typeOfRelation'] ?? null;
                        $contact->maritalStatus = $googleContact['maritalStatus'] ?? null;
                        $contact->syncStatus = 'Synced';
                        $contact->lastSync = Carbon::now();
                        $contact->save();
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
