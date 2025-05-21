<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\CrmApiServices;


class clientController extends Controller
{

    function createForm()
    {
        return view('client.createForm');
    }


    public function create(Request $request)
    {
        try {

            $validate = $request->validate([
                'phone' => 'required',
                'email' => 'required',
            ]);


            $birth_date = Carbon::parse($request->birth_date)->format('d/m/y');

            $address = (new CrmApiServices())->formatAddress($request->addresses);
            $phone_json = (new CrmApiServices())->formatPhones($request->phone_json);
            $email_json = (new CrmApiServices())->formatEmails($request->email_json);
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
                        "birth_date" => $birth_date,
                        "occupation_c" => $request->occupation_c,
                        "adhaar_card_c" => $request->adhaar_card_c,
                        "pancard_c" => $request->pancard_c,
                        "kyc_status_c" => $request->kyc_status_c,
                        "annual_income_c" => $request->annual_income_c,
                        "total_investment_c" => $request->total_investment_c,
                        "comment" => $request->comment,
                        "total_sip_c" => $request->total_sip_c,
                        "meeting_schedule_c" => $request->meeting_schedule_c,
                        "first_meeting_date_c" => $request->first_meeting_date_c,
                        "marital_status_c" => $request->marital_status_c,
                        "anniversary" => $request->anniversary,
                        "protfolio_no_c" => $request->protfolio_no_c,
                        "gender_c" => $request->gender_c,
                        "hierarchy" => "03",
                        "assigned_user_id" => "1",
                        "teamsSet" => "1"
                    ]
                ]
            ];


            //    return response()->json($payload, 200, [], JSON_PRETTY_PRINT);

            //  Step 5: Send to service
            $response = (new CrmApiServices(session('crm_token')))->createContact($payload);

            return redirect(route('ajax.index'))->with('success', "Contact is create successfully");
            // return response()->json(['status' => true, 'message' => 'Contact created', 'data' => $response]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editContact($id)
    {
        // $data = client::where('id',$id)->find($id);
        $payload = [
            'rest_data' => [
                'action' => 'show',
                'module_name' => 'Contact',
                'id' => $id,
                'select_fields' => [
                    "id",
                    "name",
                    "phone",
                    "email_json",
                    "phone_primary",
                    "designation",
                    "birth_date",
                    "first_name",
                    "last_name",
                    "email_primary",
                    "phone_json",
                    "address_type",
                    'address_json',
                    'street',
                    'city',
                    'area',
                    'state',
                    'postal_code',
                    'country',
                    'sync_status_c',
                    'account_id_name',
                    'pancard_c',
                    'adhaar_card_c',
                    'occupation_c',
                    'kyc_status_c',
                    'annual_income_c',
                    'by_person_name _c_name',
                    'total_investment_c',
                    'comment',
                    'assigned_user_id_name',
                    'total_sip_c',
                    'contact_id_name',
                    'meeting_schedule_c',
                    'first_meeting_date_c',
                    'type_of_relation_c',
                    'marital_status_c',
                    'anniversary',
                    'protfolio_no_c',
                    'gender_c',

                ],
                'select_relate_fields' => []
            ]
        ];

        $getdataById = (new CrmApiServices(session('crm_token')))->getContactById($payload);
        $data = $getdataById;

        $nameValueList = $data['entry_list']['name_value_list'] ?? [];
        //    return response()->json($nameValueList, 200, [], JSON_PRETTY_PRINT);

        $contacts = collect($nameValueList)->mapWithKeys(function ($item) {
            return [$item['name'] => $item['value']];
        })->toArray();

        return view("client.createForm", compact('contacts'));
    }
    ///------UpdateForm------------------
    public function UpdateFormContact(Request $request)
    {
        $validate = $request->validate([
            'first_name' => 'required|string',
            "phone" => 'required',
            "email" => 'required|email',
        ]);

        // return response()->json($request->all(), 200, [], JSON_PRETTY_PRINT);


        $address = (new CrmApiServices())->formatAddress($request->addresses);
        $phone_json = (new CrmApiServices())->formatPhones($request->phone_json);
        $email_json = (new CrmApiServices())->formatEmails($request->email_json);
        // return $birth_date;
        $payload = [
            "rest_data" => [
                "module_name" => "Contact",
                "maping_records_upadate" => true,
                "mapping_parent_fields" => [
                    "first_name",
                    "last_name",
                    "designation",
                    "account_id",
                    "phone",
                    "email",
                    "hierarchy",
                    "department"
                ],
                "name_value_list" => [
                    "first_name" => $request->first_name ?? '',
                    "last_name" => $request->last_name ?? '',
                    "designation" => "Developer",
                    "hiddenPhone" => $phone_json,
                    "hiddenEmail" => $email_json,
                    "hiddenAddress" => $address,
                    "sync_status_c" => $request->sync_status_c === 'Synced' ? 'pending' : ($request->sync_status_c ?? 'Not Synced'),
                    "birth_date" => $request->birth_date,
                    "occupation_c" => $request->occupation_c,
                    "adhaar_card_c" => $request->adhaar_card_c,
                    "pancard_c" => $request->pancard_c,
                    "kyc_status_c" => $request->kyc_status_c,
                    "annual_income_c" => $request->annual_income_c,
                    "total_investment_c" => $request->total_investment_c,
                    "comment" => $request->comment,
                    "total_sip_c" => $request->total_sip_c,
                    "meeting_schedule_c" => $request->meeting_schedule_c,
                    "first_meeting_date_c" => $request->first_meeting_date_c,
                    "marital_status_c" => $request->marital_status_c,
                    "anniversary" => $request->anniversary,
                    "protfolio_no_c" => $request->protfolio_no_c,
                    "gender_c" => $request->gender_c,
                    // "hierarchy" => "03",
                    // "assigned_user_id" => "",
                    // "teamsSet" => "1"
                ]
            ]
        ];
        // return $payload;
        $response = (new CrmApiServices(session('crm_token')))->updateContact($request->id, $payload);

        if ($response) {
            return redirect()->route('ajax.index')->with('success', 'CRM Contact updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to update CRM Contact.');
        }
    }
}
