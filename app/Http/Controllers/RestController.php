<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Restaurant;
use App\Product;
use Validator;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Order as OrderResource;
use App\Http\Resources\Offer as OfferResource;
use App\Offer;
use App\Http\Resources\Orderitem;
use App\Order;
use App\Appfee;

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
            'pic' => 'required|image|max:1900',
            'price' => 'required|numeric|between:0,99999999.99',
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
        $product->price = $request->input('price');
        auth('api_rest')->user()->products()->save($product);
        return apiRes(200 , 'success, product created', $product);
    }

    public function edit_product($productid)
    {
        $product = Product::findOrFail($productid);
        $restproducts = auth('api_rest')->user()->products()->pluck('id')->toArray();
        if(!in_array($productid , $restproducts))
        {
            return apiRes(400 , 'you can not edit another restaurant product');
        }
        return (new ProductResource($product))->additional(['msg' => 'product details' , 'status' => 200]);
    }

    public function update_product(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'productid' => 'required|integer|min:1',
            'name' => 'nullable|string|max:190',
            'description' => 'nullable|string|max:190',
            'cooking_duration' => 'nullable|string|max:50',
            'pic' => 'nullable|image|max:1900',
            'price' => 'nullable|numeric|between:0,99999999.99',
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation error' , $validator->errors());
        }
        $product = Product::findOrFail($request->input('productid'));
        $restproducts = auth('api_rest')->user()->products()->pluck('id')->toArray();
        if(!in_array($request->input('productid') , $restproducts))
        {
            return apiRes(400 , 'you can not edit another restaurant product');
        }

        if($request->hasFile('pic'))
        {
            $path = $request->file('pic')->store('public');
            $filename = pathinfo($path , PATHINFO_BASENAME);
            Storage::delete($product['pic']);
            $product->pic = $filename;
        }
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->cooking_duration = $request->input('cooking_duration');
        $product->price = $request->input('price');
        $product->save();
        return apiRes(200 , 'success , product updated' , $product);
    }

    public function destroy_product(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'productid' => 'required|integer|min:1'
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'valdiation error , invalida product id' , $validator->errors());
        }
        $product = Product::findOrFail($request->input('productid'));
        $restproducts = auth('api_rest')->user()->products()->pluck('id')->toArray();
        if(!in_array($request->input('productid') , $restproducts))
        {
            return apiRes(400 , 'you can not edit another restaurant product');
        }
        if($product->orders()->exists())
        {
            return apiRes(400 , 'you can not delete product , it has orders');
        }
        $product->delete();
        return apiRes(200 , 'product deleted successfully');
    }

    public function restaurant_products()
    {
        $products = auth('api_rest')->user()->products()->where('activated' , 1)->paginate(10);
        return ProductResource::collection($products)->additional(['status'=> 200 , 'msg'=> 'restaurant products data']);
    }


    public function change_status()
    {
        $restaurant = auth('api_rest')->user();
        if($restaurant['status'] == 'closed')
        {
            $restaurant->status = 'open';
            $restaurant->save();
            return apiRes(200 , 'restaurant is open now');
        }elseif($restaurant['status'] == 'open'){
            $restaurant->status = 'closed';
            $restaurant->save();
            return apiRes(200 , 'restaurant is closed now');
        }
    }

    public function new_orders()
    {
        $orders = auth('api_rest')->user()->orders()->where('order_status' , 'pending')->paginate(10);
        return OrderResource::collection($orders)->additional(['status' => 200 , 'msg' => 'new orders data']);
    }

    public function current_orders()
    {
        $orders = auth('api_rest')->user()->orders()->where('restaurant_decision' , 'accepted')->where('client_decision' , '!=','rejected')->paginate(10);
        return OrderResource::collection($orders)->additional(['status' => 200 , 'msg' => 'current orders data']);
    }

    public function old_orders()
    {
        $orders = auth('api_rest')->user()->orders()->where('order_status' , '!=' , 'pending')->paginate(10);
        return OrderResource::collection($orders)->additional(['status' => 200 , 'msg' => 'old orders data']);
    }

    public function accept_order(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'orderid' => 'required|integer|min:1',
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation error , invalid  order id');
        }
        $order = Order::findOrFail($request->input('orderid'));
        $orders = auth('api_rest')->user()->orders()->pluck('id')->toArray();
        if(!in_array($request->input('orderid'), $orders))
        {
            return apiRes(400 , 'error , not your order to accept');
        }
        if($order['order_status'] == 'rejected' || $order['order_status'] == 'delivered')
        {
            return apiRes(400 , 'error , can not accept order, its already finished');
        }
        $order->restaurant_decision = 'accepted';
        $order->save();
        return apiRes(200 , 'order accepted');
    }

    public function reject_order(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'orderid' => 'required|integer|min:1',
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation error , invalid  order id');
        }
        $order = Order::findOrFail($request->input('orderid'));
        $orders = auth('api_rest')->user()->orders()->pluck('id')->toArray();
        if(!in_array($request->input('orderid'), $orders))
        {
            return apiRes(400 , 'error , not your order to accept');
        }
        if($order['order_status'] == 'rejected' || $order['order_status'] == 'delivered')
        {
            return apiRes(400 , 'error , can not reject order, its already finished');
        }
        $order->restaurant_decision = 'rejected';
        $order->order_status = 'rejected';
        $order->save();
        return apiRes(200 , 'you rejected order and now its finished order');
    }

    public function confirm_delivered(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'orderid' => 'required|integer|min:1',
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation error , invalid  order id');
        }
        $order = Order::findOrFail($request->input('orderid'));
        $orders = auth('api_rest')->user()->orders()->pluck('id')->toArray();
        if(!in_array($request->input('orderid'), $orders))
        {
            return apiRes(400 , 'error , not your order to change status');
        }
        if($order['order_status'] == 'delivered')
        {
            return apiRes(400 , 'error , can not change status of order, its already finished');
        }
        if($order['restaurant_decision'] == 'pending')
        {
            return apiRes(400, 'you can not change status to delivered , you have to accept order first');
        }
        $order->order_status = 'delivered';
        $order->save();
        return apiRes(200 , 'success , order status has been changed to delivered and added commission on the order price');
    }

    public function order_items($orderid)
    {
        $order = Order::findOrFail($orderid);
        if($order['restaurant_id'] != auth('api_rest')->user()->id)
        {
            return apiRes(400 , 'can not show order data because it is not yours');
        }
        $orderitems = Order::where('id' , $orderid)->with('products')->first();
        //return apiRes(200 , 'order items' , $orderitems);
        return (new Orderitem($orderitems))->additional(['status' => 200 , 'msg' => 'order items']);
    }

    public function restaurant_offers()
    {
        $offers = auth('api_rest')->user()->offers()->where('activated' , 1)->get();
        return OfferResource::collection($offers)->additional(['status' => 200 , 'msg' => 'offers data']);
    }

    public function create_offer(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'name' => 'required|string|min:3|max:190',
            'description' => 'required|string|min:10|max:190',
            'discount_percent' => 'required|numeric|between:0,99.99',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
            'pic' => 'required|image|max:1900'
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation errors' , $validator->errors());
        }
        if(strtotime($request->input('from_date')) <= time())
        {
            return apiRes(400 , 'invalid from date');
        }
        if(strtotime($request->input('to_date')) <= strtotime($request->input('from_date')))
        {
            return apiRes(400 , 'invalid to date');
        }
        if($request->hasFile('pic'))
        {
            $path = $request->file('pic')->store('public');
            $filename = pathinfo($path , PATHINFO_BASENAME);
        }
        $offer = new Offer;
        $offer->name = $request->input('name');
        $offer->description = $request->input('description');
        $offer->discount_percent = $request->input('discount_percent');
        $offer->from_date = $request->input('from_date');
        $offer->to_date = $request->input('to_date');
        $offer->pic = $filename;
        auth('api_rest')->user()->offers()->save($offer);
        return (new OfferResource($offer))->additional(['status' => 200 , 'msg' => 'offer created data']);
    }

    public function update_offer(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'name' => 'required|string|min:3|max:190',
            'description' => 'required|string|min:10|max:190',
            'discount_percent' => 'required|numeric|between:0,99.99',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
            'pic' => 'nullable|image|max:1900',
            'offerid' => 'required|integer|min:1',
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation errors' , $validator->errors());
        }

        if(strtotime($request->input('from_date')) <= time())
        {
            return apiRes(400 , 'invalid from date');
        }
        if(strtotime($request->input('to_date')) <= strtotime($request->input('from_date')))
        {
            return apiRes(400 , 'invalid to date');
        }
        $offer = Offer::findOrFail($request->input('offerid'));
        if($offer['restaurant_id'] != auth('api_rest')->user()->id)
        {
            return apiRes(400 , 'you can not edit another restaurant offer');
        }
        if($request->hasFile('pic'))
        {
            Storage::delete($offer['pic']);
            $path = $request->file('pic')->store('public');
            $filename = pathinfo($path , PATHINFO_BASENAME);
            $offer->pic = $filename;
        }
        $offer->name = $request->input('name');
        $offer->description = $request->input('description');
        $offer->discount_percent = $request->input('discount_percent');
        $offer->from_date = $request->input('from_date');
        $offer->to_date = $request->input('to_date');
        $offer->save();
        return (new OfferResource($offer))->additional(['status' => 200 , 'msg' => 'offer updated data']);
    }

    public function restaurant_commissions()
    {
        $orders = auth('api_rest')->user()->orders()->where('order_status' , 'delivered')->get();
        $total_commission = 0;
        $total_sales = 0;
        foreach($orders as $order)
        {
            $total_commission = $order['commission'] + $total_commission;
            $total_sales = $total_sales + $order['price'];
        }
        $appfees = auth('api_rest')->user()->appfees()->get();
        $paid_fees = 0;
        foreach($appfees as $fee)
        {
            $paid_fees = $paid_fees + $fee['amount_paid'];
        }
        return apiRes(200 , 'sales and fees info' , ['total_commission' => $total_commission , 'total_sales' => $total_sales , 'paid_fees' => $paid_fees]);
    }
}
