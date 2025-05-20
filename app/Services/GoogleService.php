<?php

namespace App\Services;

use App\Models\GoogleAuth;
use Carbon\Carbon;
use Exception;
use Google\Service\PeopleService;
use Google\Service\PeopleService\Biography;
use Google\Service\PeopleService\Address;
use Google\Service\PeopleService\Birthday;
use Google\Service\PeopleService\Date;
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

        Log::info('Return get GoogleCliet form Google Services class: ' . (memory_get_usage(true)/1024/1024)." MB");

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
                  'https://www.googleapis.com/auth/userinfo.profile',
                   'https://www.googleapis.com/auth/contacts'
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


    public function getPerson($contact)
    {
        $person = new Person();

        //  Set name
        $name = new Name();
        $name->setGivenName($contact->first_name ?? '');
        $name->setFamilyName($contact->last_name ?? '');
        $person->setNames([$name]);

        //Set Birth date
      if (!empty($contact->birth_date)) {
            $dobParts = explode('-', $contact->birth_date); // [2001, 04, 04]
            $birthDate = new Date();
            $birthDate->setYear((int)$dobParts[0]);
            $birthDate->setMonth((int)$dobParts[1]);
            $birthDate->setDay((int)$dobParts[2]);

            $birthday = new Birthday();
            $birthday->setDate($birthDate);
            $person->setBirthdays([$birthday]);
        }

        //Set emails
        $emailAddresses = [];
        $emails = json_decode($contact->email_json ?? '[]', true);
        $emails = $emails??[];
        foreach ($emails as $email) {
            if (!empty($email['email_address'])) {
                $emailObj = new EmailAddress();
                $emailObj->setValue($email['email_address']);
                $emailAddresses[] = $emailObj;
            }
        }
        $person->setEmailAddresses($emailAddresses);

        //  Set phones
        $phoneNumbers = [];
        $phones = json_decode($contact->phone_json ?? '[]', true);
        $phones =$phones??[];
        foreach ($phones as $phone) {
            if (!empty($phone['phone_number'])) {
                $phoneObj = new PhoneNumber();
                $phoneObj->setValue($phone['phone_number']);
                $phoneNumbers[] = $phoneObj;
            }
        }
        $person->setPhoneNumbers($phoneNumbers);

        //  Set addresses
        $addresses = [];
        $addressData = json_decode($contact->address_json ?? '[]', true);
        $addressData=$addressData??[];
        foreach ($addressData as $addr) {
            $address = new Address();
            $address->setStreetAddress($addr['street'] ?? '');
            $address->setCity($addr['city'] ?? '');
            $address->setRegion($addr['state'] ?? '');
            $address->setPostalCode($addr['postal_code'] ?? '');
            $address->setCountry($addr['country'] ?? '');
            $addresses[] = $address;
        }
        $person->setAddresses($addresses);

        //  Add comment to Notes (biography)
        // if (!empty($contact->comment)) {
        //     $bio = new Biography();
        //     $bio->setValue($contact->comment);
        //     $bio->setContentType('TEXT_PLAIN');
        //     $person->setBiographies([$bio]);
        // }

        //  Set userDefined fields
        $userDefinedFields = [];
        $userDefinedPairs = [

            'PAN card'                => $contact->pancard_c ?? '',
            'Adhaar card'             => $contact->adhaar_card_c ?? '',
            'kyc status'             => $contact->kyc_status_c ?? '',
            'designation'             => $contact->designation ?? '',
            'anniversary'             => $contact->anniversary ?? '',
            'birth_date'              => $contact->birth_date ?? '',
            'account_id'              => $contact->account_id ?? '',
            'account_id_name'         => $contact->account_id_name ?? '',
            'customer_type'           => $contact->customer_type ?? '',
            'hierarchy'               => $contact->hierarchy ?? '',
            'department'              => $contact->department ?? '',
            'lead_source'             => $contact->lead_source ?? '',
            'created_at'              => $contact->created_at ?? '',
            'updated_at'              => $contact->updated_at ?? '',
            'tag'                     => $contact->tag ?? '',
            'created_by'              => $contact->created_by ?? '',
            'created_by_name'         => $contact->created_by_name ?? '',
            'assigned_user_id'        => $contact->assigned_user_id ?? '',
            'assigned_user_id_name'   => $contact->assigned_user_id_name ?? '',
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
