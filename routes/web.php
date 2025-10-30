<?php

use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::controller(UploadController::class)->prefix('upload')->name('upload.')->group(function() {
    Route::get('/', 'index')->name('index');
    Route::post('/file-upload', 'fileUpload')->name('file-upload'); 
    Route::get('/poll', 'poll')->name('poll'); 
});