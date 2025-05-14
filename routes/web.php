<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\DataTables\SyncContactsDataTable;
use App\Http\Controllers\clientController;
use App\Http\Controllers\CRMLoginController;
use App\Http\Middleware\loginAuthMiddleware;
use App\Http\Controllers\AjaxRequestController;
use App\DataTables\clietsSyncedHistoryDataTable;



Route::get('crm/login',[CRMLoginController::class,'ViewcrmLogin'])->name('login');
//--login---route
Route::post('/crmlogin',[CRMLoginController::class,'login']);
//--login---auth--miggleware--route
Route::middleware(loginAuthMiddleware::class)->group(function(){
//logout--route----
Route::post('/logout',[CRMLoginController::class,'logout'])->name('logout');
Route::get('/', function () {
    return view('client.list');
});

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

//Testing Function
Route::get('test',function(){
    // $personFields=['names,emailAddresses,phoneNumbers,userDefined,organizations,biographies'];
    // $pageSize=1000;
    $time=1746519477;
    print_r(time());
    echo"<br>";
    $estimated=560;
    $els=time() -$time;
    $result=min(100,($els / $estimated) *100);
    $result=round($result,2);

    print_r($result ."%");
    //contact come here ny the functon
        // $googleContacts = (new GoogleService())->getContacts($this->googleToken, $pageSize, $personFields,$nextPageToken, $this->nextSynToken);

});

}); //login middleware end