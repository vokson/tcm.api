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
    Route::post('/auth/login/token', 'ApiAuthController@loginByToken');
    Route::post('/auth/check_token', 'ApiAuthController@isTokenValid');

    Route::post('/test_guest', 'ApiAuthController@test');


    Route::middleware(['auth.api.token', 'auth.api.roles'])->group(function () {

        Route::post('/auth/change_password', 'UserController@changePassword');

        Route::post('/settings/get', 'SettingsController@get');
        Route::post('/settings/set', 'SettingsController@set');

        // LOG
        Route::post('/logs/get', 'LogController@get');
        Route::post('/logs/get/last/articles', 'LogController@getLatestArticles');

        Route::middleware(['auth.log.edit', 'reg_exp.log.edit'])->group(function () {
            Route::post('/logs/set', 'LogController@set');
            Route::post('/logs/delete', 'LogController@delete');
        });

        // LOG FILE
        Route::post('/logs/file/get', 'LogFileController@get');
        Route::post('/logs/file/download', 'LogFileController@download');
        Route::post('/logs/file/download/all', 'LogFileController@downloadAll');
        Route::post('/logs/clean/files/without/articles', 'LogFileController@clean');

        Route::middleware(['auth.log.file.edit', 'reg_exp.log.file.edit'])->group(function () {
            Route::post('/logs/file/upload', 'LogFileController@upload');
            Route::post('/logs/file/delete', 'LogFileController@delete');
        });

        // LOG NEW MESSAGE
        Route::middleware(['auth.log.new.message'])->group(function () {
            Route::post('/logs/new/message/switch', 'LogNewMessageController@set');
        });

        Route::post('/logs/new/message/count', 'LogNewMessageController@count');

        // STATUS
        Route::post('/statuses/get', 'StatusController@get');
        Route::post('/statuses/set', 'StatusController@set');
        Route::post('/statuses/delete', 'StatusController@delete');
        Route::post('/statuses/add', 'StatusController@add');

        // TITLE
        Route::post('/titles/get', 'TitleController@get');
        Route::post('/titles/set', 'TitleController@set');
        Route::post('/titles/delete', 'TitleController@delete');
        Route::post('/titles/history/get', 'TitleHistoryController@get');

        // USER
        Route::post('/users/get', 'UserController@get');
        Route::post('/users/set', 'UserController@set');
        Route::post('/users/set/default/password', 'UserController@setDefaultPassword');
        Route::post('/users/delete', 'UserController@delete');

        // DATABASE
        Route::post('/service/database/backup', 'ServiceController@getDatabaseBackup');
        Route::post('/service/database/update/attachments', 'ServiceController@updateAttachmentStatuses');

        // STATISTIC
        Route::post('/charts/logs/created/get', 'StatisticController@getItemsForLogChart');
        Route::post('/charts/titles/created/get', 'StatisticController@getItemsForTitleChart');
        Route::post('charts/storage/get', 'StatisticController@getItemsForStorageChart');

        // CHECK FILES
        Route::post('/checker/file/upload', 'CheckedFileController@upload');
        Route::post('/checker/file/download', 'CheckedFileController@download');

        // CHECK
        Route::post('/checker/get', 'CheckController@get');
        Route::middleware(['auth.checker.file.delete'])->group(function () {
            Route::post('/checker/delete', 'CheckController@delete');
        });

    });

});







