<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\client;
use App\Models\GoogleAuth;
use Carbon\Carbon;
use DateTime;
use Exception;
use Google_Client;
use Google\Service\PeopleService;
use Google\Service\PeopleService\Person;
use Laravel\Socialite\Facades\Socialite;
use PhpParser\Node\Stmt\TryCatch;

class googleSyncController extends Controller
{
    public function index()
    {
        try {
            // CRM data operation
            $totalClients = client::count();
            $pendingForSync = Client::whereNull('resourceName')->count();
            $pendingForUpdate = Client::whereColumn('updated_at', '>', 'updateFlag')->count();
            $pending = $pendingForSync + $pendingForUpdate;
            $synchData = $totalClients - $pending;

            $crmData = [
                'total' => $totalClients,
                'pending' => $pending,
                'sync' => $synchData,
            ];

            //Google realted Operaton
            $GoogleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();
            //If table is empty then sign in user
            if (!$GoogleToken || $GoogleToken == null) {
                return redirect()->route('client.redirect');
            }

            //if access token Expire then Regenrate
            $created = Carbon::parse($GoogleToken->updated_at)->timestamp;
            $expireIn = $GoogleToken->accessTokenExpiresIn;

            if ($created + $expireIn < time()) {
                // get new Token
                $this->refreshAccessToken($GoogleToken);
            } else {
                //Get Google Contact
                $contacts = $this->getContacts($GoogleToken);
                $googleClient = $contacts->totalPeople;
                $googlePending = $googleClient - $synchData;
                $googlesync = $synchData;

                //Pass that data to view
                $googleContact = [
                    'total' => $googleClient,
                    'pending' => $googlePending,
                    'sync' => $googlesync,
                ];
                return view('client.googleSync', ['contacts' => [], 'googleContact' => $googleContact, 'crmData' => $crmData]);
            }
            // some Return
            return redirect()->route('client.sync');
        } catch (Exception $e) {
            dd($e);
        }
    }


