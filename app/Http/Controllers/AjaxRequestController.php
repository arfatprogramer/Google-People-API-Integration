<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Google_Client;
use App\Models\client;
use App\Models\GoogleAuth;
use Illuminate\Http\Request;
use App\Jobs\pushToGoogleJob;
use App\Services\GoogleService;
use App\Services\CrmApiServices;
use App\Jobs\importFormGoogleJob;
use Google\Service\PeopleService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use App\Models\clientContatSyncHistory;
use Yajra\DataTables\Facades\DataTables;
use App\DataTables\SyncContactsDataTable;
use App\DataTables\clietsSyncedHistoryDataTable;

class AjaxRequestController extends Controller
{
    protected $isProcessing = false;
    protected $updateCountToGoogle = 0;
    protected $createCountToGoogle = 0;
    protected $updateCountToCrm = 0;
    protected $createCountToCrm = 0;
    protected $googleToken;
    protected $apiToken;

    protected $clientContactSyncHistoryTable;

    function __construct()
    {
        $this->apiToken = session('crm_token');
        $this->googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();
        $this->clientContactSyncHistoryTable = new clientContatSyncHistory();

        Log::info('Ajac Controller Constructor Method: ' . (memory_get_usage(true) / 1024 / 1024) . " MB");
    }

