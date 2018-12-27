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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/client/register' , 'ClientAuthController@register');
Route::post('/client/login', 'ClientAuthController@login');
Route::post('/client/logout', 'ClientAuthController@logout');
Route::post('/client/refresh', 'ClientAuthController@refresh');
Route::post('/client/me', 'ClientAuthController@me');
Route::get('/client/restaurants' , 'ClientController@restaurants');
Route::post('/client/password/email' , 'ClientForgotPasswordController@sendResetLinkEmail');
Route::get('/client/restaurant/products/{restaurantid}', 'ClientController@restaurant_products');
Route::get('/client/restaurant/reviews/{restaurantid}' , 'ClientController@restaurant_reviews');
Route::get('/client/restaurant/details/{restaurantid}' , 'ClientController@restaurant_details');
Route::get('/client/product/{productid}' , 'ClientController@product');
Route::post('/client/order/create' , 'ClientController@create_order');
Route::get('/client/address' , 'ClientController@client_address');
Route::get('/client/order/pending' , 'ClientController@pending_orders');
Route::get('/client/order/restaccepted' , 'ClientController@restaurant_accepted_orders');
Route::get('/client/order/restrejected' , 'ClientController@restaurant_rejected_orders');
Route::get('/client/order/clientrejected' , 'ClientController@client_rejected_orders');
Route::get('/client/order/delivered' , 'ClientController@delivered_orders');
Route::post('/client/order/accept/{orderid}' , 'ClientController@accept_order');
Route::post('/client/order/reject/{orderid}' , 'ClientController@reject_order');
Route::get('/client/offers' , 'ClientController@offers');
Route::post('/client/complaint/create' , 'ClientController@create_complaint');
Route::post('/client/suggestion/create' , 'ClientController@create_suggestion');
Route::post('/client/contact/create' , 'ClientController@create_contact');
Route::post('/client/review/create' , 'ClientController@create_review');
Route::delete('/client/review/destroy/{reviewid}' , 'ClientController@destroy_review');
Route::post('/client/trynote' , 'ClientAuthController@sendNote');
Route::post('/client/tryfire' , 'ClientAuthController@notifyByFirebase');

Route::post('/rest/register' , 'RestAuthController@register');
Route::post('/rest/login', 'RestAuthController@login');
Route::post('/rest/logout', 'RestAuthController@logout');
Route::post('/rest/refresh', 'RestAuthController@refresh');
Route::post('/rest/me', 'RestAuthController@me');
Route::post('/rest/password/email' , 'RestForgotPasswordController@sendResetLinkEmail');
Route::post('/rest/product/create' , 'RestController@create_product');
Route::get('/rest/product/edit/{productid}' , 'RestController@edit_product');
Route::put('/rest/product/update' , 'RestController@update_product');
Route::post('/rest/product/destroy' , 'RestController@destroy_product');
Route::get('/rest/product' , 'RestController@restaurant_products');
Route::post('/rest/status' , 'RestController@change_status');
Route::get('/rest/order/new' , 'RestController@new_orders');
Route::get('rest/order/current' , 'RestController@current_orders');
Route::get('/rest/order/old' , 'RestController@old_orders');
Route::get('/rest/order/items/{orderid}' , 'RestController@order_items');
Route::post('/rest/order/accept' , 'RestController@accept_order');
Route::post('/rest/order/reject' , 'RestController@reject_order');
Route::post('/rest/order/confirm-delivered' , 'RestController@confirm_delivered');
Route::get('/rest/offer' , 'RestController@restaurant_offers');
Route::post('/rest/offer/create' , 'RestController@create_offer');
Route::put('/rest/offer/update' , 'RestController@update_offer');
Route::get('/rest/commissions' , 'RestController@restaurant_commissions');