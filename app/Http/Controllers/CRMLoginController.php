<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\CrmApiServices;
use Illuminate\Support\Facades\Session;

class CRMLoginController extends Controller
{

    protected $crm;

    public function __construct(CrmApiServices $crm)
    {
        $this->crm = $crm;
    }

    public function ViewcrmLogin(){
        return view('client.login');
    }
    public function login(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string',
            'password' => 'required|string',
        ]);

        $data = $this->crm->login($request->user_name, $request->password);

        if ($data && isset($data['status']) && $data['status'] === 200 && isset($data['data']['token'])) {
            session([
                'crm_token' => $data['data']['token'],
                'crm_user' => $data['data']['name'],
            ]);
            return response()->json(['status' => true, 'message' => 'Login successful', 'data' => $data]);
        }

        return response()->json([
            'status' => false,
            'message' => $data['message'] ?? 'Login failed'
        ], 401);
    }

    public function logout(){
         Session::flush();
        return redirect(route('login'))->with('success','logout successfully!');
    }
    // public function fetchCrmContacts()
    // {
    //     $data = $this->crm->getContacts();
    //     $contacts = $data['data'] ?? [];

    //     foreach ($contacts as $contact) {
    //         Client::firstOrCreate(
    //             ['crm_id' => $contact['id']],
    //             ['firstName' => $contact['name'] ?? '', 'syncStatus' => 'Not Synced']
    //         );
    //     }

    //     return view('client.crm_contact_list', compact('contacts'));
    // }

    // public function getCrmContactById($id)
    // {
    //     $data = $this->crm->getContactById($id);
    //     $contacts = $data['entry_list']['name_value_list'] ?? [];

    //     return view('client.Crmform', compact('contacts'));
    // }

    // public function createCrmContact(Request $request)
    // {
    //     $data = [
    //         "first_name" => $request->first_name,
    //         "last_name" => $request->last_name,
    //         "phone_primary" => $request->phone,
    //         "email_primary" => $request->email,
    //         "designation" => "Developer",
    //         "comment" => [["description" => "For demo"]],
    //         "attachment1_c" => [[
    //             "filename" => "Screenshot_20250329-123003.png",
    //             "date" => "2025-04-29",
    //             "fileId" => "c9a69daa-1d0a-4c5d-aa6e-6a168f2d960a"
    //         ]],
    //         "hierarchy" => "03",
    //         "lead_source" => "Justdial",
    //         "assigned_user_id" => "1",
    //         "teamsSet" => "1"
    //     ];

    //     $this->crm->createContact($data);

    //     return redirect(route('crmViewList'))->with('success', 'CRM contact created successfully!');
    // }

    // public function updateCrmContact(Request $request)
    // {
    //     $request->validate(["first_name" => 'required']);

    //     $data = [
    //         "first_name" => $request->first_name,
    //         "last_name" => $request->last_name,
    //         "phone" => $request->phone,
    //         "phone_primary" => $request->phone,
    //         "designation" => "Developer",
    //         "attachment1_c" => [[
    //             "filename" => "Screenshot_20250329-123003.png",
    //             "date" => "2025-04-29",
    //             "fileId" => "c9a69daa-1d0a-4c5d-aa6e-6a168f2d960a"
    //         ]],
    //     ];

    //     $this->crm->updateContact($request->id, $data);

    //     $client = Client::where('crm_id', $request->id)->first();
    //     if ($client) {
    //         $client->syncStatus = ($client->syncStatus === 'Synced') ? 'pending' : 'Not Synced';
    //         $client->save();
    //     }

    //     return redirect(route('crmViewList'))->with('success', 'CRM contact updated successfully!');
    // }
}
