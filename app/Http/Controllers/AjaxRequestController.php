<?php

namespace App\Http\Controllers;

use App\Jobs\importFormGoogleJob;
use App\Jobs\pushToGoogleJob;
use App\Models\client;
use App\Models\clientContatSyncHistory;
use App\Models\GoogleAuth;
use App\Services\GoogleService;
use Exception;
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
            // $crmTotalClient = client::count();
            $crmTotalClient=Client::count();
            // Client::chunk(10, function ($clients) use (&$crmTotalClient) {
            //     $crmTotalClient += $clients->count();

            // });
            $google=$contact->totalItems ?? 0;
            $pending= abs($crmTotalClient - $google);
            $lastSync=$this->clientContactSyncHistoryTable::orderBy('created_at','desc')->first();

            $data=['crm'=>$crmTotalClient,
                    'google'=>$google,
                    'pending'=>$pending,
                    'lastSync'=>$lastSync,
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

            $this->pushToGoogle();
            $this->importFromGoogle();
            $data=[];

            return response()->json([
                    'status'=>true,
                    'message'=>"Sync Started ",
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

    // Push To Google Function Ready
    public function pushToGoogle(){
        try {
            $this->isProcessing=true;
            $delay=1;
            $googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();
            $createIdList = Client::whereNull('resourceName')->pluck('id')->toArray();
            $updateIdList = Client::whereColumn('updated_at', '>', 'updateFlag')->pluck('id')->toArray();

            $chunks1 = array_chunk($createIdList, 80);
            $chunks2=array_chunk($updateIdList,80);

            //creating job for Create Contact on Google
            foreach($chunks1 as $chunk){
                pushToGoogleJob::dispatch($googleToken, $chunk, [])
                        ->delay(now()->addMinutes(1));
            }
            // creating job for Update contacts to google
            foreach($chunks2 as $chunk){
                pushToGoogleJob::dispatch($googleToken, [], $chunk)
                ->delay(now()->addMinutes(1));
            }


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
        importFormGoogleJob::dispatch($googleToken);
        $data=[];

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
            $this->createCountToCrm;
            $this->createCountToGoogle;
            $this->updateCountToCrm;
            $this->updateCountToGoogle;
            $isProcessing=$this->isProcessing;

        $data=[
            'isProcessing'=>$isProcessing,
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



}
