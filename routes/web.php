<?php

use App\Http\Controllers\AjaxRequestController;
use App\Http\Controllers\clientController;
use App\Http\Controllers\googleSyncController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('client.list');
});

Route::post('/create',[clientController::class, 'create'])->name('client.create');
Route::get('/create',[clientController::class, 'createForm'])->name('client.createForm');
Route::get('/list',[clientController::class, 'show'])->name('client.list');
Route::get('/edit',[clientController::class, 'show'])->name('client.edit');
Route::get('google/sync',[googleSyncController::class, 'index'])->name('client.sync');
Route::get('google/syncProcess',[googleSyncController::class, 'syncGoogleContacts'])->name('client.syncProcess');
Route::get('google/redirect',[googleSyncController::class, 'redirectToGoogle'])->name('client.redirect');
Route::get('google/sync/callback',[googleSyncController::class, 'handleGoogleCallback'])->name('client.callback');

Route::get('test',[googleSyncController::class, 'test']);





// Only for Testing Purpose
Route::get('sync',[AjaxRequestController::class,'index'])->name('ajax.index');
Route::get('refreshUrl',[AjaxRequestController::class,'refreshReq'])->name('ajax.request');
Route::get('pushToGoogle',[AjaxRequestController::class,'pushToGoogle'])->name('ajax.pushToGoogle');
Route::get('syncStatus',[AjaxRequestController::class,'syncStatus'])->name('ajax.syncStatus');

Route::get('test',[AjaxRequestController::class,'pushToGoogle']);

Route::get('/sync/progress', function () {
    return Cache::get('sync_progress', ['total' => 0, 'synced' => 0]);
});