    public function index(Request $req, SyncContactsDataTable $contactsTable, clietsSyncedHistoryDataTable $historyTable)
    {

        try {
            //If table is empty then sign in user
            if (!$this->googleToken || $this->googleToken == null) {
                // $this->redirectToGoogle();
                return redirect()->route('client.redirect');
            }


            if ($req->ajax()) {
                try {
                    $response = (new CrmApiServices($this->apiToken))->getContacts();

                    // Use only the actual data array
                    $data = collect($response['data'] ?? $response);

                    // Filter out any invalid rows (non-array)
                    $cleaned = $data->filter(fn($item) => is_array($item) || is_object($item));

                    return DataTables::of($cleaned)
                        ->addColumn('action', function ($client) {
                            $id = $client['id'] ?? '';
                            return "
                            <button class='px-4 deleteGoogleContact cursor-pointer' data-bs-id='{$id}'>
                                <i class='fas fa-sync-alt  text-[17px] text-gray-500 w-2 h-2 hover:text-blue-500'></i>
                                </button>
                                <a href='" . route('client.edit', $id) . "' class='hover:text-blue-500 text-2xl'>+</a>
                                ";
                        })
                        ->editColumn('email_primary', fn($client) => $client['email_primary'] ?? '-')
                        ->editColumn('phone_primary', fn($client) => $client['phone_primary'] ?? '-')
                        ->editColumn(
                            'created_at',
                            fn($client) =>
                            isset($client['created_at']) ? \Carbon\Carbon::parse($client['created_at'])->format('Y-m-d') : '-'
                        )


                        ->editColumn('sync_status_c', function ($client) {
                            $status = $client['sync_status_c'] ?? 'Not Synced';
                            if ($status === 'Synced') {
                                return '<span class="px-2 py-1 text-xs border border-green-400 text-green-600 rounded-full">Synced</span>';
                            } elseif ($status === 'Pending') {
                                return '<span class="px-2 py-1 text-xs border border-yellow-400 text-yellow-600 rounded-full">Pending</span>';
                            } else {
                                return '<span class="px-2 py-1 text-xs border border-gray-400 text-gray-600 rounded-full">Not Synced</span>';
                            }
                        })

                        ->editColumn('last_sync_c', function ($client) {
                            $value = $client['last_sync_c'] ?? null;

                            try {
                                if (!$value) {
                                    return '<span class="px-2 py-1 text-xs border border-gray-400 text-gray-600 rounded-full">Never</span>';;
                                }
                                $diff = Carbon::parse($value)->diffForHumans(); // e.g. "5 minutes ago"
                                // return '<span class="text-green-600">' . e($diff) . '</span>';
                                return '<span class="px-2 py-1 text-xs border border-green-400 text-green-600 rounded-full">' . e($diff) . '</span>';
                            } catch (\Exception $e) {
                                return '<span class="text-red-500">Invalid date</span>';
                            }
                        })

                        ->rawColumns(['sync_status_c', 'last_sync_c', 'action'])
                        ->make(true);
                } catch (\Throwable $e) {
                    Log::error('AJAX DataTables Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                    return response()->json([
                        'error' => true,
                        'message' => $e->getMessage(),
                    ], 500);
                }
            }

            //   return view('client.enjayDesign');
            return view('client.enjayDesign', [
                'contactsTable' => $contactsTable->html(),
                'historyTable' => $historyTable->html(),

            ]);
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function refreshReq()
    {
        try {
            Log::info('Starting  Refresh Method in Ajax Contrller: ' . (memory_get_usage(true) / 1024 / 1024) . " MB");

            $lastSync = $this->clientContactSyncHistoryTable::orderBy('created_at', 'desc')->first();
            $lastSyncChangesDeteted = ($lastSync->createdAtGoogle ?? 0) + ($lastSync->updatedAtGoogle ?? 0) + ($lastSync->created ?? 0) + ($lastSync->updated ?? 0) + ($lastSync->deleted ?? 0);

            $nextSynToken = clientContatSyncHistory::orderBy('id', 'desc')->get('synToken')->first();
            $TotalcontactInGoogle = (new GoogleService())->getContacts($this->googleToken, 10, ['names']);
            $pendingChangesOnGoogle = (new GoogleService())->getContacts($this->googleToken, 10, ['names'], null, $nextSynToken->synToken ?? null);
            // dd($TotalcontactInGoogle);

            $res = (new CrmApiServices(session('crm_token')))->getContacts();
            $crmTotalClient = $res['meta']['total'] ?? 0;

            $res = (new CrmApiServices(session('crm_token')))->getClietsList(1, 1, "sync_status_c= 'Synced'");
            $crmTotalClientSynced = $res->meta->total ?? 0;
            $res = (new CrmApiServices(session('crm_token')))->getClietsList(1, 1, "sync_status_c='Pending' OR sync_status_c is null OR sync_status_c='Not Synced'");
            $pendingChangesOnCRM = $res->meta->total ?? 0;


            $TotalcontactInGoogle = $TotalcontactInGoogle->totalPeople ?? 0;
            $pendingChangesOnGoogle = $pendingChangesOnGoogle->totalPeople ?? 0;
            $remanigToImportFromGoogle = ($TotalcontactInGoogle - $crmTotalClient) > 0 ? ($TotalcontactInGoogle - $crmTotalClient) : (0);



            $data = [
                'crm' => $crmTotalClient,
                'TotalcontactInGoogle' => $TotalcontactInGoogle,
                'pendingChangesOnGoogle' => $pendingChangesOnGoogle,
                'pendingChangesOnCRM' => $pendingChangesOnCRM,
                'remanigToImportFromGoogle' => $remanigToImportFromGoogle,
                'lastSync' => $lastSync,
                'lastSyncChangesDeteted' => $lastSyncChangesDeteted,
                'crmTotalClientSynced' => $crmTotalClientSynced,
                'error' => 0,
            ];

            Log::info('Before Resposnse Refresh Method: ' . (memory_get_usage(true) / 1024 / 1024) . " MB");

            return response()->json([
                'status' => true,
                'message' => "Refreshed",
                'error' => false,
                'data' => $data,
            ]);
            //code...
        } catch (Exception $e) {
            Log::info("Error During Refresh Data Called :" . $e);
            return response()->json([
                'status' => false,
                'error' => true,
                'massage' => $e,
                'data' => [],
            ]);
        }
    }

    public function synNowBoth()
    {
        try {
            $this->isProcessing = true;
            $batchCount = 0;
            $extimetedTime = 0;
            $totalPending = 0;

            $googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();

            // calculating pending on Google and Add in Pending
            $nextSynToken = clientContatSyncHistory::orderBy('id', 'desc')->get('synToken')->first();
            $pendingChangesOnGoogle = (new GoogleService())->getContacts($this->googleToken, 10, ['names'], null, $nextSynToken->synToken ?? null);
            $totalPending += $pendingChangesOnGoogle->totalPeople ?? 0;

            // Create an empty history row
            $syncHistory = new clientContatSyncHistory();
            $syncHistory->save();

            $batches = Bus::batch([])->name("Synk Both")->dispatch();
            $createPages = 1;
            do {
                $res = (new CrmApiServices($this->apiToken))->getClietsList($createPages, 5, "sync_status_c is null OR sync_status_c='Not Synced'");
                $data = $res->data ?? [];

                $batches->add(new pushToGoogleJob($googleToken, $data, [], $syncHistory->id, $this->apiToken));
                $next = isset($res->links->next);
                $createPages++;
                $batchCount++;
                $pendingToCreate = $res->meta->total ?? 0;
                $extimetedTime += 15; // 15 sec take to process

            } while ($next);
            $updatePages = 1;
            do {
                $response = (new CrmApiServices($this->apiToken))->getClietsList($updatePages, 5, "sync_status_c='Pending'");
                $data = $response->data ?? [];
                $batches->add(new pushToGoogleJob($googleToken, [], $data, $syncHistory->id, $this->apiToken));
                $nextUpdate = isset($response->links->next);
                $updatePages++;
                $batchCount++;
                $pendingToUpdate = $response->meta->total ?? 0;
                $extimetedTime += 15; // 15 sec take to process

            } while ($nextUpdate);
            $totalPending = ($pendingToUpdate + $pendingToCreate);

            // import form Google Job
            $batches->add(new importFormGoogleJob($googleToken, $syncHistory->id, $this->apiToken));
            $batchCount++;
            $extimetedTime += 30; // 30 sec take to process

            // Update the batches count
            $syncHistory->batches = $batchCount;
            $syncHistory->extimetedTime = $extimetedTime;
            $syncHistory->pending = $totalPending;
            $syncHistory->save();

            session(['batch_id' => $batches->id]);

            return response()->json([
                'status' => true,
                'message' => "Sync Started ",
                'error' => false,
                'data' => [],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => true,
                'massage' => $e,
                'data' => [],
            ]);
        }
    }

    // Push To Google Function Ready
    public function pushToGoogle()
    {
        try {
            $extimetedTime = 0;
            $googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();

            // Create an empty history row
            $syncHistoryNextSynCToken = clientContatSyncHistory::orderBy('id', 'desc')->get("synToken")->first();
            $syncHistory = new clientContatSyncHistory();
            $syncHistory->synToken = $syncHistoryNextSynCToken->synToken ?? null;
            $syncHistory->save();

            $batchCount = 0;
            $batches = Bus::batch([])->name('Push To Google')->dispatch();

            $createPages = 1;
            do {
                $res = (new CrmApiServices($this->apiToken))->getClietsList($createPages, 200, "sync_status_c is null OR sync_status_c='Not Synced");
                $data = $res->data ?? [];

                $batches->add(new pushToGoogleJob($googleToken, $data, [], $syncHistory->id, $this->apiToken));
                $next = isset($res->links->next);
                $createPages++;
                $batchCount++;
                $pendingToCreate = $res->meta->total ?? 0;
                $extimetedTime += 15; // 15 sec take to process

            } while ($next);
            $updatePages = 1;
            do {
                $response = (new CrmApiServices($this->apiToken))->getClietsList($updatePages, 200, "sync_status_c='Pending'");
                $data = $response->data ?? [];
                $batches->add(new pushToGoogleJob($googleToken, [], $data, $syncHistory->id, $this->apiToken));
                $nextUpdate = isset($response->links->next);
                $updatePages++;
                $batchCount++;
                $pendingToUpdate = $response->meta->total ?? 0;
                $extimetedTime += 15; // 15 sec take to process

            } while ($nextUpdate);
            $totalPending = ($pendingToUpdate + $pendingToCreate);

            if ($totalPending == 0) {
                $this->isProcessing = false;
                return response()->json([
                    'status' => true,
                    'message' => "No data To Push On Google",
                    'error' => false,
                    'data' => [
                        'isProcessing' => $this->isProcessing,
                        'UpdatingToGoogle' => $pendingToUpdate,
                        'CreatingToGoogle' => $pendingToCreate,
                    ],
                ]);
            }

            // Update the batches count
            $syncHistory->batches = $batchCount;
            $syncHistory->pending = $totalPending;
            $syncHistory->extimetedTime = $extimetedTime;
            $syncHistory->save();

            session(['batch_id' => $batches->id]);

            $data = [
                'isProcessing' => $this->isProcessing,
                'UpdatingToGoogle' => $pendingToUpdate,
                'CreatingToGoogle' => $pendingToCreate,
            ];


            Log::info(' Push to Google In Controller Before Return: ' . (memory_get_usage(true) / 1024 / 1024) . " MB");
            return response()->json([
                'status' => true,
                'message' => "Push To Google Stared",
                'error' => false,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => true,
                'massage' => $e,
                'data' => [],
            ]);
        }
    }

    public function importFromGoogle()
    {
        try {
            Log::info('Import Form Google Function ');

            $googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();
            $nextSynToken = clientContatSyncHistory::orderBy('id', 'desc')->get('synToken')->first();
            $pendingChangesOnGoogle = (new GoogleService())->getContacts($this->googleToken, 10, ['names'], null, $nextSynToken->synToken ?? null);

            $newClientSyncHistoyRow = new clientContatSyncHistory();
            $newClientSyncHistoyRow->batches += 1;
            $newClientSyncHistoyRow->pending = $pendingChangesOnGoogle->totalPeople ?? 0;
            $newClientSyncHistoyRow->extimetedTime = 30;
            $newClientSyncHistoyRow->save();

            $batches = Bus::batch([
                new importFormGoogleJob($googleToken, $newClientSyncHistoyRow->id, $this->apiToken),
            ])->name('Import From Google')->dispatch();

            session(['batch_id' => $batches->id]);

            return response()->json([
                'status' => true,
                'message' => "Import From Google Contact",
                'error' => false,
                'data' => [],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => true,
                'message' => $e,
                'data' => [],
            ]);
        }
    }

    public function singleSyncById(Request $request)
    {
        try {
            $client = (new GoogleService())->getGoogleCient($this->googleToken);
            $peopleService = new PeopleService($client);

            $cliet_id = $request->Cliet_id;
            $deletedReSync = $request->deletedReSync;
            $payload = [
                'rest_data' => [
                    'action' => 'show',
                    'module_name' => 'Contact',
                    'id' => $cliet_id,
                    "select_fields" => [
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

                    ],

                    'select_relate_fields' => []
                ]
            ];
            $response = (new CrmApiServices(session('crm_token')))->getContactById($payload);
            $data = $response;
            $contact = $data['entry_list']['name_value_list'] ?? [];

            $newContact = [];
            foreach ($contact as $data) {
                $name = $data['name'];
                $newContact[$name] = $data['value'];
            }
            $temp = json_decode(json_encode($newContact));;

            $person = (new GoogleService())->getPerson($temp);
            if ($contact['sync_status_c']['value'] == "Pending" && $contact['resource_name_c']['value']) {

                $person->setEtag($contact['etag_c']['value']);
                $updated = $peopleService->people->updateContact($contact['resource_name_c']['value'], $person, [
                    'updatePersonFields' => 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies',
                ]);
            } elseif ($contact['sync_status_c']['value'] == "Not Synced" || $deletedReSync) {
                $updated = $peopleService->people->createContact($person, [
                    'personFields' => 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'error' => true,
                    'message' => "This Data is Deleted From Google But Remaning in CRM",
                    'data' => [],
                ]);
            }
            $res = (new CrmApiServices($this->apiToken))->updateSyncStatus($cliet_id, $updated->resourceName, $updated->etag, 'Synced');

            return response()->json([
                'status' => true,
                'message' => "Completed",
                'error' => false,
                'data' => $updated,
                'data' => [],
            ]);
        } catch (Exception $e) {

            $message = $e->getMessage();
            $ErrorCode = $e->getCode();
            if ($ErrorCode === 404) { //404  Requested entity was not found
                $message = 'This Contact Is Deleted From Google';
                $res = (new CrmApiServices($this->apiToken))->updateSyncStatus($cliet_id, null, null, 'Deleted');
            }
            return response()->json([
                'status' => false,
                'error' => true,
                'message' => $message,
                'data' => [],
            ]);
        }
    }

    // This for Dispaly Process Bar
    public function syncStatus()
    {
        try {
            $progress = 100;
            $processing = false;
            $batchId = session('batch_id');
            if (!$batchId == null) {
                $batch = Bus::findBatch($batchId);
                $progress = $batch->progress();

                if ($batch->cancelled()) {  //cancelled
                    $processing = false;
                } elseif ($batch->finished()) {  // Completed
                    $processing = false;
                } else {   //Processing
                    $processing = true;
                }
            }

            $message = "Synced status function";

            $lastSync = clientContatSyncHistory::orderBy('created_at', 'desc')->first();
            $estimatedTime = $lastSync->extimetedTime ?? 0; // total estimated time in seconds
            $startTime = $lastSync->startTime ?? null;
            $remainingTime = 0;
            if ($startTime && $estimatedTime > 0) {
                $elapsedTime = time()-$startTime;
                $remainingTime = max(0, $estimatedTime - $elapsedTime); // never negative
            }
            // Format to MM:SS Min
            $minutes = str_pad(floor($remainingTime / 60), 2, '0', STR_PAD_LEFT);
            $seconds = str_pad($remainingTime % 60, 2, '0', STR_PAD_LEFT);
            $formattedRemainingTime = "{$minutes}:{$seconds} Min";

            $data = [
                'processing' => $processing,
                'progress' => $progress,
                'lastSync' => $lastSync,
                'extimetedTime' => $formattedRemainingTime,
            ];

            return response()->json([
                'status' => true,
                'message' => $message,
                'error' => false,
                'data' => $data,
            ]);
        } catch (Exception $e) {

            return response()->json([
                'status' => false,
                'error' => true,
                'massage' => $e->getMessage(),
                'data' => [],

            ]);
        }
    }


    // aut2.0 authenticating
    public function redirectToGoogle()
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setRedirectUri(config('services.google.redirect'));
                $client->addScope([
            // 'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/contacts',
            'https://www.googleapis.com/auth/contacts.other.readonly' //  Required for otherContacts
        ]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return redirect($client->createAuthUrl());
    }

    //auth 2.0 authonticatin redirect handel function
    public function handleGoogleCallback(Request $request)
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        // Exchange the authorization code for an access token
        $googleUser = $client->fetchAccessTokenWithAuthCode($request->get('code'));

        //save token and user info however you need
        $user = new GoogleAuth;
        $user->google_id = $googleUser['created'];
        $user->name = 'Mo Arfat';
        $user->email = 'ArfatAnsari.Code@gmail.com';
        $user->googleAccessToken = $googleUser['access_token'];
        $user->accessTokenExpiresIn = $googleUser['expires_in'];
        $user->googleRefreshToken = $googleUser['refresh_token'];
        $user->refreshTokenExpiresIn = $googleUser['refresh_token_expires_in'];
        $user->save();



        return redirect()->route('ajax.index')->with('success', 'Google account connected successfully.');
    }


