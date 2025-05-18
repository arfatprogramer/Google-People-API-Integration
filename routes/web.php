<?php


use Illuminate\Support\Facades\Route;
use App\DataTables\SyncContactsDataTable;
use App\Http\Controllers\clientController;
use App\Http\Controllers\CRMLoginController;
use App\Http\Middleware\loginAuthMiddleware;
use App\Http\Controllers\AjaxRequestController;
use App\DataTables\clietsSyncedHistoryDataTable;
use App\Models\GoogleAuth;
use App\Services\GoogleService;
use Google\Service\PeopleService;

Route::get('crm/login',[CRMLoginController::class,'ViewcrmLogin'])->name('login');

Route::post('/crmlogin',[CRMLoginController::class,'login']);


//--login---auth--miggleware--route
Route::middleware(loginAuthMiddleware::class)->group(function(){
//logout--route----
Route::post('/logout',[CRMLoginController::class,'logout'])->name('logout');
// Route::get('/', function () {
//     return view('ajax.index');
// });

Route::post('/create',[clientController::class, 'create'])->name('client.create');
Route::get('/create',[clientController::class, 'createForm'])->name('client.createForm');
Route::get('/list',[clientController::class, 'show'])->name('client.list');
Route::get('/edit/{id}',[clientController::class, 'editContact'])->name('client.edit');
Route::put('/ContactUpdate',[clientController::class, 'UpdateFormContact'])->name('client.ContactUpdate');
//soft-delete---or-----google--delete---contact----------
Route::post('/contacts/soft-delete',[AjaxRequestController::class, 'softDeletOrGoogleContact'])->name('client.softDelete');



Route::get('google/redirect',[AjaxRequestController::class, 'redirectToGoogle'])->name('client.redirect');
Route::get('google/sync/callback',[AjaxRequestController::class, 'handleGoogleCallback'])->name('client.callback');


Route::get('/',[AjaxRequestController::class,'index'])->name('ajax.index');
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
Route::get('/crm/delete',[AjaxRequestController::class,'deleteDataFromCRm'])->name('ajax.deleteDataFromCRm');

}); //login middleware end

Route::get('/test',function(){
    try {
        // $syncToken=GoogleAuth::orderBy('id', 'desc')->get()->first();
        // $client=(new GoogleService())->getGoogleCient($syncToken);
        // $peopleService=new PeopleService($client);
        // $response = $peopleService->otherContacts->listOtherContacts([
        //     'readMask' => 'names,emailAddresses',
        //     'pageSize' => 1000,
        // ]);
        // $otherContact=$response->otherContacts??[];

        $personFields=['names,phoneNumbers,emailAddresses,userDefined,organizations,biographies,addresses,birthdays,urls,relations'];
            $pageSize=1000;
           $googleToken= GoogleAuth::orderBy('id', 'desc')->get()->first();

            $googleContacts = (new GoogleService())->getContacts($googleToken, $pageSize, $personFields);

            foreach($googleContacts->connections as $data){

                // dump($data->birthdays[0]->date??'');
                // dump($data->userDefined??'');
                $userDefined=collect($data->userDefined??[]);
                $arrayKeys=['pan'=>'pancard_c',
                            'pancard '=>'pancard_c',
                            'pan card'=>'pancard_c',
                            'pan card no'=>'pancard_c',
                            'pan card number'=>'pancard_c',
                            'pen'=>'pancard_c',
                            'aadhar'=>'adhaar_card_c',
                            'aadhaar card'=>'adhaar_card_c',
                            'adhaar card'=>'adhaar_card_c'
                        ];
                $userDefindArray=[];
                foreach($userDefined as $data){

                    $key=strtolower($data->key);
                   $newKey=$arrayKeys[$key]??null;
                    $value=$data->value;
                    if (!empty($newKey)) {
                        $userDefindArray[$newKey]=$value;
                    }
                }



                // dump(preg_replace('/[^0-9]/','',$data->phoneNumbers[0]->value??''));
                // dump($data->addresses[0]??'');

            }
            return;
    } catch (\Throwable $th) {
            throw $th;
     }

});
