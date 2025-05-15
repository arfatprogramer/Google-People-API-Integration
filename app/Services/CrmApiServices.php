<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class CrmApiServices 
{
    protected string $baseUrl = 'https://uat.sanchaycrm.com/api';

    public function login($username, $password)
    {
        $response = Http::post("{$this->baseUrl}/v2/login", [
            'user_name' => $username,
            'password' => $password,
        ]);

        return $response->json();
    }

    public function getContacts($payload)
    {
        $token = session('crm_token');

        

        $response = Http::withToken($token)
            ->acceptJson()
            ->post("{$this->baseUrl}/v2/get-listview-data", $payload);

        return $response->json();
    }

    public function getContactById($payload)
    {
        $token = session('crm_token');

        $response = Http::withToken($token)
            ->acceptJson()
            ->post("{$this->baseUrl}/v1/getentry-detail", $payload);

        return $response->json();
    }

    public function createContact($payload)
    {
        $token = session('crm_token');

        $response = Http::withToken($token)
            ->acceptJson()
            ->contentType('application/json')
            ->post("{$this->baseUrl}/v1/setentry-create", $payload);

        return $response->json();
    }

    public function updateContact($payload)
    {
        $token = session('crm_token');

        $response = Http::withToken($token)
            ->acceptJson()
            ->contentType('application/json')
            ->post("{$this->baseUrl}/v1/setentry-update", $payload);

        return $response->json();
    }

   
    public static function formatPhones(array $phones): Collection
    {
        return collect($phones)->map(function ($phone, $index) {
            return [
                'table_name'         => 'phone_numbers',
                'related_table_name' => 'phone_numbers_rel',
                'phone_number'       => $phone,
                'primary'            => $index === 0,
                'invalid'            => false,
                'unsubscribed'       => false,
                'verified_at'        => now()->toDateTimeString(),
            ];
        });
    }

     public static function formatEmails(array $emails): Collection
    {
        return collect($emails)->map(function ($email, $index) {
            return [
                'table_name'         => 'email_addresses',
                'related_table_name' => 'email_address_rel',
                'email_address'      => $email,
                'primary'            => $index === 0,
                'status'             => 'invalid',
                'suppression'        => $index === 0 ? $email : '',
                'verified_at'        => now()->toDateTimeString(),
            ];
        });
    }

         public static function formatAddress(array $address): Collection
        {
            return collect($address)->map(function ($address, $index) {
            return [
                'table_name'      => 'addresses',
                'related_table_name' => 'addresses_rel',
                'address_type'    => $address['address_type'] ?? 'Other',
                'street'          => $address['street'] ?? '',
                'area'            => $address['area'] ?? '',
                'city'            => $address['city'] ?? '',
                'state'           => $address['state'] ?? '',
                'country'         => $address['country'] ?? '',
                'postal_code'     => $address['postal_code'] ?? '',
                'primary'         => $index === 0, // First address is primary
                'verified_at'     => now()->toDateTimeString(),
            ];
        });
    }

    public function UpdatePayload($request , $phone_json, $email_json , $address_json ){
           return [
            "rest_data" => [
                "module_name" => "Contact",
                "id" => $request->id,
                "maping_records_upadate" => true,
                "mapping_parent_fields" => [
                    "first_name", "last_name", "designation", "account_id",
                    "phone", "email", "hierarchy", "department"
                ],
                "name_value_list" => [
                    "first_name" => $request->first_name ?? '',
                    "last_name" => $request->last_name ?? '',
                    "designation" => "Developer",
                    "hiddenPhone" =>  $phone_json,
                    "hiddenEmail" => $email_json,
                    "hiddenAddress" => $address_json,
                    "sync_status_c" => $request->sync_status_c === 'Synced' ? 'Pending' : ($request->sync_status_c ?? 'Not Synced'),
                    "hierarchy" =>"03",
                    "assigned_user_id" => "1",
                    "teamsSet" => "1"
            ]
            ]
        ];
    }

     
   
          return [
            "rest_data" => [
                "module_name" => "Contact",
                "name_value_list" => [
                    "first_name" => $request->first_name ?? '',
                    "last_name" => $request->last_name ?? '',
                    "designation" => "Developer",
                    "hiddenPhone" => $phone_json,
                    "hiddenEmail" => $email_json,
                    "hiddenAddress" => $address_json,
                    "sync_status_c" => "Not Synced",
                    "hierarchy" =>"03",
                    "assigned_user_id" => "1",
                    "teamsSet" => "1"
                ]
            ]
    ];
    
}
