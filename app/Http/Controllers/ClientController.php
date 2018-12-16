<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Restaurant as RestResource;
use App\Http\Resources\testResource;

use App\Restaurant;
use App\Http\Resources\RestProducts;
use App\Http\Resources\RestReviews;
use App\Http\Resources\ProductResource;
use App\Product;

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

    //public function restaurant_reviews($restaurant_id)
    //{
     //   $restaurant = Restaurant::findOrFail($restaurant_id);
    //    return new RestReviews($restaurant);
    //}

    public function restaurant_reviews($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        return new testResource($restaurant);
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
        
    }
}