    // function for get clinet sync history data
    public function getClinetSyncHistory()
    {
        $query = $this->clientContactSyncHistoryTable::query();
        // dd($query);
        return DataTables::eloquent($query)
            ->addColumn('action', function ($query) {
                return "<a href='#'>View</a>";
            })

            ->rawColumns(['action'])
            ->make(true);
    }


    ///------softDelete---crm--or----googlecontact---delete---------------
    public function softDeletOrGoogleContact(Request $request)
    {

        $contactSoftDelete = filter_var($request->delete_contact, FILTER_VALIDATE_BOOLEAN);

        if ($contactSoftDelete === true) {

            $client = (new GoogleService())->getGoogleCient($this->googleToken);
            // Assume you already have the authenticated Google Client
            $peopleService = new PeopleService($client);

            $contact = client::find($request->client_id);  // or however you fetch


            try {

                if (!empty($contact->resourceName)) {
                    $delete = $peopleService->people->deleteContact($contact->resourceName);

                    if ($delete) {
                        $contact->delete();
                    }
                    return response()->json(['success' => true, 'message' => ' Google and CRM Contact deleted successfully.']);
                } else {

                    $contact->delete();

                    return response()->json(['success' => true, 'message' => 'CRM Contact is delete successfully', 'resourceName']);
                }
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            // return response()->json(['message' => 'Google Contact deleted successfully.','resource'=>$resourceName]);


        } else {

            $contact = client::find($request->client_id);
            if ($contact) {
                $contact->delete(); // Soft delete karega (deleted_at fill karega)
                return response()->json([
                    'success' => true,
                    'message' => ' CRM Contact soft deleted successfully.',
                    'data' => $contactSoftDelete
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found.'
                ]);
            }
        }
    }


    public function cancelPendingGoogleSync()
    {
        try {

            $lastRow = clientContatSyncHistory::latest('id')->first();
            $lastRow->delete();

            $batchId = session('batch_id');
            if (!$batchId == null) {
                $batch = Bus::findBatch($batchId);
                $batch->cancel();
            }

            return response()->json([
                'status' => true,
                'error' => false,
                'message' => "Process canceld",
                'data' => $lastRow,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => true,
                'message' => $e,
                'data' => [],
            ]);
        }
    }

    public function deleteDataFromCRm()
    {
        try {

            $response = (new CrmApiServices($this->apiToken))->getClietsList(1, 200,"sync_status_c = 'pending'");
            $resData = $response->data ?? [];
            $ids = [];
            foreach ($resData as $data) {
                $ids[] = $data->id;
            }
            foreach ($ids as $id) {
                // $res = (new CrmApiServices($this->apiToken))->deleteFromCRM($id);
                $res = (new CrmApiServices($this->apiToken))->updateSyncStatus($id,null,null,null);
            }

            return response()->json([
                'status' => true,
                'error' => false,
                'message' => "Deleted All Data From CRM",
                'data' => [],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'error' => true,
                'message' => $e,
                'data' => [],
            ]);
        }
    }
}
