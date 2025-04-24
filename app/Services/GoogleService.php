<?php

namespace App\Services;

use App\Models\GoogleAuth;
use Carbon\Carbon;
use Exception;
use Google\Service\PeopleService;
use Google\Service\PeopleService\EmailAddress;
use Google_Client;
use Google\Service\PeopleService\Person;
use Google\Service\PeopleService\Name;
use Google\Service\PeopleService\PhoneNumber;
use Google\Service\PeopleService\UserDefined;
use Illuminate\Support\Facades\Cache;

class GoogleService
{

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

      //Genrate person Object
        /***
         * Folling name Where Use this Function
         1 createContactToGoogle
         2 updateContactToGoogle
        **/
      public function getPerson($contact){
        $person = new Person();

        // Set name
        $name = new Name();
        $name->setGivenName($contact->FirstName);
        $name->setFamilyName($contact->lastName);
        $person->setNames([$name]);

        // Set email
        $email = new EmailAddress();
        $email->setValue($contact->email);
        $person->setEmailAddresses([$email]);

        // Set phone
        $phone = new PhoneNumber();
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
                $field = new UserDefined();
                $field->setKey($key);
                $field->setValue($value);
                $userDefinedFields[] = $field;
            }
        }

        $person->setUserDefined($userDefinedFields);

        return $person;
     }

     public function getContacts($googleToken, $pageSize, $personFields, $nextPageToken = null): object
     {
         try {
             $client = $this->getGoogleCient($googleToken);
             $peopleService = new PeopleService($client);

             $params = [
                 'pageSize' => $pageSize,
                 'personFields' => $personFields,
             ];

             if ($nextPageToken) {
                 $params['pageToken'] = $nextPageToken;
             }

             $response = $peopleService->people_connections->listPeopleConnections(
                 'people/me',
                 $params
             );

             return $response->toSimpleObject();
         } catch (Exception $e) {
             return $e;
         }
     }


}
