<?php

namespace App\Jobs;

use App\Models\client;
use App\Models\GoogleAuth;
use App\Services\GoogleService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class importFormGoogleJob implements ShouldQueue
{
    use Queueable;
    protected $googleToken;
    /**
     * Create a new job instance.
     */
    public function __construct($googleToken)
    {
        $this->googleToken=$googleToken;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $personFields=['names,emailAddresses,phoneNumbers,userDefined,organizations,biographies'];
            $pageSize=1;
            //contact come here ny the functon
            $nextPageToken=false;
            do {
                $googleContacts = (new GoogleService())->getContacts($this->googleToken, $pageSize, $personFields,$nextPageToken);
                $nextPageToken=$googleContacts->nextPageToken??false;


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
                    $crmContact = $crmContacts->get($resource);

                    if (!$crmContact) {
                        // Create new contact in CRM
                        $contact = new client();
                    } elseif ($crmContact->etag !== $googleContact['etag']) {
                        // Update existing contact
                        $contact = $crmContact;
                    } else {
                        // No change, skip
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
                    $contact->typeOfRelation = $googleContact['typeOfRelation'] ?? 'Select';
                    $contact->maritalStatus = $googleContact['maritalStatus'] ?? 'Select';
                    $contact->save();
                }
            } while ($nextPageToken);

        } catch (Exception $e) {
            Log::error("Update failed for contact ID : {$e->getMessage()}");
        }

    }
}
