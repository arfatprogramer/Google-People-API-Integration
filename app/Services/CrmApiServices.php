<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CrmApiServices
{
    protected string $baseUrl;
    protected string  $token='';

     public function __construct($token=''){
        $this->token= $token;
        $this->baseUrl = env('SANCHAY_CRM_URL') . '/api';
     }

    public function login($username, $password)
    {
        $response = Http::post("{$this->baseUrl}/v2/login", [
            'user_name' => $username,
            'password' => $password,
        ]);

        return $response->json();
    }

    public function getContacts()
    {
         $payload = [
                "rest_data" => [
                    "module_name" => "Contact",
                    "max_result" => 1000,
                    "sort" => "updated_at",
                    "order_by" => "DESC",
                //    "query" => "sync_status_c IS NULL OR sync_status_c = ''",
                    "query" =>'',
                    "favorite" => false,
                    "save_search" => false,
                    "save_search_id" => "",

                    //"assigned_user_id" => "",

                    "advance_search" => false,
                    "advance_search_json" => "",
                    "multi_initial_filter" => "",
                    "name_value_list" => [
                        "select_fields" => [
                            "name",
                            "designation",
                            "phone_json",
                            "phone_primary",
                            "email_json",
                            "email_primary",
                            "sync_status_c",
                            "last_sync_c",
                            "id",


                        ]
                    ]
                ]
            ];

        $response = Http::withToken($this->token)
            ->acceptJson()
            ->post("{$this->baseUrl}/v2/get-listview-data", $payload);

        return $response->json();
    }

    public function getContactById($payload)
    {

        $response = Http::withToken($this->token)
            ->acceptJson()
            ->post("{$this->baseUrl}/v1/getentry-detail", $payload);

        return $response->json();
    }

    public function createContact($payload)
    {
        dump($payload);
        try {
            $response = Http::withToken($this->token)
            ->acceptJson()
            ->contentType('application/json')
            ->post("{$this->baseUrl}/v1/setentry-create",$payload);
        } catch (\Throwable $th) {
            dump($th);
        }
        // dump($response);
        return $response->json() ;
    }

    public function updateContact($id, $payload)
    {
        $payload['rest_data']['id'] = $id;
        $response = Http::withToken($this->token)
        ->acceptJson()
        ->contentType('application/json')
        ->post("{$this->baseUrl}/v1/setentry-update", $payload);
        // return $response->json();
        return $payload;
    }

    public function updateSyncStatus($id,$resourceName,$etag,$status,$lastSync=null) {
        $payload = [
            "rest_data"=> [
                "module_name"=> "Contact",
                "id"=>$id,
                "name_value_list"=> [
                    "etag_c"=>$etag,
                    "resource_name_c"=>$resourceName,
                    "last_sync_c"=>$lastSync==null? (null):(Carbon::now()),
                    "sync_status_c"=>$status
                ]
            ]
        ];
         $response = Http::withToken($this->token)
            ->acceptJson()
            ->contentType('application/json')
            ->post("{$this->baseUrl}/v1/setentry-update", $payload);
        return $response;
    }


    // to get the inform mation data is existing in data base or noe
    public function getExistingDataFromCrm($resourceName=[]) {
        $string = "('" . implode("','", $resourceName) . "')";

        $payload=[
            "rest_data"=> [
                "module_name"=> "Contact",
                "max_result"=> 1000,
                "sort"=> "updated_at",
                "order_by"=> "DESC",
                "query"=> "resource_name_c in $string",
                "favorite"=> false,
                "save_search"=> false,
                "save_search_id"=> "",
                "assigned_user_id"=> "",
                "teamsSet"=> "",
                "advance_search"=> false,
                "advance_search_json"=> "",
                "multi_initial_filter"=> "",
                "name_value_list"=> [
                    "select_fields"=> [
                        "resource_name_c",
                        "etag_c",
                        "id"
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withToken($this->token)
            ->acceptJson()
            ->contentType('application/json')
            ->post("{$this->baseUrl}/v2/get-listview-data", $payload);
            Log::info("in getExistingDataFromCrm CrmAPIService");

        } catch (\Throwable $th) {
            Log::info("error in getExistingDataFromCrm CrmAPIService");
        }
        $response=json_decode($response);
        // dump($response);
        $existingData=[];
        $responseData=$response->data??[];
        foreach($responseData as $data){
                $resource_name_c=$data->resource_name_c;
                $existingData[$resource_name_c]=['etag'=>$data->etag_c,'id'=>$data->id ];
            }
            dump($existingData);
        return $existingData;

    }


    // Get list of Clietsfrom Crm
    public function getClietsList($pairameter,$pageSize,$search){
         $payload=[
            "rest_data"=>[
                "module_name"=> "Contact",
                "max_result"=> $pageSize,
                "sort"=> "updated_at",
                "order_by"=> "DESC",
                // "query"=> "sync_status_c='$status'",
                "query"=>$search,
                "favorite"=> false,
                "save_search"=> false,
                "save_search_id"=> "",
                "assigned_user_id"=> "",
                "teamsSet"=> "1",
                "advance_search"=> false,
                "advance_search_json"=> "",
                "multi_initial_filter"=> "",
                "name_value_list"=>[
                    "select_fields"=> [
                    "id",
                    "name",
                    "designation",
                    "anniversary",
                    "birth_date",
                    "pancard_c",
                    "adhaar_card_c",
                    "kyc_status_c",
                    "account_id",
                    "attachment1_c",
                    "customer_type",
                    "phone",
                    "phone_json",
                    "email",
                    "email_json",
                    "duration_c",
                    "comment",
                    "hierarchy",
                    "department",
                    "lead_source",
                    "assigned_user_id",
                    "team_set_id",
                    "created_at",
                    "updated_at",
                    "tag",
                    "created_by",
                    "eta_id",
                    "eta_end_time",
                    "eta_status",
                    "first_name",
                    "last_name",
                    "phone_json",
                    "email_json",
                    "address",
                    "tally_master_id",
                    "linked_status",

                    "resource_name_c",
                    "etag_c",
                    "last_sync_c",
                    "sync_status_c"

                    ]
                ]
            ]
        ];

     $response = Http::withToken($this->token)
            ->acceptJson()
            ->post("{$this->baseUrl}/v2/get-listview-data?page=$pairameter", $payload);
        $response=json_decode($response);
        return ($response);

    }

    public function deleteFromCRM($id){
        $payload=[
                    "rest_data"=> [
                        "module_name"=> "Contact",
                        "id"=> $id
                    ]

                ];
     $response = Http::withToken($this->token)
            ->acceptJson()
            ->post("{$this->baseUrl}/v1/setentry-delete", $payload);
        $response=json_decode($response);
        return ($response);

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


}
