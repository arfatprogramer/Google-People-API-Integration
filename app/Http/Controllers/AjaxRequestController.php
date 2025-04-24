<?php

namespace App\Http\Controllers;

use App\Jobs\importFormGoogleJob;
use App\Jobs\pushToGoogleJob;
use App\Models\client;
use App\Models\GoogleAuth;
use App\Services\GoogleService;
use Exception;
use Laravel\Socialite\Facades\Socialite;

class AjaxRequestController extends Controller
{
    protected $isProcessing=false;
    protected $updateCountToGoogle=0;
    protected $createCountToGoogle=0;
    protected $updateCountToCrm=0;
    protected $createCountToCrm=0;
    protected $googleToken;
    protected $clientTable;

    function __construct()
    {
        $this->googleToken=GoogleAuth::orderBy('id', 'desc')->get()->first();
        $this->clientTable=new client();
    }

    public function index(){
        try {
            //If table is empty then sign in user
            if (!$this->googleToken || $this->googleToken == null) {
                // $this->redirectToGoogle();
                return redirect()->route('client.redirect');
            }
            return view('client.enjayDesign');

        } catch (\Throwable $th) {
            dd($th);
        }

    }

    public function refreshReq(){
        try {

            $contact=(new GoogleService())->getContacts($this->googleToken, 1, ['names']);
            $crmTotalClient=$this->clientTable->count();
            $google=$contact->totalItems;
            $pending= abs($crmTotalClient - $google);

            $data=['crm'=>$crmTotalClient,
                    'google'=>$google,
                    'pending'=>$pending,
                    'error'=>0,
                    'lastSync'=>"April 7, 2024 at 1:20 AM"
            ];

            return response()->json([
                    'status'=>true,
                    'message'=>"Refreshed",
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

            $chunks1 = array_chunk($createIdList, 40);
            $chunks2=array_chunk($updateIdList,40);

            // Create equal pairs from both chunks
            $maxChunks = max(count($createIdList), count($updateIdList));

            for ($i = 0; $i < $maxChunks; $i++) {
                $createChunk = $chunks1[$i] ?? [];
                $updateChunk = $chunks2[$i] ?? [];

                if ($i>=1) {
                    pushToGoogleJob::dispatch($googleToken, $createChunk, $updateChunk)
                    ->delay(now()->addMinutes($delay));
                    $delay++; // Avoid rate limit
                }else{
                    pushToGoogleJob::dispatch($googleToken, $createChunk, $updateChunk);
                }
            }

        $data=[
            'isProcessing'=>$this->isProcessing,
            'UpdatingToGoogle'=>count($updateIdList),
            'CreatingToGoogle'=>count($createIdList),
            ];


        return response()->json([
                'status'=>true,
                'message'=>"Push To Google Stared",
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
        return Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/contacts',
                'email',
                'profile'
            ])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    //auth 2.0 authonticatin redirect handel function
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();

        //  save token and user info however you need
        $user = new GoogleAuth;
        $user->google_id = $googleUser->id;
        $user->name = $googleUser->name;
        $user->email = $googleUser->email;
        $user->googleAccessToken = $googleUser->token;
        $user->accessTokenExpiresIn = $googleUser->expiresIn;
        $user->googleRefreshToken = $googleUser->refreshToken;
        $user->refreshTokenExpiresIn = $googleUser->expiresIn;
        $user->save();

        return redirect()->route('ajax.index')->with('success', 'Google account connected successfully.');
    }

}
