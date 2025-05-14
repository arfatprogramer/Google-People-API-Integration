<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class CrmApiServices
{
    protected string $baseUrl = 'https://uat.sanchaycrm.com/api';
    protected string  $token='';

     public function __construct($token=''){
        $this->token= $token;
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
        $token = session('crm_token');

        $payload = [
            'rest_data' => [
                'module_name' => 'Contact',
                'max_result' => 25,
                'sort' => 'updated_at',
                'order_by' => 'DESC',
                'query' => '',
                'favorite' => false,
                'save_search' => false,
                'assigned_user_id' => '1',
                'name_value_list' => [
                    'select_fields' => [
                        'name', 'designation', 'phone_json', 'phone_primary',
                        'email_json', 'email_primary', 'id', 'created_at',
                        'updated_at', 'assigned_user_id', 'phone', 'birth_date', 'anniversary'
                    ]
                ]
            ]
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->post("{$this->baseUrl}/v2/get-listview-data", $payload);

        return $response->json();
    }

    public function getContactById($id)
    {
        $token = session('crm_token');

        $payload = [
            'rest_data' => [
                'action' => 'show',
                'module_name' => 'Contact',
                'id' => $id,
                'select_fields' => [
                    "id", "name", "phone", "email", "phone_primary", "designation",
                    "birth_date", "comment", "first_name", "last_name",
                    "email_primary", "phone_json"
                ],
                'select_relate_fields' => []
            ]
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->post("{$this->baseUrl}/v1/getentry-detail", $payload);

        return $response->json();
    }

    public function createContact($payload)
    {

        try {
            $response = Http::withToken($this->token)
            ->acceptJson()
            ->contentType('application/json')
            ->post("{$this->baseUrl}/v1/setentry-create",$payload);
            dump(json_decode($response));
        } catch (\Throwable $th) {
            dump($th);
        }

        return ;
    }

    public function updateContact($id, $payload)
    {
        $payload['rest_data']['id'] = $id;
        dump("Update Function");
        dump(json_encode($payload));
        $response = Http::withToken($this->token)
        ->acceptJson()
        ->contentType('application/json')
        ->post("{$this->baseUrl}/v1/setentry-update", $payload);

        dump(($response));
        return;

    }

    public function updateSyncStatus($id,$resourceName,$etag,$status) {
        dump("Update Status Function");
        $payload = [
            "rest_data"=> [
                "module_name"=> "Contact",
                "id"=>$id,

                "name_value_list"=> [
                    "etag_c"=>$etag,
                    "resource_name_c"=>$resourceName,
                    "last_sync_c"=>Carbon::now(),
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
        // $token = session('crm_token');
        $string = "('" . implode("','", $resourceName) . "')";
        dump($string);
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
                "assigned_user_id"=> "1",
                "teamsSet"=> "1",
                "advance_search"=> false,
                "advance_search_json"=> "",
                "multi_initial_filter"=> "",
                "name_value_list"=> [
                    "name_value_list"=>[
                        "resource_name_c"=>$resourceName ,
                    ],
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
        dump($response);
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
    public function getClietsList($pairameter,$max_result,$status){
         $payload=[
            "rest_data"=>[
                "module_name"=> "Contact",
                "max_result"=> $max_result,
                "sort"=> "updated_at",
                "order_by"=> "DESC",
                "query"=> "sync_status_c='$status'",
                "favorite"=> false,
                "save_search"=> false,
                "save_search_id"=> "",
                "assigned_user_id"=> "1",
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
}
