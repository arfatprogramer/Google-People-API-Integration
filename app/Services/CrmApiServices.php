<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Client;

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

    public function updateContact($id, $data)
    {
        $token = session('crm_token');

        $payload = [
            "rest_data" => [
                "module_name" => "Contact",
                "id" => $id,
                "maping_records_upadate" => true,
                "mapping_parent_fields" => [
                    "first_name", "last_name", "designation", "account_id",
                    "phone", "email", "hierarchy", "department"
                ],
                "name_value_list" => $data,
                "hierarchy" => "03",
                "department" => "",
                "lead_source" => "News Paper",
                "assigned_user_id" => "1",
                "teamsSet" => "1"
            ]
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->contentType('application/json')
            ->post("{$this->baseUrl}/v1/setentry-update", $payload);

        return $response->json();
    }
}
