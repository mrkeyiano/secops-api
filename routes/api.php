<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlackList\UserAgentController;
use App\Http\Controllers\BlackList\EmailController;
use App\Http\Controllers\Verify\BvnController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/




Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});




Route::prefix('verify')->group(function () {
   Route::post('bvn', [BvnController::class, 'verify'])->name('verify.bvn');
});

Route::prefix('validate')->group(function () {

    Route::post('useragent', [UserAgentController::class, 'verify'])->name('blacklist.useragent');
    Route::post('email', [EmailController::class, 'verify'])->name('blacklist.email');


});
