<?php

namespace App\Http\Controllers;

use App\DataTables\SyncContactsDataTable;
use App\Jobs\importFormGoogleJob;
use App\Jobs\pushToGoogleJob;
use App\Models\client;
use App\Models\clientContatSyncHistory;
use App\Models\GoogleAuth;
use App\Services\GoogleService;
use Exception;
use Google\Service\PeopleService;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class AjaxRequestController extends Controller
{
    protected $isProcessing=false;
    protected $updateCountToGoogle=0;
    protected $createCountToGoogle=0;
    protected $updateCountToCrm=0;
    protected $createCountToCrm=0;
    protected $googleToken;

    protected $clientContactSyncHistoryTable;

    function __construct()
    {
        $this->googleToken=GoogleAuth::orderBy('id', 'desc')->get()->first();
        $this->clientContactSyncHistoryTable=new clientContatSyncHistory();

        Log::info('Ajac Controller Constructor Method: ' . (memory_get_usage(true)/1024/1024)." MB");
    }

    public function index(SyncContactsDataTable $dataTable){
        try {
            //If table is empty then sign in user
            if (!$this->googleToken || $this->googleToken == null) {
                // $this->redirectToGoogle();
                return redirect()->route('client.redirect');
            }
            Log::info(' Before Ajax Index Return: ' .(memory_get_usage(true)/1024/1024)." MB");
            // return view('client.enjayDesign');
            return $dataTable->render('client.enjayDesign');

        } catch (\Throwable $th) {
            dd($th);
        }

    }

    public function refreshReq(){
        try {
            Log::info('Starting  Refresh Method in Ajax Contrller: ' .(memory_get_usage(true)/1024/1024)." MB");

            $lastSync=$this->clientContactSyncHistoryTable::orderBy('created_at','desc')->first();
            $lastSyncChangesDeteted=($lastSync->created?? 0) + ($lastSync->updated?? 0) + ($lastSync->deleted ?? 0) ;

            $nextSynToken=clientContatSyncHistory::orderBy('id', 'desc')->get('synToken')->first();
            $TotalcontactInGoogle=(new GoogleService())->getContacts($this->googleToken, 1, ['names']);
            $pendingChangesOnGoogle=(new GoogleService())->getContacts($this->googleToken, 1, ['names'],null,$nextSynToken->synToken??null);

            $crmTotalClient=Client::count();
            $pendingChangesOnCRM= Client::where('syncStatus','Pending')->orWhere('syncStatus','Not Synced')->count(); // it have to calculate
            $crmTotalClientSynced=Client::where('syncStatus','Synced')->count();

            $TotalcontactInGoogle=$TotalcontactInGoogle->totalPeople ?? 0;
            $pendingChangesOnGoogle=$pendingChangesOnGoogle->totalPeople??0;
            $remanigToImportFromGoogle= ($TotalcontactInGoogle - $crmTotalClient) > 0 ? ($TotalcontactInGoogle - $crmTotalClient) : (0);



            $data=['crm'=>$crmTotalClient,
                    'TotalcontactInGoogle'=>$TotalcontactInGoogle,
                    'pendingChangesOnGoogle'=>$pendingChangesOnGoogle,
                    'pendingChangesOnCRM'=>$pendingChangesOnCRM,
                    'remanigToImportFromGoogle'=>$remanigToImportFromGoogle,
                    'lastSync'=>$lastSync,
                    'lastSyncChangesDeteted'=>$lastSyncChangesDeteted,
                    'crmTotalClientSynced'=>$crmTotalClientSynced,
                    'error'=>0,
            ];

            Log::info('Before Resposnse Refresh Method: ' . (memory_get_usage(true)/1024/1024)." MB");

            return response()->json([
                    'status'=>true,
                    'message'=>"Refreshed",
                    'error'=>false,
                    'data'=>$data,
                ]);
             //code...
        } catch (Exception $e) {
            Log::info("Error During Refresh Data Called :".$e);
            return response()->json([
                'status'=>false,
                'error'=>true,
                'massage'=>$e,
                'data'=>[],
            ]);
        }
    }

    public function synNowBoth(){
        try {
            $this->isProcessing=true;
           $batchCount = 0;

            $googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();
           // Create an empty history row
            $syncHistory = new clientContatSyncHistory();
            $syncHistory->save();


            $createIds = Client::where('syncStatus', 'Not Synced')->pluck('id')->toArray();
            $updateIds = Client::where('syncStatus', 'Pending')->pluck('id')->toArray();

            $createChunks = array_chunk($createIds, 80);
            $updateChunks = array_chunk($updateIds, 80);


            foreach ($createChunks as $chunk) {
                pushToGoogleJob::dispatch($googleToken, $chunk, [], $syncHistory->id)->delay(now()->addMinutes(1));
                $batchCount++;
            }

            foreach ($updateChunks as $chunk) {
                pushToGoogleJob::dispatch($googleToken, [], $chunk, $syncHistory->id)->delay(now()->addMinutes(1));
                $batchCount++;
            }

            // import form Google Job
            importFormGoogleJob::dispatch($googleToken, $syncHistory->id);
            $batchCount++;

            // Update the batches count
            $syncHistory->batches = $batchCount;
            $syncHistory->save();

            return response()->json([
                    'status'=>true,
                    'message'=>"Sync Started ",
                    'error'=>false,
                    'data'=>[],
                ]);
                 //code...
        } catch (Exception $e) {
            return response()->json([
                'status'=>false,
                'error'=>true,
                'massage'=>$e,
                'data'=>[],
            ]);
        }
    }

    // Push To Google Function Ready
    public function pushToGoogle(){
        try {
            $this->isProcessing=true;

            $googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();

            $createIds = Client::where('syncStatus', 'Not Synced')->pluck('id')->toArray();
            $updateIds = Client::where('syncStatus', 'Pending')->pluck('id')->toArray();
            if (count($createIds)==0 && count($updateIds)==0) {
                $this->isProcessing=false;
                return response()->json([
                    'status'=>true,
                    'message'=>"No data To Push On Google",
                    'error'=>true,
                    'data'=>[
                        'isProcessing'=>$this->isProcessing,
                        'UpdatingToGoogle'=>count($createIds),
                        'CreatingToGoogle'=>count($updateIds),
                    ],
                ]);
            }
            // Create an empty history row
            $syncHistoryNextSynCToken=clientContatSyncHistory::orderBy('id','desc')->get("synToken")->first();
            $syncHistory = new clientContatSyncHistory();
            $syncHistory->synToken=$syncHistoryNextSynCToken->synToken??null;
            $syncHistory->save();



            $createChunks = array_chunk($createIds, 80);
            $updateChunks = array_chunk($updateIds, 80);

            $batchCount = 0;

            foreach ($createChunks as $chunk) {
                pushToGoogleJob::dispatch($googleToken, $chunk, [], $syncHistory->id)->delay(now()->addMinutes(1));
                $batchCount++;
            }

            foreach ($updateChunks as $chunk) {
                pushToGoogleJob::dispatch($googleToken, [], $chunk, $syncHistory->id)->delay(now()->addMinutes(1));
                $batchCount++;
            }


            // Update the batches count
            $syncHistory->batches = $batchCount;
            $syncHistory->save();

            $data=[
                'isProcessing'=>$this->isProcessing,
                'UpdatingToGoogle'=>count($createIds),
                'CreatingToGoogle'=>count($updateIds),
            ];


            Log::info(' Push to Google In Controller Before Return: ' . (memory_get_usage(true)/1024/1024)." MB");
            return response()->json([
                    'status'=>true,
                    'message'=>"Push To Google Stared",
                    'error'=>false,
                    'data'=>$data,
                ]);

        } catch (Exception $e) {
            return response()->json([
                'status'=>false,
                'error'=>true,
                'massage'=>$e,
                'data'=>[],
            ]);
        }
    }

    public function importFromGoogle()
    {   try{
        $googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();
        $newClientSyncHistoyRow=new clientContatSyncHistory();
        $newClientSyncHistoyRow->batches += 1;
        $newClientSyncHistoyRow->save();

        importFormGoogleJob::dispatch($googleToken, $newClientSyncHistoyRow->id);


        unset($googleToken);
        unset($newClientSyncHistoryRow);
        return response()->json([
                'status'=>true,
                'message'=>"Import From Google Contact",
                'error'=>false,
                'data'=>[],
            ]);
             //code...
        } catch (Exception $e) {
            return response()->json([
                'status'=>false,
                'error'=>true,
                'massage'=>$e,
                'data'=>[],
            ]);
        }
    }

    // This for Dispaly Process Bar
     public function syncStatus(){
        try {
            $message="";
            $lastSync=clientContatSyncHistory::orderBy('created_at','desc')->first();
            $status=$lastSync->status??0;
            $batches=$lastSync->batches??0;
            $synced=($lastSync->created??0)+ ($lastSync->updated??0);
            $pending=0; // it have to calculate
            $errors=$lastSync->error??0;

            $startTime=$lastSync->startTime??null;

            if ($batches > 0 && $status==0) {
                $isSynced=false;
                $message="Sync Is In Pendig On Queue";
                $porcessBarPersentage=0;
            }elseif($batches > 0 && $status==1){
                $isSynced=false;
                $message="Syn Is ProcessingIn Queue";
                $processBarWidth = 0;
                $totalTime = $batches * 60; // total expected time in seconds
                $elapsedTime = time() - $startTime; // assuming you saved the start time
                $processBarWidth = ($elapsedTime / $totalTime) * 100;
                $porcessBarPersentage=round($processBarWidth);
            }else{
                $isSynced=true;
                $porcessBarPersentage=100;
                $message="Synced is Completed";
            }

        $data=[
            'isSynced'=>$isSynced,
            'lastSync'=>$lastSync,
            'synced'=>$synced,
            'pending'=>$pending,
            'errors'=>$errors,
            'porcessBarPersentage'=>$porcessBarPersentage,
        ];

        return response()->json([
                'status'=>true,
                'message'=>$message,
                'error'=>false,
                'data'=>$data,
            ]);
             //code...
        } catch (Exception $e) {
            return response()->json([
                'status'=>false,
                'error'=>true,
                'massage'=>$e,
                'data'=>[],
            ]);
        }
    }


    // aut2.0 authenticating
    public function redirectToGoogle()
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->addScope('https://www.googleapis.com/auth/contacts');
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
        $user->name ='Mo Arfat';
        $user->email = 'ArfatAnsari.Code@gmail.com';
        $user->googleAccessToken = $googleUser['access_token'];
        $user->accessTokenExpiresIn = $googleUser['expires_in'];
        $user->googleRefreshToken = $googleUser['refresh_token'];
        $user->refreshTokenExpiresIn = $googleUser['refresh_token_expires_in'];
        $user->save();



        return redirect()->route('ajax.index')->with('success', 'Google account connected successfully.');
    }


    // function for get clinet sync history data
    public function getClinetSyncHistory(){
        $query = $this->clientContactSyncHistoryTable::query();
            // dd($query);
        return DataTables::eloquent($query)
        ->addColumn('action',function($query){
            return "<a href='#'>View</a>";
        })

        ->rawColumns(['action'])
        ->make(true);
    }

    public function SyncContactsTable(SyncContactsDataTable $dataTable)
    {
        return $dataTable->render('client.enjayDesign');
    }




    function test(){
        $client = (new GoogleService())->getGoogleCient($this->googleToken);
        $service = new PeopleService($client);

        $data=clientContatSyncHistory::orderBy('id', 'desc')->get('synToken')->first();
        // if ($data) {
        //     # code...
        //     dd($data->synToken);
        // }else{
        //     dd($data);
        // }

        $params = [
            'resourceName' => 'people/me',
            'personFields' => 'names,emailAddresses,phoneNumbers',
            'pageSize' => 1000,
            'requestSyncToken' => true,
            'syncToken' => $data->synToken, // Use this in future calls
        ];

        $response = $service->people_connections->listPeopleConnections('people/me', $params);

        Log::info('Ajac Controller test Method: ' . (memory_get_usage(true)/1024/1024)." MB");
        dd($response);

        $params = [
            'resourceName' => 'people/me',
            'personFields' => 'names,emailAddresses,phoneNumbers',
            'pageSize' => 1000,
            'syncToken' => $response->getNextSyncToken(),
        ];

        // Store this token for future incremental syncs
        $nextSyncToken = $response->getNextSyncToken();

    }



}
