<?php

namespace App\Http\Controllers;

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

    public function index(){
        try {
            //If table is empty then sign in user
            if (!$this->googleToken || $this->googleToken == null) {
                // $this->redirectToGoogle();
                return redirect()->route('client.redirect');
            }
            Log::info(' Before Ajax Index Return: ' .(memory_get_usage(true)/1024/1024)." MB");
            return view('client.enjayDesign');

        } catch (\Throwable $th) {
            dd($th);
        }

    }

    public function refreshReq(){
        try {
            Log::info('Starting  Refresh Method in Ajax Contrller: ' .(memory_get_usage(true)/1024/1024)." MB");

            $contact=(new GoogleService())->getContacts($this->googleToken, 1, ['names']);
            $crmTotalClient=Client::count();
            $google=$contact->totalItems ?? 0;
            $pending= abs($crmTotalClient - $google);
            $lastSync=$this->clientContactSyncHistoryTable::orderBy('created_at','desc')->first();
            // if First time create no Data Will be found
            $lastSyncChangesDeteted=($lastSync->created?? 0) + ($lastSync->updated?? 0) + ($lastSync->deleted ?? 0) ;
            $data=['crm'=>$crmTotalClient,
                    'google'=>$google,
                    'pending'=>$pending,
                    'lastSync'=>$lastSync,
                    'lastSyncChangesDeteted'=>$lastSyncChangesDeteted,
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
           //Import From Google

            $googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();

            //instang HistoryTable
            $newClientSyncHistoyRow=new clientContatSyncHistory();

            $newClientSyncHistoyRow->batches += 1;
            $newClientSyncHistoyRow->save();

            $clientSyncHistoyId=clientContatSyncHistory::orderBy('id', 'desc')->get('id')->first();
            importFormGoogleJob::dispatch($googleToken, $clientSyncHistoyId->id);

            // PushToGoogle
            $createIdList = Client::whereNull('resourceName')->pluck('id')->toArray();
            $updateIdList = Client::whereColumn('updated_at', '>', 'updateFlag')->pluck('id')->toArray();

            $chunks1 = array_chunk($createIdList, 80);
            $chunks2=array_chunk($updateIdList,80);

            $lastRowInContactSyncHistoryTable=clientContatSyncHistory::where('id',$clientSyncHistoyId->id)->first();

            //creating job for Create Contact on Google
            foreach($chunks1 as $chunk){
                pushToGoogleJob::dispatch($googleToken, $chunk, [])
                        ->delay(now()->addMinutes(1));
                $lastRowInContactSyncHistoryTable->batches +=1;
            }
            // creating job for Update contacts to google
            foreach($chunks2 as $chunk){
                pushToGoogleJob::dispatch($googleToken, [], $chunk)
                ->delay(now()->addMinutes(1));
                $lastRowInContactSyncHistoryTable->batches +=1;
            }

            $lastRowInContactSyncHistoryTable->save();

            return response()->json([
                    'status'=>true,
                    'message'=>"Sync Started ",
                    'error'=>true,
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
            $createIdList = Client::whereNull('resourceName')->pluck('id')->toArray();
            $updateIdList = Client::whereColumn('updated_at', '>', 'updateFlag')->pluck('id')->toArray();

            $chunks1 = array_chunk($createIdList, 80);
            $chunks2=array_chunk($updateIdList,80);

            $newClientSyncHistoryRow=new clientContatSyncHistory();
            $newClientSyncHistoryRow->save();

            $id=clientContatSyncHistory::orderBy('id','desc')->select('id')->get()->first();
            $lastRowInContactSyncHistoryTable=clientContatSyncHistory::where('id',$id->id)->first();

            // $test=clientContatSyncHistory::where('id',($id->id-1))->first();




            //creating job for Create Contact on Google
            foreach($chunks1 as $chunk){
                pushToGoogleJob::dispatch($googleToken, $chunk, [])
                        ->delay(now()->addMinutes(1));
                $lastRowInContactSyncHistoryTable->batches +=1;
            }
            // creating job for Update contacts to google
            foreach($chunks2 as $chunk){
                pushToGoogleJob::dispatch($googleToken, [], $chunk)
                ->delay(now()->addMinutes(1));
                $lastRowInContactSyncHistoryTable->batches +=1;
            }

            $lastRowInContactSyncHistoryTable->save();

            $data=[
                'isProcessing'=>$this->isProcessing,
                'UpdatingToGoogle'=>count($updateIdList),
                'CreatingToGoogle'=>count($createIdList),
            ];


            Log::info(' Push to Google In Controller Before Return: ' . (memory_get_usage(true)/1024/1024)." MB");
            return response()->json([
                    'status'=>true,
                    'message'=>"Push To Google Stared",
                    'error'=>true,
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

        $clientSyncHistoyId=clientContatSyncHistory::orderBy('id', 'desc')->get('id')->first();

        importFormGoogleJob::dispatch($googleToken, $clientSyncHistoyId->id);
        $data=[];

        unset($googleToken);
        unset($newClientSyncHistoryRow);
        return response()->json([
                'status'=>true,
                'message'=>"Import From Google Contact",
                'error'=>true,
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

    // This for Dispaly Process Bar
     public function syncStatus(){
        try {
            $lastSync=clientContatSyncHistory::orderBy('created_at','desc')->first();
            $isProcessing=$this->isProcessing;

        $data=[
            'isProcessing'=>$isProcessing,
            'lastSync'=>$lastSync,
        ];

        return response()->json([
                'status'=>true,
                'message'=>"Sync Status",
                'error'=>true,
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
