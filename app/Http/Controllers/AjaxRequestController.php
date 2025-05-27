<?php

namespace App\Http\Controllers;

use Exception;
use Google_Client;
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
use App\DataTables\SyncContactsDataTable;
use App\DataTables\clietsSyncedHistoryDataTable;
use Illuminate\Support\Facades\Cache;

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
    }

    public function index(SyncContactsDataTable $contactsTable, clietsSyncedHistoryDataTable $historyTable)
    {

        try {
            //If table is empty then sign in user
            if (!$this->googleToken || $this->googleToken == null) {
                // $this->redirectToGoogle();
                return redirect()->route('client.redirect');
            }

            //   return view('client.enjayDesign');
            return view('client.enjayDesign', [
                'contactsTable' => $contactsTable->html(),
                'historyTable' => $historyTable->html(),

            ]);
        } catch (\Throwable $th) {
            dump($th);
        }
    }

    public function refreshReq()
    {
        try {
            $res = (new CrmApiServices(session('crm_token')))->getClietsList(1, 1, "sync_status_c='Pending' OR sync_status_c is null OR sync_status_c='Not Synced'");
            $pending = $res->meta->total ?? 0;

            $pending_value_in_cahce = Cache::get("crm_total_pending");
            if ($pending != $pending_value_in_cahce) {
                Cache::forget('crm_total_pending');
                Cache::forget('crm_total_clients');
                Cache::forget('crm_total_synced');
                Cache::forget('clients');
            }
            $pendingChangesOnCRM = Cache::rememberForever('crm_total_pending', function () use ($pending) {
                return $pending;
            });


            $lastSync = $this->clientContactSyncHistoryTable::orderBy('created_at', 'desc')->first();
            $lastSyncChangesDeteted = ($lastSync->createdAtGoogle ?? 0) + ($lastSync->updatedAtGoogle ?? 0) + ($lastSync->created ?? 0) + ($lastSync->updated ?? 0) + ($lastSync->deleted ?? 0);

            $nextSynToken = clientContatSyncHistory::orderBy('id', 'desc')->get('synToken')->first();

            $pending = (new GoogleService())->getContacts($this->googleToken, 10, ['names'], null, $nextSynToken->synToken ?? null);
            $pending = $pending->totalPeople ?? 0;
            $pending_on_google = cache()->get('pending_on_google');
            if ($pending != $pending_on_google) {
                Cache::forget('pending_on_google');
                Cache::forget('total_contacts_on_google');
                 Cache::forget('crm_total_pending');
                Cache::forget('crm_total_clients');
                Cache::forget('crm_total_synced');
                Cache::forget('clients');
            }
            $pendingChangesOnGoogle = Cache::rememberForever("pending_on_google", function () use ($pending) {
                return $pending;
            });


            $TotalcontactInGoogle = Cache::rememberForever("total_contacts_on_google", function () {
                $TotalcontactInGoogle = (new GoogleService())->getContacts($this->googleToken, 10, ['names']);
                return  $TotalcontactInGoogle->totalPeople ?? 0;
            });


            $crmTotalClient = Cache::rememberForever('crm_total_clients', function () {
                $res = (new CrmApiServices(session('crm_token')))->getContacts();
                return $res['meta']['total'] ?? 0;
            });
            $crmTotalClientSynced = Cache::rememberForever('crm_total_synced', function () {
                $res = (new CrmApiServices(session('crm_token')))->getClietsList(1, 1, "sync_status_c= 'Synced'");
                return $res->meta->total ?? 0;
            });



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
            $totalPending = 0;
            // Create an empty history row
            $syncHistory = new clientContatSyncHistory();
            $syncHistory->save();

            $batches = Bus::batch([])->name("Sync Both")->dispatch();

            $createPages = 1;
            do {
                $res = (new CrmApiServices($this->apiToken))->getClietsList($createPages, 20, "sync_status_c is null OR sync_status_c = 'not synced'");
                $createData = $res->data ?? [];
                $pendingToCreate = $res->meta->total ?? 0;
                $next = isset($res->links->next);

                if (count($createData) == 0) {
                    continue;
                }


                $batches->add(new pushToGoogleJob($this->googleToken, [], [], $syncHistory->id, $this->apiToken));
                $batches->add(new pushToGoogleJob($this->googleToken, $createData, [], $syncHistory->id, $this->apiToken));
                $createPages++;
            } while ($next);
            $updatePages = 1;
            do {
                $response = (new CrmApiServices($this->apiToken))->getClietsList($updatePages, 100, "sync_status_c='Pending'");
                $updatedData = $response->data ?? [];
                $nextUpdate = isset($response->links->next);
                $pendingToUpdate = $response->meta->total ?? 0;
                if (count($updatedData) == 0) {
                    continue;
                }
                $batches->add(new pushToGoogleJob($this->googleToken, [], $updatedData, $syncHistory->id, $this->apiToken));
                $updatePages++;
            } while ($nextUpdate);
            $totalPending = ($pendingToUpdate + $pendingToCreate);

            // import form Google Job
            $nextSynToken = clientContatSyncHistory::orderBy('id', 'desc')->get('synToken')->first();
            $personFields = ['names,phoneNumbers,emailAddresses,userDefined,organizations,biographies,addresses,birthdays,urls,relations'];
            $nextPageToken = false;
            do {
                $googleContacts = (new GoogleService())->getContacts($this->googleToken, 1000, $personFields, $nextPageToken, $nextSynToken->synToken ?? null);
                $nextPageToken = $googleContacts->nextPageToken ?? false;

                $DataFromGoogle = $googleContacts->connections ?? [];
                if (count($DataFromGoogle) == 0) {
                    continue;
                }
                sleep(1);
                $totalPending += count($DataFromGoogle);
                $batches->add(new importFormGoogleJob($DataFromGoogle, $syncHistory->id, $this->apiToken));
            } while ($nextPageToken);



            // Update the batches count
            $syncHistory->extimetedTime = $totalPending * 5;
            $syncHistory->pending = $totalPending;
            $syncHistory->save();

            if ($totalPending == 0) {
                $this->isProcessing = false;
                return response()->json([
                    'status' => true,
                    'message' => "No data To Sync",
                    'error' => false,
                    'data' => [],
                ]);
            }


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

            $batches = Bus::batch([])->name('Push To Google')->dispatch();

            $createPages = 1;
            do {
                $res = (new CrmApiServices($this->apiToken))->getClietsList($createPages, 100, "sync_status_c is null OR sync_status_c='Not Synced' ");
                $createdData = $res->data ?? [];
                $next = isset($res->links->next);
                $pendingToCreate = $res->meta->total ?? 0;
                if (count($createdData) == 0) {
                    continue;
                }
                $batches->add(new pushToGoogleJob($googleToken, [], [], $syncHistory->id, $this->apiToken));
                $batches->add(new pushToGoogleJob($googleToken, $createdData, [], $syncHistory->id, $this->apiToken));
                $createPages++;
                $extimetedTime += ($pendingToCreate * 5); // 15 sec take to process

            } while ($next);
            $updatePages = 1;
            do {
                $response = (new CrmApiServices($this->apiToken))->getClietsList($updatePages, 200, "sync_status_c='Pending'");
                $updatedData = $response->data ?? [];
                $nextUpdate = isset($response->links->next);
                $pendingToUpdate = $response->meta->total ?? 0;
                if (count($updatedData) == 0) {
                    continue;
                }
                $batches->add(new pushToGoogleJob($googleToken, [], $updatedData, $syncHistory->id, $this->apiToken));
                $extimetedTime += ($pendingToUpdate * 5); // 15 sec take to process
                $updatePages++;
            } while ($nextUpdate);
            $totalPending = ($pendingToUpdate + $pendingToCreate);



            // Update the batches count
            $syncHistory->pending = $totalPending;
            $syncHistory->extimetedTime = $extimetedTime;
            $syncHistory->save();

            if ($totalPending == 0) {
                $this->isProcessing = false;
                return response()->json([
                    'status' => true,
                    'message' => "No data To Push On Google",
                    'error' => false,
                    'data' => [],
                ]);
            }

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
            dump($e);
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

            $pendingOnGoogle = 0;
            $syncHistory = new clientContatSyncHistory();
            $syncHistory->save();

            $googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();
            $nextSynToken = clientContatSyncHistory::orderBy('id', 'desc')->get('synToken')->first();

            $batches = Bus::batch([
                new pushToGoogleJob($googleToken, [], [], null, $this->apiToken)
            ])->name('Import From Google')->dispatch();

            $personFields = ['names,phoneNumbers,emailAddresses,userDefined,organizations,biographies,addresses,birthdays,urls,relations'];
            $nextPageToken = false;
            do {
                $googleContacts = (new GoogleService())->getContacts($this->googleToken, 1000, $personFields, $nextPageToken, $nextSynToken->synToken ?? null);
                $nextPageToken = $googleContacts->nextPageToken ?? false;
                sleep(1);
                $DataFromGoogle = $googleContacts->connections ?? [];
                $pendingOnGoogle = count($DataFromGoogle);
                $batches->add(new importFormGoogleJob($DataFromGoogle, $syncHistory->id, $this->apiToken));
            } while ($nextPageToken);



            $syncHistory->pending = $pendingOnGoogle;
            $syncHistory->synToken = $googleContacts->nextSyncToken ?? null;
            $syncHistory->extimetedTime = $pendingOnGoogle * 5;
            $syncHistory->save();

            if ($pendingOnGoogle == 0) {
                $this->isProcessing = false;
                return response()->json([
                    'status' => true,
                    'message' => "No data To Push On Google",
                    'error' => false,
                    'data' => [],
                ]);
            }

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
                        "pancard_c",
                        "adhaar_card_c",
                        "kyc_status_c",
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
                $elapsedTime = time() - $startTime;
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

            // $response = (new CrmApiServices($this->apiToken))->getClietsList(1,500,null);
            $response = (new CrmApiServices($this->apiToken))->getClietsList(1, 20, "sync_status_c = 'synced'");
            // $response = (new CrmApiServices($this->apiToken))->getClietsList(1, 20,"sync_status_c is null OR sync_status_c='Not Synced'");
            $resData = $response->data ?? [];
            $ids = [];
            foreach ($resData as $data) {

                $ids[] = $data->id;
            }

            foreach ($ids as $id) {
                // $res = (new CrmApiServices($this->apiToken))->deleteFromCRM($id);
                $res = (new CrmApiServices($this->apiToken))->updateSyncStatus($id, null, null, null, null);
            }

            return response()->json([
                'status' => true,
                'error' => false,
                'message' => "Deleted All Data From CRM",
                'data' => [],
            ]);
        } catch (Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'error' => true,
                'message' => $e,
                'data' => [],
            ]);
        }
    }
}