    //genrate google and people client
    public function getGoogleCient($googleToken)
    {
        $client = new Google_Client();
        $client->setAccessToken([
            'access_token' => $googleToken->googleAccessToken,
        ]);
        // this this not nessory for every things
        $client->addScope([
            'https://www.googleapis.com/auth/contacts',
            'email',
            'profile'
        ]);
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));


        return ($client);
    }

    // get contact list ushing access token
    public function  getContacts($googleToken): object
    {
        try {

            $client = $this->getGoogleCient($googleToken);
            $peopleService = new PeopleService($client);

            $response = $peopleService->people_connections->listPeopleConnections(
                'people/me',
                [
                    'pageSize' => 100,
                    'personFields' => 'names,emailAddresses,phoneNumbers',
                    // 'requestSyncToken' => true,
                ]
            );
            return $response->toSimpleObject();
        } catch (Exception $e) {
            return ($e);
        }
    }

    // to Genrate new Access token using Refresh token
    public function refreshAccessToken($googleToken)
    {
        try {
            $refreshToken = $googleToken->googleRefreshToken;
            $client = $this->getGoogleCient($googleToken);
            $accessToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);

            // herer we will update the data of current user
            $user = new GoogleAuth;
            $user->google_id = $googleToken->id;
            $user->name = $googleToken->name;
            $user->email = $googleToken->email;

            // this will come form google
            $user->googleAccessToken = $accessToken['access_token'];
            $user->accessTokenExpiresIn = $accessToken['expires_in'];
            $user->googleRefreshToken = $accessToken['refresh_token'];
            $user->refreshTokenExpiresIn = $accessToken['refresh_token_expires_in'];
            $user->save();

            return redirect()->route('client.sync')->with('success', 'Rifresh tokne Genrated successfully.');
        } catch (Exception $e) {
            dd($e);
        }
    }

    // aut2.0 authenticating
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/contacts',
                'email',
                'profile'
            ])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    //auth 2.0 authonticatin redirect handel function
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();

        //  save token and user info however you need
        $user = new GoogleAuth;
        $user->google_id = $googleUser->id;
        $user->name = $googleUser->name;
        $user->email = $googleUser->email;
        $user->googleAccessToken = $googleUser->token;
        $user->accessTokenExpiresIn = $googleUser->expiresIn;
        $user->googleRefreshToken = $googleUser->refreshToken;
        $user->refreshTokenExpiresIn = $googleUser->expiresIn;
        $user->save();

        return redirect()->route('client.sync')->with('success', 'Google account connected successfully.');
    }


    //2 Way google synch function CRM <=> Google contact

    public function syncGoogleContacts()
    {
        try {
            //code...

            $GoogleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();
            $client = $this->getGoogleCient($GoogleToken);
            $peopleService = new PeopleService($client);


            // 1. Get Google contacts
            $googleContacts = $peopleService->people_connections->listPeopleConnections(
                'people/me',
                ['personFields' => 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies']
            )->getConnections();

            $googleMap = collect($googleContacts)->mapWithKeys(function ($person) {
                $customFields = collect($person->getUserDefined())->mapWithKeys(function ($field) {
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




            // 2. Load CRM contacts with resource_name
            $crmContacts = client::whereNotNull('resourceName')->get()->keyBy('resourceName');

            // 3. Compare and Sync
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

            $this->createContactToGoogle($GoogleToken);
            $this->updateContactToGoogle($GoogleToken);

            return view('client.process');
        } catch (Exception $th) {
            dd($th);
        }
    }

    //for update contact form Crm to google contct
    public function updateContactToGoogle($GoogleToken)
    {
        $client = $this->getGoogleCient($GoogleToken);
        $peopleService = new PeopleService($client);

        //Modfy according to prevent un neseeory anpi requests
        $crmContacts = Client::whereColumn('updated_at', '>', 'updateFlag')->get();
        dd($crmContacts);
        foreach ($crmContacts as $contact) {
            try {
                $person = new Person();

                // Set name
                $name = new PeopleService\Name();
                $name->setGivenName($contact->FirstName);
                $name->setFamilyName($contact->lastName);
                $person->setNames([$name]);

                // Set email
                $email = new PeopleService\EmailAddress();
                $email->setValue($contact->email);
                $person->setEmailAddresses([$email]);

                // Set phone
                $phone = new PeopleService\PhoneNumber();
                $phone->setValue($contact->number);
                $person->setPhoneNumbers([$phone]);

                // Set userDefined fields
                $userDefinedFields = [];

                $userDefinedPairs = [
                    'panCardNumber' => $contact->panCardNumber,
                    'aadharCardNumber' => $contact->aadharCardNumber,
                    'occupation' => $contact->occupation,
                    'kycStatus' => $contact->kycStatus,
                    'anulIncome' => (string)$contact->anulIncome,
                    'referredBy' => $contact->referredBy,
                    'totalInvestment' => (string)$contact->totalInvestment,
                    'relationshipManager' => $contact->relationshipManager,
                    'serviceRM' => $contact->serviceRM,
                    'totalSIP' => (string)$contact->totalSIP,
                    'primeryContactPerson' => $contact->primeryContactPerson,
                    'meetinSchedule' => $contact->meetinSchedule,
                    'firstMeetingDate' => $contact->firstMeetingDate,
                    'typeOfRelation' => $contact->typeOfRelation,
                    'maritalStatus' => $contact->maritalStatus,
                ];

                foreach ($userDefinedPairs as $key => $value) {
                    if (!empty($value)) {
                        $field = new PeopleService\UserDefined();
                        $field->setKey($key);
                        $field->setValue($value);
                        $userDefinedFields[] = $field;
                    }
                }

                $person->setUserDefined($userDefinedFields);

                    // Update existing contact
                    $person->setEtag($contact->etag);
                    $updated = $peopleService->people->updateContact($contact->resourceName, $person, [
                        'updatePersonFields' => 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies',
                    ]);

                    $updateDate = client::where('resourceName', $contact->resourceName)->first();
                        $updateDate->etag = $updated->etag;
                        $updateDate->updateFlag = Carbon::now();
                        $updateDate->save();

            } catch (\Exception $e) {
                //Log::error("Google sync failed for CRM ID {$contact->id}: " . $e->getMessage());
                dd($e);
            }
        }

        return response()->json(['status' => 'CRM to Google contacts sync completed']);
    }

    //for Create contact form Crm to google contct
    public function createContactToGoogle($GoogleToken)
    {
        $client = $this->getGoogleCient($GoogleToken);
        $peopleService = new PeopleService($client);

        //Modfy according to prevent un neseeory anpi requests
        $crmContacts = client::whereNull('resourceName')->get();

        foreach ($crmContacts as $contact) {
            try {
                $person = new Person();

                // Set name
                $name = new PeopleService\Name();
                $name->setGivenName($contact->FirstName);
                $name->setFamilyName($contact->lastName);
                $person->setNames([$name]);

                // Set email
                $email = new PeopleService\EmailAddress();
                $email->setValue($contact->email);
                $person->setEmailAddresses([$email]);

                // Set phone
                $phone = new PeopleService\PhoneNumber();
                $phone->setValue($contact->number);
                $person->setPhoneNumbers([$phone]);

                // Set userDefined fields
                $userDefinedFields = [];

                $userDefinedPairs = [
                    'panCardNumber' => $contact->panCardNumber,
                    'aadharCardNumber' => $contact->aadharCardNumber,
                    'occupation' => $contact->occupation,
                    'kycStatus' => $contact->kycStatus,
                    'anulIncome' => (string)$contact->anulIncome,
                    'referredBy' => $contact->referredBy,
                    'totalInvestment' => (string)$contact->totalInvestment,
                    'relationshipManager' => $contact->relationshipManager,
                    'serviceRM' => $contact->serviceRM,
                    'totalSIP' => (string)$contact->totalSIP,
                    'primeryContactPerson' => $contact->primeryContactPerson,
                    'meetinSchedule' => $contact->meetinSchedule,
                    'firstMeetingDate' => $contact->firstMeetingDate,
                    'typeOfRelation' => $contact->typeOfRelation,
                    'maritalStatus' => $contact->maritalStatus,
                ];

                foreach ($userDefinedPairs as $key => $value) {
                    if (!empty($value)) {
                        $field = new PeopleService\UserDefined();
                        $field->setKey($key);
                        $field->setValue($value);
                        $userDefinedFields[] = $field;
                    }
                }
                $person->setUserDefined($userDefinedFields);
                    // Update existing contact
                    $person->setEtag($contact->etag);
                    $updated = $peopleService->people->updateContact($contact->resourceName, $person, [
                        'updatePersonFields' => 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies',
                    ]);

                    $updateDate = client::where('resourceName', $contact->resourceName)->first();
                        $updateDate->etag = $updated->etag;
                        $updateDate->updateFlag = Carbon::now();
                        $updateDate->save();

            } catch (\Exception $e) {
                //Log::error("Google sync failed for CRM ID {$contact->id}: " . $e->getMessage());
                dd($e);
            }
        }

        return back();
    }

    // for test somthing
    public function test(){
        try {
            $result=$GoogleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();
            // dd($result);
        $this->updateContactToGoogle($GoogleToken);
        } catch (\Throwable $th) {
            throw $th;
        }

    }
}
