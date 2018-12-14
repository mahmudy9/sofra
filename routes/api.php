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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/client/register' , 'ClientAuthController@register');
Route::post('/client/login', 'ClientAuthController@login');
Route::post('/client/logout', 'ClientAuthController@logout');
Route::post('/client/refresh', 'ClientAuthController@refresh');
Route::post('/client/me', 'ClientAuthController@me');

Route::post('/rest/register' , 'RestAuthController@register');
Route::post('/rest/login', 'RestAuthController@login');
Route::post('/rest/logout', 'RestAuthController@logout');
Route::post('/rest/refresh', 'RestAuthController@refresh');
Route::post('/rest/me', 'RestAuthController@me');
