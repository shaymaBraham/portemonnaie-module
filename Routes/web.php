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

Route::prefix('portemonnaie')->group(function() {
    Route::get('/', 'PorteMonnaieController@index');

    Route::post('/buy_product','PorteMonnaieController@buy_product')->name('buy_product');
});
