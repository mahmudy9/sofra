<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Restaurant as RestResource;
use App\Restaurant;
use App\Http\Resources\RestProducts;
use App\Http\Resources\RestReviews;
use App\Http\Resources\ProductResource;
use App\Product;
use Validator;
use App\Order;
use App\Commission;
use App\Offer;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api_client'])
        ->except(['restaurants' , 'restaurant_products' , 'restaurant_reviews' , 'restaurant_details' , 'product']);
    }

    
    public function restaurants()
    {
        $rests = Restaurant::paginate(10);
        return RestResource::collection($rests);
    }

    public function restaurant_products($restaurant_id)
    {
        $restaurant = Restaurant::findOrFail($restaurant_id);
        return new RestProducts($restaurant);
    }

    public function restaurant_reviews($restaurant_id)
    {
       $restaurant = Restaurant::findOrFail($restaurant_id);
       return new RestReviews($restaurant);
    }

    public function restaurant_details($restaurant_id)
    {
        $restaurant = Restaurant::findOrFail($restaurant_id);
        return new RestResource($restaurant);
    }

    public function product($product_id)
    {
        $product = Product::findOrFail($product_id);
        return new ProductResource($product);
    }

    public function create_order(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'products' => 'required|array',
            'restaurant' => 'required|integer',
            'notes' => 'nullable|string|max:190',
            'offer' => 'nullable|integer'
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'Validation error' , $validator->errors());
        }
        $order = new Order;
        $order->notes = $request->input('notes');
        if($request->has('offer'))
        {
            $order->offer_id = $request->input('offer');
            $offer = Offer::findOrFail($request->input('offer'));
            $discount = $offer->discount;
        }
        if($request->has('offer'))
        {

            $order->discount = $discount;
        }
        $order->restaurant_id = $request->input('restaurant');
        $rest = Restaurant::findOrFail($request->input('restaurant'));
        $com = Commission::first();
        $products = $request->input('products');
        $order->order_status = 'pending';
        $order->client_decision = 'pending';
        $order->restaurant_decision = "pending";
        $price = 0;

        foreach($products as $product)
        {
            $price = $product['price'] * $product['quantity'] + $price;
        }
        $commission = $price*($com->commission/100);
        $total = $price + $rest->delivery_fee;
        $order->commission = $commission;
        $order->price = $price;
        $order->delivery_fee = $rest->delivery_fee;
        if($request->has('offer'))
        {
            $total = $total * ($discount/100);
        }
        $order->total = $total;
        auth('api_client')->user()->orders()->save($order);
        $order->products()->attach($products);
        
        return apiRes(200 , 'order created' , $order);
    }
}
