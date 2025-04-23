<?php

namespace App\Http\Controllers;

use App\Jobs\pushToGoogleJob;
use App\Models\client;
use App\Models\GoogleAuth;
use Carbon\Carbon;
use Exception;
use Google\Service\PeopleService;
use Google\Service\PeopleService\Person;
use Google_Client;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AjaxRequestController extends Controller
{
    private $isProcessing=false;
    private $updateCountToGoogle=0;
    private $createCountToGoogle=0;
    private $updateCountToCrm=0;
    private $createCountToCrm=0;

    public function index(){
        try {

            return view('client.enjayDesign');

        } catch (\Throwable $th) {
            dd($th);
        }

    }

    public function refreshReq(){
        try {

        $crmTotalClient=client::count();
        $google=2345;
        $data=['crm'=>$crmTotalClient,
                'google'=>$google,
                'pending'=>$crmTotalClient,
                'error'=>$crmTotalClient,
                'lastSync'=>"April 7, 2024 at 1:20 AM"
        ];

        return response()->json([
                'status'=>true,
                'message'=>"Refreshed",
                'error'=>true,
                'data'=>$data,
            ]);
             //code...
        } catch (Exception $e) {
            return response()->json([
                'status'=>false,
                'error'=>true,
                'massage'=>$e,
                'data'=>[],
            ]);
        }
    }

    // Push To Google Function Ready
    public function pushToGoogle(){
        try {
            $this->isProcessing=true;
            $delay=1;
            $googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();
            $createIdList = Client::whereNull('resourceName')->pluck('id')->toArray();
            $updateIdList = Client::whereColumn('updated_at', '>', 'updateFlag')->pluck('id')->toArray();

            $chunks1 = array_chunk($createIdList, 40);
            $chunks2=array_chunk($updateIdList,40);

            // Create equal pairs from both chunks
            $maxChunks = max(count($createIdList), count($updateIdList));

            for ($i = 0; $i < $maxChunks; $i++) {
                $createChunk = $chunks1[$i] ?? [];
                $updateChunk = $chunks2[$i] ?? [];

                if ($i>=1) {
                    pushToGoogleJob::dispatch($googleToken, $createChunk, $updateChunk)
                    ->delay(now()->addMinutes($delay));
                    $delay++; // Avoid rate limit
                }else{
                    pushToGoogleJob::dispatch($googleToken, $createChunk, $updateChunk);
                }
            }

        $data=[
            'isProcessing'=>$this->isProcessing,
            'UpdatingToGoogle'=>count($updateIdList),
            'CreatingToGoogle'=>count($createIdList),
            ];


        return response()->json([
                'status'=>true,
                'message'=>"Push To Google Stared",
                'error'=>true,
                'data'=>$data,
            ]);
             //code...
        } catch (Exception $e) {
            return response()->json([
                'status'=>false,
                'error'=>true,
                'massage'=>$e,
                'data'=>[],
            ]);
        }
    }

    // This for Dispaly Process Bar
     public function syncStatus(){
        try {
            $this->createCountToCrm;
            $this->createCountToGoogle;
            $this->updateCountToCrm;
            $this->updateCountToGoogle;
            $isProcessing=$this->isProcessing;

        $data=[
            'isProcessing'=>$isProcessing,
        ];

        return response()->json([
                'status'=>true,
                'message'=>"Sync Status",
                'error'=>true,
                'data'=>$data,
            ]);
             //code...
        } catch (Exception $e) {
            return response()->json([
                'status'=>false,
                'error'=>true,
                'massage'=>$e,
                'data'=>[],
            ]);
        }
    }

    // This is Layout for Function
    public function layout(){
        try {

        $data=[];

        return response()->json([
                'status'=>true,
                'message'=>"Refreshed",
                'error'=>true,
                'data'=>$data,
            ]);
             //code...
        } catch (Exception $e) {
            return response()->json([
                'status'=>false,
                'error'=>true,
                'massage'=>$e,
                'data'=>[],
            ]);
        }
    }


    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<   Copy from Other Controller >>>>>>>>>>>>>>>>>>>>>>>>>

    // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>  this Section for Genral Function Use By Many Function <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

    //genrate google and people client
    public function getGoogleCient($googleToken)
    {
        $accessToken = $googleToken->googleAccessToken;
        //if access token Expire then Regenrate
        $created = Carbon::parse($googleToken->updated_at)->timestamp;
        $expireIn = $googleToken->accessTokenExpiresIn;
        if ($created + $expireIn < time()) {
            // get new Token
            $accessToken = $this->refreshAccessToken($googleToken);
        }

        $client = new Google_Client();
        $client->setAccessToken([
            'access_token' => $accessToken,
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


     // get contact list ushing access token
      /***
         * Folling name Where Use this Function
         1 index
         2 syncGoogleContacts
        **/
     public function  getContacts($googleToken, $pageSize ,$personFields): object
     {
         try {

             $client = $this->getGoogleCient($googleToken);
             $peopleService = new PeopleService($client);

             $response = $peopleService->people_connections->listPeopleConnections(
                 'people/me',
                 [
                     'pageSize' => $pageSize,
                     'personFields' => $personFields,
                     // 'requestSyncToken' => true,
                 ]
             );
             return $response->toSimpleObject();
         } catch (Exception $e) {
             return ($e);
         }
     }

     //Genrate person Object
        /***
         * Folling name Where Use this Function
         1 createContactToGoogle
         2 updateContactToGoogle
        **/
     public function getPerson($contact){
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

        return $person;
     }



// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>  this Section for  Google  Authentication <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

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





// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>  this Section for  Google To Database <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

    //2 Way google synch function CRM <=> Google contact

    public function syncGoogleContacts()
    {
        try {
            $GoogleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();

            // 1. Get Google contacts
            // Require fileds
            $personFields=['names,emailAddresses,phoneNumbers,userDefined,organizations,biographies'];
            $pageSize=1000;
            //contact come here ny the functon
            $googleContacts = $this->getContacts($GoogleToken, $pageSize, $personFields);

            $googleMap = collect($googleContacts->connections)->mapWithKeys(function ($person) {
                $customFields = collect($person->userDefined)->mapWithKeys(function ($field) {
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

            // 2. Load CRM contacts with resourceName
            $crmContacts = client::whereNotNull('resourceName')->select(['id', 'resourceName', 'etag'])->get()->keyBy('resourceName');

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

            //following both will be user to create and update contact from crm to google
            // $reatedCound=$this->createContactToGoogle($GoogleToken);
            // $UpdatedCound=$this->updateContactToGoogle($GoogleToken);

            unset($googleMap);
            return view('client.process');
        } catch (Exception $th) {
            dd($th);
        }
    }
// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>  this Section for Database to Google <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

    //for update contact form Crm to google contct
    public function updateContactToGoogle($GoogleToken)
    {
        $count=0;
        $timeStamp=Carbon::now();
        $client = $this->getGoogleCient($GoogleToken);
        $peopleService = new PeopleService($client);

        //Modify according to prevent un neseeory API requests
        $crmContacts = Client::whereColumn('updated_at', '>', 'updateFlag')->get();
        foreach ($crmContacts as $contact) {
            try {

                //This will come By Getperson Function
                $person=$this->getPerson($contact);

                // Update existing contact
                $person->setEtag($contact->etag);
                $updated = $peopleService->people->updateContact($contact->resourceName, $person, [
                    'updatePersonFields' => 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies',
                ]);

                $updateDate = client::where('resourceName', $contact->resourceName)->first();
                $updateDate->etag = $updated->etag;
                $updateDate->updateFlag = $timeStamp;
                $updateDate->save();

                $count++;

            } catch (\Exception $e) {

                //We need to handel the session Expire when Executing
                dd($e);
            }
        }
        // Free up memory after done
        unset($crmContacts);
        return (['status' => 'CRM to Google contacts Update completed', 'code'=>200, 'count' => $count]);
    }

    //for Create contact form Crm to google contacts
    public function createContactToGoogle($GoogleToken)
    {
        $count=0;
        $timeStamp=Carbon::now();
        $client = $this->getGoogleCient($GoogleToken);
        $peopleService = new PeopleService($client);

        //Modfy according to prevent un nesseory API requests
        $crmContacts = client::whereNull('resourceName')->get();
        foreach ($crmContacts as $contact) {
            try {
                //This will come By Getperson Function
                $person=$this->getPerson($contact);

                $created = $peopleService->people->createContact($person, [
                    'personFields' => 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies',
                ]);

                $updateDate = client::where('id', $contact->id)->first();
                $updateDate->resourceName = $created->resourceName;
                $updateDate->etag = $created->etag;
                $updateDate->updateFlag = $timeStamp;
                $updateDate->save();
                $count++;
            } catch (\Exception $e) {
                //handel expire access token
                dd($e);
            }
        }
        // Free up memory after done
        unset($crmContacts);
        return (['status' => 'CRM to Google contacts sync completed', 'code'=>200, 'count' => $count]);
    }

}


