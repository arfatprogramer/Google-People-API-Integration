<?php

namespace App\Services;

use App\Models\clientContatSyncHistory;
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
use Illuminate\Support\Facades\Log;

class GoogleService
{

    public function getGoogleCient($googleToken)
    {
        Log::info('get GoogleCliet form Google Services class: ' . (memory_get_usage(true)/1024/1024)." MB");

        $accessToken = $googleToken->googleAccessToken;
        $expireIn = $googleToken->accessTokenExpiresIn;
        $created = Carbon::parse($googleToken->updated_at)->timestamp;
        $expectedExpireTime = Carbon::parse(now()->addMinutes(3))->timestamp;

        if ($created + $expireIn < $expectedExpireTime) {
            // get new Token
            Log::info('Refresh Function Called: ' . (memory_get_usage(true)/1024/1024)." MB");
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
              $client = new Google_Client();
              // this this not nessory for every things
              $client->addScope([
                  'https://www.googleapis.com/auth/contacts',
                  'email',
                  'profile'
              ]);
              $client->setClientId(config('services.google.client_id'));
              $client->setClientSecret(config('services.google.client_secret'));

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

              return $accessToken['access_token'];
          } catch (Exception $e) {
              Log::error("This is comming From RefreshToke Rator Function in Google Service ".$e);
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

     public function getContacts($googleToken, $pageSize, $personFields, $nextPageToken = null, $nextSyncToken=null): object
     {
         try {
             $client = $this->getGoogleCient($googleToken);
             $peopleService = new PeopleService($client);

             $params = [
                 'pageSize' => $pageSize,
                 'personFields' => $personFields,
                 'requestSyncToken' => true,
             ];

             if ($nextPageToken) {
                 $params['pageToken'] = $nextPageToken;
                }

             if ($nextSyncToken) {
                $params['syncToken'] = $nextSyncToken;
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
