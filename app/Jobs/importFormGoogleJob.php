<?php

namespace App\Jobs;

use App\Models\clientContatSyncHistory;
use App\Services\CrmApiServices;
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
    protected $id;
    protected $nextSynToken;
    protected $GoogleResourceName = [];
    protected $apiToken;
    protected $data;

    public function __construct($data, $id, $apiToken)
    {
        $this->id = $id;
        $this->apiToken = $apiToken;
        $this->data = $data;
    }
    public function handle(): void
    {
        sleep(5);
        try {
            $timeStamp = Carbon::now();

            //contact come here ny the functon
            $lastRowInContactSyncHistoryTable = clientContatSyncHistory::where('id', $this->id)->first();
            $lastRowInContactSyncHistoryTable->status = "Processing";
            $lastRowInContactSyncHistoryTable->startTime = $lastRowInContactSyncHistoryTable->startTime == null ? time() : $lastRowInContactSyncHistoryTable->startTime;
            $lastRowInContactSyncHistoryTable->save();

            $contacts = [];
            foreach ($this->data as $person) {
                $resourceName = $person->resourceName;
                $etag = $person->etag;
                $this->GoogleResourceName[] = $resourceName;

                $name = $person->names[0] ?? null;
                $emails = collect($person->emailAddresses ?? []);
                $phones = collect($person->phoneNumbers ?? []);
                $biographies = $person->biographies[0]->value ?? '';
                $organizations = $person->organizations[0] ?? null;
                $addresses = collect($person->addresses ?? []);
                $urls = collect($person->urls ?? []); // no files are avalable
                $relations = collect($person->relations ?? []); // no files are avalable
                // birth Date
                $birthDate = $person->birthdays[0]->date ?? "";
                $day = $birthDate->day ?? "";
                $month = $birthDate->month ?? "";
                $year = $birthDate->year ?? "";

                $userDefined = collect($person->userDefined ?? []);
                $arrayKeys = [
                    'pan' => 'pancard_c',
                    'pancard ' => 'pancard_c',
                    'pan card' => 'pancard_c',
                    'pan card no' => 'pancard_c',
                    'pan card number' => 'pancard_c',
                    'pen' => 'pancard_c',
                    'aadhar' => 'adhaar_card_c',
                    'aadharno' => 'adhaar_card_c',
                    'aadhar no' => 'adhaar_card_c',
                    'adhar' => 'adhaar_card_c',
                    'adhar no' => 'adhaar_card_c',
                    'aadhaar card' => 'adhaar_card_c',
                    'adhaar card' => 'adhaar_card_c'
                ];
                $userDefindArray = [];
                foreach ($userDefined as $data) {

                    $key = strtolower($data->key);
                    $newKey = $arrayKeys[$key] ?? null;
                    $value = $data->value;
                    if (!empty($newKey)) {
                        $userDefindArray[$newKey] = $value;
                    }
                }


                $contacts[$resourceName] = [
                    'rest_data' => [
                        'module_name' => 'Contact',
                        'name_value_list' => [
                            'first_name' => $name->givenName ?? '',
                            'last_name' => $name->familyName ?? '',
                            'company_name' => $organizations->name ?? '',   // No Feilds to store // name of company
                            'occupation_c' => $organizations->title ?? '',
                            // 'designation' => $organizations->title ?? '',
                            'birth_date' => "$year/$month/$day",
                            // 'anniversary' => '',
                            'urls_c' => $urls->map(function ($url) {           ///  No data fields in Crm To store this
                                return [
                                    'type' => $url->formattedType ?? $url->type ?? '',
                                    'value' => $url->value ?? '',
                                    'primary' => $url->metadata->primary ?? false,
                                ];
                            })->values()->all(),
                            'relations_c' => $relations->map(function ($relation) {        ///  No data fields in Crm To store this
                                return [
                                    'type' => $relation->formattedType ?? $relation->type ?? '',
                                    'person' => $relation->person ?? '',
                                    'primary' => $relation->metadata->primary ?? '',
                                ];
                            })->values()->all(),
                            'customer_type' => '',
                            'hiddenPhone' => $phones->map(function ($phone) {
                                return [
                                    // $phone->formattedType??'';    // no Avalable filed in crm to Store this
                                    'phone_number' => $phone->canonicalForm ?? preg_replace('/[^0-9]/', '', $phone->value ?? 0) ?? '',
                                    'verified_at' => '',
                                    'unsubscribed' => false,
                                    'invalid' => false,
                                    'primary' => $phone->metadata->primary ?? false,
                                ];
                            })->values()->all(),
                            'hiddenEmail' => $emails->map(function ($email) {
                                return [
                                    //  $email->type ?? '';       // No Avalable field in crm to Store
                                    'email_address' => $email->value ?? '',
                                    'primary' => $email->metadata->primary ?? false,
                                    'status' => 'invalid',
                                    'suppression' => $email->value ?? null,
                                    'verified_at' => '',
                                ];
                            })->values()->all(),
                            'hiddenAddress' => $addresses->map(function ($address) {
                                return [
                                    'table_name'      => 'addresses',
                                    'related_table_name' => 'addresses_rel',
                                    'address_type'    => $address->type ?? '',
                                    'street'          => $address->streetAddress ?? '',
                                    'area'            => "",
                                    'city'            => $address->city ?? '',
                                    'state'           => $address->region ?? '',
                                    'country'         => $address->country ?? '',
                                    'postal_code'     => $address->postalCode ?? '',
                                    'primary'         => $address->metadata->primary ?? false,
                                    'verified_at'     => null,
                                ];
                            })->values()->all(),
                            'comment' => $biographies ? [['description' => $biographies]] : [],
                            //Data From User Defincd Fields
                            'pancard_c'=>$userDefindArray['pancard_c']??null,
                            'adhaar_card_c'=>$userDefindArray['adhaar_card_c']??null,
                            'etag_c' => $etag,
                            'resource_name_c' => $resourceName,
                            'sync_status_c' => 'Synced',
                            'last_sync_c' => $timeStamp,
                            // 'assigned_user_id'=>'',
                            // 'duration_c' => '',
                            // 'hierarchy' => '',
                            // 'department' => '',
                            // 'lead_source' => '',
                            // 'teamsSet' => ''
                        ]
                    ]
                ];
            }

            $existingData = (new CrmApiServices($this->apiToken))->getExistingDataFromCrm($this->GoogleResourceName);
            foreach ($contacts as $resource => $payload) {
                try {
                    // this for captur data in Sync History Table
                    $lastRowInContactSyncHistoryTable = clientContatSyncHistory::where('id', $this->id)->first();
                    $lastRowInContactSyncHistoryTable->synced += 1;
                    $lastRowInContactSyncHistoryTable->pending = $lastRowInContactSyncHistoryTable->pending == 0 ? 0 : $lastRowInContactSyncHistoryTable->pending - 1;

                    if (!array_key_exists($resource, $existingData)) {
                        // Create new contact in CRM
                        if (!empty($payload['rest_data']['name_value_list']['first_name']) || !empty($payload['rest_data']['name_value_list']['last_name'])) {
                            (new CrmApiServices($this->apiToken))->createContact($payload);

                            // this for captur data in Sync History Table
                            $lastRowInContactSyncHistoryTable->created += 1;
                        }
                        $lastRowInContactSyncHistoryTable->save();
                        continue;
                    } elseif ($existingData[$resource]['etag'] !== $payload['rest_data']['name_value_list']['etag_c']) {

                        if (empty($payload['rest_data']['name_value_list']['first_name']) && empty($payload['rest_data']['name_value_list']['last_name'])) {
                            (new CrmApiServices($this->apiToken))->updateSyncStatus($existingData[$resource]['id'], $resource, $payload['rest_data']['name_value_list']['etag_c'], "Deleted");
                            $lastRowInContactSyncHistoryTable->deleted += 1;
                            $lastRowInContactSyncHistoryTable->save();
                            continue;
                        }

                        (new CrmApiServices($this->apiToken))->updateContact($existingData[$resource]['id'], $payload);
                        // this for captur data in Sync History Table
                        $lastRowInContactSyncHistoryTable->updated += 1;
                        $lastRowInContactSyncHistoryTable->save();
                        continue;
                    } else {
                        // No change, skip
                        // this for captur data in Sync History Table
                        dump("Skipng No update Found");
                        $lastRowInContactSyncHistoryTable->save();
                        continue;
                    }
                } catch (\Throwable $th) {
                    Log::info(' error message in Import From google Job ' . $th);
                }
            }
        } catch (Exception $e) {
            Log::error("Update failed for contact ID : {$e->getMessage()}");
        }
    }
}
