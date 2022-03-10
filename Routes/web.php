<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['prefix' => '', 'as' => 'portemonnaie.',  'middleware' => ['auth']], function () {

    Route::get('/portemonnaie', 'PorteMonnaieController@index')->name('index');

   
});
