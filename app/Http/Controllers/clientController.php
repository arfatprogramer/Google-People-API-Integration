<?php

namespace App\Http\Controllers;

use App\Models\client;
use Illuminate\Http\Request;

use App\Models\ClientAddress;
use Illuminate\Support\Carbon;
use App\Services\CrmApiServices;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Vatidator;


class clientController extends Controller
{

    function __construct(){
        Log::info('Client controller Constructor Method: ' . (memory_get_usage(true)/1024/1024)." MB");
    }


    function show(Request $req ){
        if ($req->ajax()) {


            $clients=client::query();

            return DataTables::eloquent($clients)
            ->addColumn('action',function($clients){
                    return "<a href=".route('client.edit',$clients->id)."> <i class='bi bi-pencil text-blue-500 hover:text-blue-600 '></i></a>
                     <button class='deleteGoogleContact cursor-pointer' data-bs-id='$clients->id'> <i class='bi bi-trash text-red-500 hover:text-red-600 '></i></button>";

            })
            ->addColumn('created_at',function($clients){
                return Carbon::parse($clients->created_at)->format('y-m-d');
            })
            ->addColumn('updated_at',function($clients){
                return Carbon::parse($clients->updated_at)->format('y-m-d');
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('client.list');
    }



    function createForm(){
        return view('client.createForm');
    }


public function create(Request $request)
{
    try {

        $validate = $request->validate([
            'phone' => 'required',
            'email' => 'required',
        ]);

                // Address
            $address = collect($request->addresses)->map(function ($address, $index) {
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


        $phone_json = collect($request->phone_json)->map(function ($phone, $index) {
            return [
                'table_name'         => 'phone_numbers',
                'related_table_name' => 'phone_numbers_rel',
                'phone_number'       => $phone,
                'primary'            => $index === 0  ,
                'invalid'            => false,
                'unsubscribed'       => false,
                'verified_at'        => now()->toDateTimeString(),
            ];
        });

        $email_json = collect($request->email_json)->map(function ($email, $index) {
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


         $payload = [
            "rest_data" => [
                "module_name" => "Contact",
                "name_value_list" => [
                    "first_name" => $request->first_name ?? '',
                    "last_name" => $request->last_name ?? '',
                    "designation" => "Developer",
                    "hiddenPhone" => $phone_json,
                    "hiddenEmail" => $email_json,
                    "hiddenAddress" => $address,
                    "sync_status_c" => "Not Synced",
                    "hierarchy" =>"03",
                    "assigned_user_id" => "1",
                    "teamsSet" => "1"
                ]
            ]
    ];

        // return $payload;
        // ✅ Step 5: Send to service
        $response = (new CrmApiServices(session('crm_token')))->createContact($payload);

       return redirect(route('ajax.index'))->with('success',"Contact is create successfully");
        // return response()->json(['status' => true, 'message' => 'Contact created', 'data' => $response]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ], 500);
    }
}

    public function editContact($id){
        // $data = client::where('id',$id)->find($id);
           $payload = [
            'rest_data' => [
                'action' => 'show',
                'module_name' => 'Contact',
                'id' => $id,
                'select_fields' => [
                    "id", "name", "phone", "email_json", "phone_primary", "designation",
                    "birth_date",  "first_name", "last_name",
                    "email_primary", "phone_json","address_type",'address_json','street','city','area','state','postal_code','country',
                    'occupation','sync_status_c'
                ],
                'select_relate_fields' => []
            ]
        ];

        $getdataById = (new CrmApiServices(session('crm_token')))->getContactById($payload);
        $data = $getdataById;

       $nameValueList = $data['entry_list']['name_value_list'] ?? [];
//            $response = $yourApiResponse['entry_list'] ?? [];
// $nameValueList = $response['name_value_list'] ?? [];

$contacts = collect($nameValueList)->mapWithKeys(function ($item) {
    return [$item['name'] => $item['value']];
})->toArray();

        return view("client.createForm",compact('contacts'));

    }
///------UpdateForm------------------
        public function UpdateFormContact(Request $request){
            $validate = $request->validate([
                'first_name'=>'required|string',
                "phone"=>'required',
                "email"=>'required|email',
            ]);
            // dd($request->sync_status_c);
                 // Address
            $address = collect($request->addresses)->map(function ($address, $index) {
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

        // return $address;
        // Format phone_json
        $phone_json = collect($request->phone_json)->map(function ($phone, $index) {
            return [
                'table_name'         => 'phone_numbers',
                'related_table_name' => 'phone_numbers_rel',
                'phone_number'       => $phone,
                'primary'            => $index === 0  ,
                'invalid'            => false,
                'unsubscribed'       => false,
                'verified_at'        => now()->toDateTimeString(),
            ];
        });
        // return $phone_json;
        //  Format email_json
        $email_json = collect($request->email_json)->map(function ($email, $index) {
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
             $payload = [
            "rest_data" => [
                "module_name" => "Contact",
                "id"=>$request->id,
                "maping_records_upadate" => true,
                "mapping_parent_fields" => [
                    "first_name", "last_name", "designation", "account_id",
                    "phone", "email", "hierarchy", "department"
                ],
                "name_value_list" => [
                    "first_name" => $request->first_name ?? '',
                    "last_name" => $request->last_name ?? '',
                    "designation" => "Developer",
                    "hiddenPhone" => $phone_json,
                    "hiddenEmail" => $email_json,
                    "hiddenAddress" => $address,
                    "sync_status_c" => $request->sync_status_c === 'Synced' ? 'Pending' : ($request->sync_status_c ?? 'Not Synced'),
                    "hierarchy" =>"03",
                    "assigned_user_id" => "1",
                    "teamsSet" => "1"
            ]
            ]
        ];
        //   dd($payload);
       $response = (new CrmApiServices(session('crm_token')))->updateContact($request->id,$payload);
        if ($response) {
            return redirect()->route('ajax.index')->with('success', 'CRM Contact updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to update CRM Contact.');
        }



  }
    public function __destruct() {
        Log::info('Client Controller Distructor Method: ' . (memory_get_usage(true)/1024/1024)." MB");
    }
}
