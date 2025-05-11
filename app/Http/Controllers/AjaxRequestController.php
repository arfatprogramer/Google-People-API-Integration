<?php

namespace App\Http\Controllers;

use App\DataTables\clietsSyncedHistoryDataTable;
use App\DataTables\SyncContactsDataTable;
use App\Jobs\importFormGoogleJob;
use App\Jobs\pushToGoogleJob;
use App\Models\client;
use App\Models\clientContatSyncHistory;
use App\Models\GoogleAuth;
use App\Services\GoogleService;
use Carbon\Carbon;
use Exception;
use Google\Http\Batch;
use Google\Service\PeopleService;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

use function Laravel\Prompts\progress;
use function PHPUnit\Framework\isEmpty;

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

    public function index(SyncContactsDataTable $contactsTable, clietsSyncedHistoryDataTable $historyTable){
        try {
            //If table is empty then sign in user
            if (!$this->googleToken || $this->googleToken == null) {
                // $this->redirectToGoogle();
                return redirect()->route('client.redirect');
            }
            return view('client.enjayDesign', [
                'contactsTable' => $contactsTable->html(),
                'historyTable' => $historyTable->html(),

            ]);

        } catch (\Throwable $th) {
            dd($th);
        }

    }

    public function refreshReq(){
        try {
            Log::info('Starting  Refresh Method in Ajax Contrller: ' .(memory_get_usage(true)/1024/1024)." MB");

            $lastSync=$this->clientContactSyncHistoryTable::orderBy('created_at','desc')->first();
            $lastSyncChangesDeteted=($lastSync->createdAtGoogle?? 0) + ($lastSync->updatedAtGoogle?? 0) + ($lastSync->created?? 0) + ($lastSync->updated?? 0) + ($lastSync->deleted ?? 0) ;

            $nextSynToken=clientContatSyncHistory::orderBy('id', 'desc')->get('synToken')->first();
            $TotalcontactInGoogle=(new GoogleService())->getContacts($this->googleToken, 10, ['names']);
            $pendingChangesOnGoogle=(new GoogleService())->getContacts($this->googleToken, 10, ['names'],null,$nextSynToken->synToken??null);
            // dd($TotalcontactInGoogle);

            $crmTotalClient=Client::count();
            $pendingChangesOnCRM= Client::where('syncStatus','Pending')->orWhere('syncStatus','Not Synced')->count(); // it have to calculate
            $crmTotalClientSynced=Client::where('syncStatus','Synced')->count();


            $TotalcontactInGoogle=$TotalcontactInGoogle->totalPeople ?? 0;
            $pendingChangesOnGoogle=$pendingChangesOnGoogle->totalPeople??0;
            $remanigToImportFromGoogle= ($TotalcontactInGoogle - $crmTotalClient) > 0 ? ($TotalcontactInGoogle - $crmTotalClient) : (0);



            $data=[
                'crm'=>$crmTotalClient,
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
           $extimetedTime=0;
           $totalPending=0;

            $googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();

              // calculating pending on Google and Add in Pending
              $nextSynToken=clientContatSyncHistory::orderBy('id', 'desc')->get('synToken')->first();
              $pendingChangesOnGoogle=(new GoogleService())->getContacts($this->googleToken, 10, ['names'],null,$nextSynToken->synToken??null);
              $totalPending +=$pendingChangesOnGoogle->totalPeople??0;

           // Create an empty history row
            $syncHistory = new clientContatSyncHistory();
            $syncHistory->save();


            $createIds = Client::where('syncStatus', 'Not Synced')->pluck('id')->toArray();
            $updateIds = Client::where('syncStatus', 'Pending')->pluck('id')->toArray();
            $totalPending= (count($createIds) + count($updateIds));
            $createChunks = array_chunk($createIds, 200);
            $updateChunks = array_chunk($updateIds, 200);

            $batches=Bus::batch([])->name("Synk Both")->dispatch();


            foreach ($createChunks as $chunk) {
                $batches->add(new pushToGoogleJob($googleToken, $chunk, [], $syncHistory->id));
                $extimetedTime += 15; // 15 sec take to process
                $batchCount++;
            }

            foreach ($updateChunks as $chunk) {
                $batches->add(new pushToGoogleJob($googleToken, [], $chunk, $syncHistory->id));
                $extimetedTime += 15; // 15 sec take to process
                $batchCount++;
            }

            // import form Google Job
            $batches->add(new importFormGoogleJob($googleToken, $syncHistory->id));
            $batchCount++;
            $extimetedTime += 30; // 30 sec take to process

            // Update the batches count
            $syncHistory->batches = $batchCount;
            $syncHistory->extimetedTime = $extimetedTime;
            $syncHistory->pending = $totalPending;
            $syncHistory->save();

            session(['batch_id'=>$batches->id]);

            return response()->json([
                    'status'=>true,
                    'message'=>"Sync Started ",
                    'error'=>false,
                    'data'=>[],
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

    // Push To Google Function Ready
    public function pushToGoogle(){
        try {
            $extimetedTime=0;
            $googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();

            $createIds = Client::where('syncStatus', 'Not Synced')->pluck('id')->toArray();
            $updateIds = Client::where('syncStatus', 'Pending')->pluck('id')->toArray();
            $totalPending= (count($createIds) + count($updateIds));
            if ($totalPending==0) {
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
            $syncHistory->pending=$totalPending;
            $syncHistory->save();

            $createChunks = array_chunk($createIds, 10);
            $updateChunks = array_chunk($updateIds, 200);

            $batchCount = 0;
            $batches=Bus::batch([])->name('Push To Google')->dispatch();

            foreach ($createChunks as $chunk) {
                $batchCount++;
                $batches->add(new pushToGoogleJob($googleToken, $chunk, [], $syncHistory->id));
                $extimetedTime += 15; // 15 sec take to process
            }

            foreach ($updateChunks as $chunk) {
                $batchCount++;
                $batches->add(new pushToGoogleJob($googleToken, [], $chunk, $syncHistory->id));
                $extimetedTime += 15; // 15 sec take to process
            }

            // Update the batches count
            $syncHistory->batches = $batchCount;
            $syncHistory->extimetedTime = $extimetedTime;
            $syncHistory->save();

            session(['batch_id'=>$batches->id]);

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
        Log::info('Import Form Google Function '  );

        $googleToken = GoogleAuth::orderBy('id', 'desc')->get()->first();
        $nextSynToken=clientContatSyncHistory::orderBy('id', 'desc')->get('synToken')->first();
        $pendingChangesOnGoogle=(new GoogleService())->getContacts($this->googleToken, 10, ['names'],null,$nextSynToken->synToken??null);

        $newClientSyncHistoyRow=new clientContatSyncHistory();
        $newClientSyncHistoyRow->batches += 1;
        $newClientSyncHistoyRow->pending = $pendingChangesOnGoogle->totalPeople??0;
        $newClientSyncHistoyRow->extimetedTime = 30;
        $newClientSyncHistoyRow->save();

        $batches=Bus::batch([
            new importFormGoogleJob($googleToken, $newClientSyncHistoyRow->id),
        ])->name('Import From Google')->dispatch();

        session(['batch_id'=>$batches->id]);

        return response()->json([
                'status'=>true,
                'message'=>"Import From Google Contact",
                'error'=>false,
                'data'=>[],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status'=>false,
                'error'=>true,
                'message'=>$e,
                'data'=>[],
            ]);
        }
    }

    public function singleSyncById(Request $request){
        try {
            $client = (new GoogleService())->getGoogleCient($this->googleToken);
            $peopleService = new PeopleService($client);

            $cliet_id=$request->Cliet_id;
            $deletedReSync=$request->deletedReSync;
            $contact=client::where('id',$cliet_id)->get()->first();

            $person=(new GoogleService())->getPerson($contact);

            if ($contact->syncStatus=="Pending" && $contact->resourceName) {

                $person->setEtag($contact->etag);
                $updated = $peopleService->people->updateContact($contact->resourceName, $person, [
                    'updatePersonFields' => 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies',
                ]);
            }elseif($contact->syncStatus=="Not Synced" || $deletedReSync ){
                $updated = $peopleService->people->createContact($person, [
                    'personFields' => 'names,emailAddresses,phoneNumbers,userDefined,organizations,biographies',
                ]);
            }else {
                return response()->json([
                    'status'=>false,
                    'error'=>true,
                    'message'=>"This Data is Deleted From Google But Remaning in CRM",
                    'data'=>[],
                ]);
            }
            $contact->etag = $updated->etag;
            $contact->resourceName = $updated->resourceName;
            $contact->lastSync = Carbon::now();
            $contact->syncStatus = 'Synced';
            $contact->save();

            return response()->json([
                'status'=>true,
                'message'=>"Completed",
                'error'=>false,
                'data'=>$updated,
            ]);

        } catch (Exception $e) {

            $message=$e->getMessage();
            $ErrorCode=$e->getCode();
            if ($ErrorCode===404) { //404  Requested entity was not found
                $message='This Contact Is Deleted From Google';
                $contact->syncStatus = 'Deleted';
                $contact->save();
            }
            return response()->json([
                'status'=>false,
                'error'=>true,
                'message'=>$message,
                'data'=>[],
            ]);
        }
    }

    // This for Dispaly Process Bar
     public function syncStatus(){
        try {
            $progress=100;
            $processing=false;
            $batchId = session('batch_id');
            if (!$batchId==null) {
                $batch = Bus::findBatch($batchId);
                $progress=$batch->progress();

                if ($batch->cancelled()) {  //cancelled
                     $processing=false;

                }elseif($batch->finished()){  // Completed
                     $processing=false;

                }else{   //Processing
                     $processing=true;

                }
            }

            $message="Synced status function";
            $lastSync=clientContatSyncHistory::orderBy('created_at','desc')->first();
            $extimetedTime = ($lastSync->extimetedTime ?? 0);
            $extimetedTime=round(($extimetedTime/60)). ":".($extimetedTime%60)." Min";

        $data=[
            'processing'=>$processing,
            'progress'=>$progress,
            'lastSync'=>$lastSync,
            'extimetedTime'=>$extimetedTime,
        ];

        return response()->json([
                'status'=>true,
                'message'=>$message,
                'error'=>false,
                'data'=>$data,
            ]);
        } catch (Exception $e) {

            return response()->json([
                'status'=>false,
                'error'=>true,
                'massage'=>$e->getMessage(),
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
        $client->addScope([
            'https://www.googleapis.com/auth/userinfo.profile',
             'https://www.googleapis.com/auth/contacts'
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


    ///------softDelete---crm--or----googlecontact---delete---------------
    public function softDeletOrGoogleContact(Request $request){

        $contactSoftDelete = filter_var($request->delete_contact, FILTER_VALIDATE_BOOLEAN);

            if($contactSoftDelete === true){

                $client = (new GoogleService())->getGoogleCient($this->googleToken);
                    // Assume you already have the authenticated Google Client
                $peopleService = new PeopleService($client);

            $contact = client::find($request->client_id);  // or however you fetch


            try {

                if(!empty($contact->resourceName)){
                $delete= $peopleService->people->deleteContact( $contact->resourceName);

                if($delete){
                    $contact->delete();
                }
                return response()->json(['success' => true,'message' => ' Google and CRM Contact deleted successfully.']);

                }else{

                $contact->delete();

                return response()->json(['success' =>true,'message' => 'CRM Contact is delete successfully','resourceName']);

                }
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

                // return response()->json(['message' => 'Google Contact deleted successfully.','resource'=>$resourceName]);


            }else{

            $contact = client::find($request->client_id);
            if ($contact) {
                $contact->delete(); // Soft delete karega (deleted_at fill karega)
                return response()->json(['success' => true,
                'message' => ' CRM Contact soft deleted successfully.',
                'data'=>$contactSoftDelete
            ]);
            } else {
                return response()->json(['success' => false,
                'message' => 'Contact not found.'
            ]);
            }
        }
    }


    public function cancelPendingGoogleSync(){

        try {

            $lastRow = clientContatSyncHistory::latest('id')->first();
            $lastRow->delete();

            $batchId = session('batch_id');
            if (!$batchId==null) {
                $batch = Bus::findBatch($batchId);
                $batch->cancel();
            }

            return response()->json([
                'status'=>true,
                'error'=>false,
                'message'=>"Process canceld",
                'data'=>$lastRow,
            ]);

        } catch (Exception $e) {
             return response()->json([
                'status'=>false,
                'error'=>true,
                'message'=>$e,
                'data'=>[],
            ]);
        }

    }

}
