<?php

namespace App\Http\Controllers;

use App\Models\client;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class clientController extends Controller
{
    function show(Request $req){
        if ($req->ajax()) {
            // $clients = Client::select([
            //     'firstName',
            //     'lastName',
            //     'number',
            //     'email',
            //     'panCard',
            //     'aadharCard',
            //     'ocupation',
            //     'kycStatus',
            //     'anulIncome',
            //     'reffredBy',
            //     'totalInvestment',
            //     'relationshipManager',
            //     'serviceRM',
            //     'typeOfRelation'
            // ]);

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
}
