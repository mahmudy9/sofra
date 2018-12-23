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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/client/password/reset/{token}' , 'ClientResetPasswordController@showResetForm');
Route::post('/client/password/reset' , 'ClientResetPasswordController@reset');

Route::get('/rest/password/reset/{token}' , 'RestResetPasswordController@showResetForm');
Route::post('/rest/password/reset' , 'RestResetPasswordController@reset');

Route::get('/admin' , 'AdminController@index');