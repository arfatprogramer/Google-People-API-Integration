<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\DataTables\SyncContactsDataTable;
use App\Http\Controllers\clientController;
use App\Http\Controllers\CRMLoginController;
use App\Http\Middleware\loginAuthMiddleware;
use App\Http\Controllers\AjaxRequestController;
use App\DataTables\clietsSyncedHistoryDataTable;
use App\Models\client;
use App\Models\GoogleAuth;
use App\Services\CrmApiServices;
use App\Services\GoogleService;
use Symfony\Component\VarDumper\VarDumper;

Route::get('crm/login',[CRMLoginController::class,'ViewcrmLogin'])->name('login');

Route::post('/crmlogin',[CRMLoginController::class,'login']);

//--login---auth--miggleware--route
Route::middleware(loginAuthMiddleware::class)->group(function(){

Route::get('/', function () {
    return view('client.list');
});
//--login---route
Route::post('/create',[clientController::class, 'create'])->name('client.create');
Route::get('/create',[clientController::class, 'createForm'])->name('client.createForm');
Route::get('/list',[clientController::class, 'show'])->name('client.list');
Route::get('/edit/{id}',[clientController::class, 'editContact'])->name('client.edit');
Route::put('/ContactUpdate',[clientController::class, 'UpdateFormContact'])->name('client.ContactUpdate');
//soft-delete---or-----google--delete---contact----------
Route::post('/contacts/soft-delete',[AjaxRequestController::class, 'softDeletOrGoogleContact'])->name('client.softDelete');



Route::get('google/redirect',[AjaxRequestController::class, 'redirectToGoogle'])->name('client.redirect');
Route::get('google/sync/callback',[AjaxRequestController::class, 'handleGoogleCallback'])->name('client.callback');


Route::get('sync',[AjaxRequestController::class,'index'])->name('ajax.index');
Route::get('refreshUrl',[AjaxRequestController::class,'refreshReq'])->name('ajax.request');
Route::get('synNow',[AjaxRequestController::class,'synNowBoth'])->name('ajax.synNow');
Route::get('pushToGoogle',[AjaxRequestController::class,'pushToGoogle'])->name('ajax.pushToGoogle');
Route::get('importFromGoogle',[AjaxRequestController::class,'importFromGoogle'])->name('ajax.importFromGoogle');
Route::post('singleSyncById',[AjaxRequestController::class,'singleSyncById'])->name('ajax.singleSyncById');
Route::get('syncStatus',[AjaxRequestController::class,'syncStatus'])->name('ajax.syncStatus');
Route::delete('cancelPendingGoogleSync',[AjaxRequestController::class,'cancelPendingGoogleSync'])->name('ajax.cancelPendingGoogleSync');


// Data Table Routes
Route::get('sync-history-data', [clietsSyncedHistoryDataTable::class, 'ajax'])->name('sync.history.data');
Route::get('sync-contacts-data', [SyncContactsDataTable::class, 'ajax'])->name('sync.contacts.data');

Route::get('getClinetSyncHistory',[AjaxRequestController::class,'getClinetSyncHistory'])->name('ajax.getClinetSyncHistory');

}); //login middleware end

Route::get('/test',function(){
    // $crmContacts = client::whereNotNull('resourceName')->pluck('resourceName')->toArray();
    // echo"<pre>";
    // print_r($crmContacts);
    // $data=(!in_array("people/c8678220737383419328", $crmContacts));
    // if ($data) {
    //     echo"Create";
    // }else
    // echo"Update";

    // $googleToken=GoogleAuth::orderBy('id', 'desc')->get()->first();
    //  $googleContacts = (new GoogleService())->getContacts($googleToken, 100,['names,phoneNumbers,emailAddresses,userDefined,organizations,biographies,addresses,birthdays']);

    // $peopleArray=$googleContacts->connections;
    // $contacts = [];
    // $timeStamp=123;
    // foreach ($peopleArray as $person) {
    //  $resourceName = $person->resourceName;
    //  $etag = $person->etag;

    //     $name = $person->names[0] ?? null;
    //     $emails = collect($person->emailAddresses ?? []);
    //     $phones = collect($person->phoneNumbers ?? []);
    //     $biographies = $person->biographies[0]->value ?? '';
    //     $organizations = $person->organizations[0] ?? null;
    //     $addresses = collect($person->addresses ?? []);

    //     $contacts[$resourceName] = [
    //         'rest_data' => [
    //             'module_name' => 'Contact',
    //             'name_value_list' => [
    //                 'first_name' => $name->givenName ?? '',
    //                 'last_name' => $name->familyName ?? '',
    //                 'designation' => $organizations->title ?? '',
    //                 'birth_date' => '',
    //                 'anniversary' => '',
    //                 'customer_type' => '',
    //                 'hiddenPhone' => $phones->map(function ($phone) {
    //                     return [
    //                         'phone_number' => $phone->value ?? '',
    //                         'verified_at' => '',
    //                         'unsubscribed' => false,
    //                         'invalid' => false,
    //                         'primary' => $phone->metadata->primary ?? false,
    //                     ];
    //                 })->values()->all(),
    //                 'hiddenEmail' => $emails->map(function ($email) {
    //                     return [
    //                         'email_address' => $email->value ?? '',
    //                         'primary' => $email->metadata->primary ?? false,
    //                         'status' => 'invalid',
    //                         'suppression' => $email->value ?? null,
    //                         'verified_at' => '',
    //                     ];
    //                 })->values()->all(),
    //                 'hiddenAddress' => $addresses->map(function ($address) {
    //                     return [
    //                         'street' => $address->streetAddress ?? '',
    //                         'city' => $address->city ?? '',
    //                         'region' => $address->region ?? '',
    //                         'postal_code' => $address->postalCode ?? '',
    //                         'country' => $address->country ?? '',
    //                         'type' => $address->type ?? '',
    //                         'primary' => $address->metadata->primary ?? false,
    //                     ];
    //                 })->values()->all(),
    //                 'comment' => $biographies ? [['description' => $biographies]] : [],
    //                 'etag_c'=>$etag,
    //                 'resource_name_c'=>$resourceName,
    //                 'sync_status_c'=>'Synced',
    //                 'last_aync_c'=>$timeStamp,
    //                 'duration_c' => '12:00 AM',
    //                 'hierarchy' => '',
    //                 'department' => '',
    //                 'lead_source' => '',
    //                 'teamsSet' => '1'
    //             ]
    //         ]
    //     ];
    // }

    // foreach($contacts as $resource => $payload){
    //     // print_r($payload['rest_data']['name_value_list']);
    // }
//    return response()->json($googleContacts);


    // $token=session('crm_token');
    // $existingData=(new CrmApiServices($token))->getExistingDataFromCrm(['people/c5989124196303340918','people/c4689612350472725237','people/c7543721668131697888']);
    // // $data=(new CrmApiServices(session('crm_token')))->updateSyncStatus("96510fb0-e87e-4500-a845-5c02d3669c82",'4','eee','Pending');

    // return $existingData;

  $pairmeter = 1;
    // do {
        $res = (new CrmApiServices(session('crm_token')))->getContacts();
        $pendingToCreate = $res['meta']['total']??0;

        dump($res??"no Data");

        $next = isset($res->links->next);
        echo $pendingToCreate;
        $pairmeter++;

    // } while ($next);


});
