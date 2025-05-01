<?php

use App\DataTables\clietsSyncedHistoryDataTable;
use App\DataTables\SyncContactsDataTable;
use App\Http\Controllers\AjaxRequestController;
use App\Http\Controllers\clientController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

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


// Data Table Routes
Route::get('sync-history-data', [clietsSyncedHistoryDataTable::class, 'ajax'])->name('sync.history.data');
Route::get('sync-contacts-data', [SyncContactsDataTable::class, 'ajax'])->name('sync.contacts.data');




Route::get('getClinetSyncHistory',[AjaxRequestController::class,'getClinetSyncHistory'])->name('ajax.getClinetSyncHistory');

