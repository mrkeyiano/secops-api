<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlackList\UserAgentController;
use App\Http\Controllers\BlackList\EmailController;
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




    Route::prefix('blacklist')->group(function () {

        Route::post('useragent', [UserAgentController::class, 'verify'])->name('blacklist.useragent');
        Route::post('emailprovider', [EmailController::class, 'verify'])->name('blacklist.email');
    });

