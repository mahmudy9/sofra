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
use App\Client;
use App\Notification;

class RestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api_rest','is_restaurant_blocked'])->except([]);
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

        if($product['restaurant_id'] != auth('api_rest')->user()->id )
        {
            return apiRes(401 , 'you can not edit another restaurant product');
        }

        return apiRes(200 , 'product data' , $product);
    }

    public function update_product(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'productid' => 'required|integer|min:1',
            'name' => 'required|string|max:190',
            'description' => 'required|string|max:190',
            'cooking_duration' => 'required|string|max:50',
            'pic' => 'nullable|image|max:1900',
            'price' => 'required|numeric|between:0,99999999.99',
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation error' , $validator->errors());
        }
        $product = Product::findOrFail($request->input('productid'));

        if($product['restaurant_id'] != auth('api_rest')->user()->id)
        {
            return apiRes(401 , 'you can not edit another restaurant product');
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
        if($product['restaurant_id'] != auth('api_rest')->user()->id )
        {
            return apiRes(401 , 'you can not delete another restaurant product');
        }
        if($product->orders()->exists())
        {
            return apiRes(403 , 'you can not delete product , it has orders');
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
        $orders = auth('api_rest')->user()->orders()->where('restaurant_decision' , 'accepted')->where('order_status','pending')->paginate(10);
        return OrderResource::collection($orders)->additional(['status' => 200 , 'msg' => 'current orders data']);
    }

    public function old_orders()
    {
        $orders = auth('api_rest')->user()->orders()->where('order_status' , '<>' , 'pending')->paginate(10);
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
        //$orders = auth('api_rest')->user()->orders()->pluck('id')->toArray();
        //if(!in_array($request->input('orderid'), $orders))
        if($order['restaurant_id'] != auth('api_rest')->user()->id)
        {
            return apiRes(401 , 'error , not your order to accept');
        }
        if($order['order_status'] == 'rejected' || $order['order_status'] == 'delivered')
        {
            return apiRes(403 , 'error , can not accept order, its already finished');
        }
        $order->restaurant_decision = 'accepted';
        $order->save();
        $client = Client::find($order['client_id']);
        $note = $client->notifications()->create([
            'title' => 'Your order has been accpted from the restaurant',
            'content' => 'Your order with id '.$order->id.' has been accepted and will be dleivered to your address soon',
            'order_id' => $order->id,
        ]);
        notifyByFirebase($note->title , $note->content , $client->tokens()->pluck('token')->toArray() , ['order_id' => $order->id]);
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
        //$orders = auth('api_rest')->user()->orders()->pluck('id')->toArray();
        //if(!in_array($request->input('orderid'), $orders))
        if($order['restaurant_id'] != auth('api_rest')->user()->id)
        {
            return apiRes(401 , 'error , not your order to accept');
        }
        if($order['order_status'] == 'rejected' || $order['order_status'] == 'delivered')
        {
            return apiRes(403 , 'error , can not reject order, its already finished');
        }
        $order->restaurant_decision = 'rejected';
        $order->order_status = 'rejected';
        $order->save();
        $client = Client::find($order['client_id']);
        $note = $client->notifications()->create([
            'title' => 'Your order has been rejected from the restaurant',
            'content' => 'Your order with id '.$order->id.' has been rejected from the restaurant',
            'order_id' => $order->id,
        ]);
        notifyByFirebase($note->title , $note->content , $client->tokens()->pluck('token')->toArray() , ['order_id' => $order->id]);

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
        //$orders = auth('api_rest')->user()->orders()->pluck('id')->toArray();
        //if(!in_array($request->input('orderid'), $orders))
        if($order['restaurant_id'] != auth('api_rest')->user()->id)
        {
            return apiRes(401 , 'error , not your order to change status');
        }
        if($order['order_status'] == 'delivered' || $order['order_status'] == 'rejected')
        {
            return apiRes(403 , 'error , can not change status of order, its already finished');
        }
        if($order['restaurant_decision'] == 'pending')
        {
            return apiRes(409, 'you can not change status to delivered , you have to accept order first');
        }
        $order->order_status = 'delivered';
        $order->save();
        $client = Client::find($order['client_id']);
        $note = $client->notifications()->create([
            'title' => 'Your order has been confirmed being delivered from the restaurant',
            'content' => 'Your order with id '.$order->id.' has been confirmed dleivered to your address',
            'order_id' => $order->id,
        ]);
        notifyByFirebase($note->title , $note->content , $client->tokens()->pluck('token')->toArray() , ['order_id' => $order->id]);

        return apiRes(200 , 'success , order status has been changed to delivered and added commission on the order price');
    }

    public function order_items($orderid)
    {
        $order = Order::findOrFail($orderid);
        if($order['restaurant_id'] != auth('api_rest')->user()->id)
        {
            return apiRes(401 , 'can not show order data because it is not yours');
        }
        $orderitems = Order::where('id' , $orderid)->with('products')->first();
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
            return apiRes(401 , 'you can not edit another restaurant offer');
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

    public function delete_offer($offerid)
    {
        $offer = Offer::findOrFail($offerid);
        if($offer['restaurant_id'] != auth('api_rest')->user()->id)
        {
            return apiRes(401 , 'unauthorized to delete offer');
        }
        if($offer->orders()->exists())
        {
            return apiRes(403 , 'cant delete offer it has orders');
        }
        $offer->delete();
        return apiRes(200 , 'success, offer has been deleted');
    }

    public function restaurant_commissions()
    {
        $commissions = auth('api_rest')->user()->orders()->where('order_status' , 'delivered')->sum('commission');
        $prices = auth('api_rest')->user()->orders()->where('order_status' , 'delivered')->sum('price');
        $commissions = round($commissions , 2);
        $prices = round($prices , 2);
        $paid_fees = auth('api_rest')->user()->appfees()->sum('amount_paid');
        return apiRes(200 , 'sales and fees info' , ['total_commission' => $commissions , 'total_sales' => $prices , 'paid_fees' => $paid_fees]);
    }


    public function my_notifications()
    {
        $notes = auth('api_rest')->user()->notifications()->latest()->paginate(10);
        return apiRes(200 , 'your notifications' , $notes);
    }


}
