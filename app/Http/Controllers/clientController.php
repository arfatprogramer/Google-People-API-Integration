<?php

namespace App\Http\Controllers;

use App\Models\client;
use App\Models\ClientAddress;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Vatidator;
class clientController extends Controller
{

    function __construct(){
        Log::info('Client controller Constructor Method: ' . (memory_get_usage(true)/1024/1024)." MB");
    }


    function show(Request $req){
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

    function create(Request $req){
        $validate = $req->validate([
            'firstName'=>'required|string',
            "number"=>'required',
            "email"=>'required|email',
          ]);
        try {
            //code...

                $newClient=new client;
                $newClient->firstName=$req->firstName;//"firstName" => null
                $newClient->lastName=$req->lastName;               //"lastName" => null
                $newClient->number=$req->number;                //"number" => null
                $newClient->familyOrOrgnization=$req->familyOrOrgnization;    //"familyOrOrgnization" => null
                $newClient->email=$req->email;                  //"email" => null
                $newClient->panCardNumber=$req->panCardNumber;                //"panCardNumber" => null
                $newClient->aadharCardNumber=$req->aadharCardNumber;             //"aadharCardNumber" => null
                $newClient->occupation=$req->occupation;              //"occupation" => "Select"
                $newClient->kycStatus=$req->kycStatus;              //"kycStatus" => "Select"
                $newClient->anulIncome=$req->anulIncome;            //"anulIncome" => null
                $newClient->referredBy=$req->referredBy;              //"referredBy" => null
                $newClient->totalInvestment=$req->totalInvestment;        //"totalInvestment" => null
                $newClient->comments=$req->comments;                //"comments" => null
                $newClient->relationshipManager=$req->relationshipManager;    //"relationshipManager" => "Mo Arfat Ansari"
                $newClient->serviceRM=$req->serviceRM;              //"serviceRM" => null
                $newClient->totalSIP=$req->totalSIP;              //"totalSIP" => null
                $newClient->primeryContactPerson=$req->primeryContactPerson;   //"primeryContactPerson" => null
                $newClient->meetinSchedule=$req->meetinSchedule;         //"meetinSchedule" => "Select"
                $newClient->firstMeetingDate=$req->firstMeetingDate;       //"firstMeetingDate" => null
                $newClient->typeOfRelation=$req->typeOfRelation;         //"typeOfRelation" => "Select"
                $newClient->maritalStatus=$req->maritalStatus;          //"maritalStatus" => "Select"
                $newClient->save();

                // Now save addresses
                if ($req->addresses && is_array($req->addresses)) {
                    foreach ($req->addresses as $address) {
                        $clientAddress = new ClientAddress();
                        $clientAddress->client_id = $newClient->id; // get newly created client's id
                        $clientAddress->address_type = $address['address_type'] ?? null;
                        $clientAddress->street = $address['street'] ?? null;
                        $clientAddress->area = $address['area'] ?? null;
                        $clientAddress->city = $address['city'] ?? null;
                        $clientAddress->state = $address['state'] ?? null;
                        $clientAddress->postal_code = $address['postal_code'] ?? null;
                        $clientAddress->country = $address['country'] ?? null;
                        $clientAddress->save();
                    }
                }

                return redirect()->route("client.list")->with('success','Contact Create Successfully');
            }catch (\Throwable $e) {
                   dd($e);
                   return back()->with('error','form is not create successfully');
                }
    }

    public function editContact($id){
        $data = client::where('id',$id)->find($id);
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

        $newClient->syncStatus=  $newClient->syncStatus=="Synced" ?("Pending"):($newClient->syncStatus);
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
