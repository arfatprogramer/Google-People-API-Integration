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

        //     $payload = [
        //     "rest_data" => [
        //         "module_name" => "Contact",
        //         "max_result" => 25,
        //         "sort" => "updated_at",
        //         "order_by" => "DESC",
        //         "query" => "",
        //         "favorite" => false,
        //         "save_search" => false,
        //         "save_search_id" => "",
        //         "assigned_user_id" => "1",
        //         "advance_search" => false,
        //         "advance_search_json" => "",
        //         "multi_initial_filter" => "",
        //         "name_value_list" => [
        //             "select_fields" => [
        //                 "name",
        //                 "designation",
        //                 "phone_json",
        //                 "phone_primary",
        //                 "email_json",
        //                 "email_primary",
        //                 "id",
        //                 "address_type",
        //                 "city",
        //                 "created_at",
        //                 "updated_at",
        //                 "assigned_user_id",
        //                 "phone",
                       
        //             ]
        //         ]
        //     ]
        //  ];
         
        //  return $payload;
            // $clients = $getList_crm->getContacts($payload);
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

   


public function create(Request $request, CrmApiServices $create)
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
        // $response = $create->createContact($payload);

       return redirect(route('ajax.index'))->with('success',"Contact is create successfully");
        // return response()->json(['status' => true, 'message' => 'Contact created', 'data' => $response]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ], 500);
    }
}


   
        
    public function editContact($id,CrmApiServices $getContactById){
        // $data = client::where('id',$id)->find($id);
           $payload = [
            'rest_data' => [
                'action' => 'show',
                'module_name' => 'Contact',
                'id' => $id,
                'select_fields' => [
                    "id", "name", "phone", "email", "phone_primary", "designation",
                    "birth_date",  "first_name", "last_name",
                    "email_primary", "phone_json","address_type",
                ],
                'select_relate_fields' => []
            ]
        ];

        $getdataById = $getContactById->getContactById($payload);
        return $getdataById;
        return view("client.createForm",compact('data'));

       $clientAddress = ClientAddress::where('client_id',$id)->get();
        return view("client.createForm",compact('data','clientAddress'));

    }
///------UpdateForm------------------
public function UpdateFormContact(Request $request){
      $validate = $request->validate([
        'firstName'=>'required|string',
        "number"=>'required',
        "email"=>'required|email',
      ]);
       

    //   return "updateto = ". $request->id;
      $newClient = Client::find($request->id);

      if( $newClient){

        $newClient->firstName=$request->firstName;//"firstName" => null
        $newClient->lastName=$request->lastName;               //"lastName" => null
        $newClient->number=$request->number;                //"number" => null
        $newClient->familyOrOrgnization=$request->familyOrOrgnization;    //"familyOrOrgnization" => null
        $newClient->email=$request->email;                  //"email" => null
        $newClient->panCardNumber=$request->panCardNumber;                //"panCardNumber" => null
        $newClient->aadharCardNumber=$request->aadharCardNumber;             //"aadharCardNumber" => null
        $newClient->occupation=$request->occupation;              //"occupation" => "Select"
        $newClient->kycStatus=$request->kycStatus;              //"kycStatus" => "Select"
        $newClient->anulIncome=$request->anulIncome;            //"anulIncome" => null
        $newClient->referredBy=$request->referredBy;              //"referredBy" => null
        $newClient->totalInvestment=$request->totalInvestment;        //"totalInvestment" => null
        $newClient->comments=$request->comments;                //"comments" => null
        $newClient->relationshipManager=$request->relationshipManager;    //"relationshipManager" => "Mo Arfat Ansari"
        $newClient->serviceRM=$request->serviceRM;              //"serviceRM" => null
        $newClient->totalSIP=$request->totalSIP;              //"totalSIP" => null
        $newClient->primeryContactPerson=$request->primeryContactPerson;   //"primeryContactPerson" => null
        $newClient->meetinSchedule=$request->meetinSchedule;         //"meetinSchedule" => "Select"
        $newClient->firstMeetingDate=$request->firstMeetingDate;       //"firstMeetingDate" => null
        $newClient->typeOfRelation=$request->typeOfRelation;         //"typeOfRelation" => "Select"
        $newClient->maritalStatus=$request->maritalStatus;          //"maritalStatus" => "Select"

        $newClient->maritalStatus=$request->maritalStatus;

        $newClient->syncStatus = $newClient->syncStatus=="Synced" ?("Pending"):($newClient->syncStatus);
        $newClient->save();

        // Retrieve existing addresses for the client
             $existingAddresses = ClientAddress::where('client_id', $request->id)->get();

            if ($request->addresses && is_array($request->addresses)) {
                foreach ($request->addresses as $address) {
                    // Check if the address already exists (you may want to check by address type or another unique identifier)
                    $clientAddress = $existingAddresses->firstWhere('address_type', $address['address_type'] ?? null);

                    if ($clientAddress) {
                        // Update existing address
                        $clientAddress->street = $address['street'] ?? null;
                        $clientAddress->area = $address['area'] ?? null;
                        $clientAddress->city = $address['city'] ?? null;
                        $clientAddress->state = $address['state'] ?? null;
                        $clientAddress->postal_code = $address['postal_code'] ?? null;
                        $clientAddress->country = $address['country'] ?? null;
                        $clientAddress->save();
                    } else {
                        // Create a new address if it doesn't exist
                        $newClientAddress = new ClientAddress();
                        $newClientAddress->client_id = $request->id; // Use the client's ID
                        $newClientAddress->address_type = $address['address_type'] ?? null;
                        $newClientAddress->street = $address['street'] ?? null;
                        $newClientAddress->area = $address['area'] ?? null;
                        $newClientAddress->city = $address['city'] ?? null;
                        $newClientAddress->state = $address['state'] ?? null;
                        $newClientAddress->postal_code = $address['postal_code'] ?? null;
                        $newClientAddress->country = $address['country'] ?? null;
                        $newClientAddress->save(); // Save the new address
                    }
                }
            }

        return redirect()->route("client.list")->with('success','Client Update Successfully');
      }else{
        return redirect()->back()->with('error','client not found');
      }

  }
    public function __destruct() {
        Log::info('Client Controller Distructor Method: ' . (memory_get_usage(true)/1024/1024)." MB");
    }
}