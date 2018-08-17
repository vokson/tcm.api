<?php

use Illuminate\Http\Request;

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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::middleware(['cors'])->group(function () {

    Route::post('/auth/login', 'ApiAuthController@login');
    Route::post('/auth/check_token', 'ApiAuthController@isTokenValid');

    Route::post('/test_guest', 'ApiAuthController@test');

    Route::middleware(['auth.api.token', 'auth.api.roles'])->group(function () {

        Route::post('/test_engineer', 'ApiAuthController@test');
        Route::post('/test_pm', 'ApiAuthController@test');
        Route::post('/test_admin', 'ApiAuthController@test');

    });

});







