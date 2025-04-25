<?php

namespace App\Http\Controllers;

use App\Models\client;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

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
                    return "<a href=".route('client.edit').">Edit</a>";
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

        return redirect()->route("client.list");
    } catch (\Throwable $e) {
        dd($e);
    }
    }

    public function __destruct() {
        Log::info('Client Controller Distructor Method: ' . (memory_get_usage(true)/1024/1024)." MB");
    }
}
