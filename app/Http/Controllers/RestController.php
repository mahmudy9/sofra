<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Restaurant;
use App\Product;
use Validator;
use App\Http\Resources\ProductResource;

class RestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api_rest'])->except([]);
    }


    public function create_product(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'name' => 'required|min:3|max:190|string',
            'description' => 'required|string|min:10|max:190',
            'cooking_duration' => 'required|string|min:2|max:30',
            'pic' => 'required|image|max:1900'
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation error' , $validator->errors());
        }
        if($request->hasFile('pic'))
        {
            $path = $request->file('pic')->store('public');
            $filename = pathinfo($path , PATHINFO_BASENAME);
        }
        $product = new Product;
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->cooking_duration = $request->input('cooking_duration');
        $product->pic = $filename;
        $restaurant = Restaurant::find(auth('api_rest')->user()->id);
        $restaurant->products()->save($product);
        return apiRes(200 , 'success, product created', $product);
    }

}
