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
use App\Http\Resources\Order as OrderResource;
use App\Http\Resources\Offer as OfferResource;
use App\Complaint;
use App\Review;
use App\Http\Resources\ReviewResource;
use OneSignal;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api_client'])
        ->except(['restaurants' , 'restaurant_products' , 'restaurant_reviews' , 'restaurant_details' , 'product']);
    }

    
    public function restaurants()
    {
        $rests = Restaurant::where('status' , 'open')->paginate(10);
        return RestResource::collection($rests)->additional(['status' => 200 , 'msg' => 'restaurants data']);
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
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:products,id',
            'restaurant' => 'required|integer',
            'notes' => 'nullable|string|max:190',
            'offer' => 'nullable|integer'
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'Validation error' , $validator->errors());
        }
        $rest = Restaurant::findOrFail($request->input('restaurant'));
        //$products_array = $rest->products()->pluck('id')->toArray();
        if($rest['status'] == 'closed')
        {
            return apiRes(400 , 'you can not make order ,restaurant is closed');
        }
        $order = new Order;
        $order->notes = $request->input('notes');
        if($request->has('offer'))
        {
            $order->offer_id = $request->input('offer');
            $offer = Offer::findOrFail($request->input('offer'));
            $discount = $offer->discount;
            $order->discount = $discount;
        }

        $order->restaurant_id = $request->input('restaurant');
        $order->order_status = 'pending';
        $order->client_decision = 'pending';
        $order->restaurant_decision = "pending";
        $order->price = 0;
        $order->commission = 0;
        $order->delivery_fee = $rest->delivery_fee;
        $order->total = 0;
        auth('api_client')->user()->orders()->save($order);

        
        $items = $request->input('items');
        $price = 0;
        foreach($items as $item)
        {
            $product = Product::find($item['item_id']);
            //if(!in_array($product['id'] , $products_array))
            if($product['restaurant_id'] != $rest['id'])
            {
                $order->products()->detach();
                $order->delete();
                return apiRes(400 , 'error , invalid items , not in restaturant products');
            }
            $price = $price + $product['price'] * $item['quantity'];
            $product_ready = [
                $product['id'] => [
                    'quantity' => $item['quantity'],
                    'price' => $product['price'],
                    'special_order' => $item['special_order'],
                ],
            ];
            $order->products()->attach($product_ready);
        }

        if($price < $rest['min_order'])
        {
            $order->products()->detach();
            $order->delete();
            return apiRes(400 , 'your order is less than the min charge order');
        }
        
        $com = Commission::first();
        $commission = $price*($com->commission/100);
        $order->commission = $commission;
        
        $order->price = $price;

        $total = $price + $rest->delivery_fee;
        $order->delivery_fee = $rest->delivery_fee;

        if($request->has('offer'))
        {
            $total = $total - $total * ($discount/100);
        }

        $order->total = $total;
        $order->save();

        
        return apiRes(200 , 'order created' , $order);
    }

    
    public function client_address()
    {
        $client = auth('api_client')->user();
        return apiRes(200 , 'client address' , ['address' => $client['city']['name'] .' '. $client['neighborhood']['name'] .' '. $client['address']]);
    }

    public function pending_orders()
    {
        $orders = auth('api_client')->user()->orders()->where('order_status' , 'pending')->get();
        return OrderResource::collection($orders)->additional(['msg' => 'pending orders data' , 'status' => 200]);
    }

    public function restaurant_accepted_orders()
    {
        $orders = auth('api_client')->user()->orders()->where('restaurant_decision' , 'accepted')->get();
        return OrderResource::collection($orders)->additional(['msg' => 'orders accepted from restaurant' , 'status' => 200]);
    }

    public function restaurant_rejected_orders()
    {
        $orders = auth('api_client')->user()->orders()->where('order_status' , 'rejected')->where('restaurant_decision' , 'rejected')->get();
        return OrderResource::collection($orders)->additional(['msg' => 'orders rejected from restaurant' , 'status' => 200]);
    }

    public function client_rejected_orders()
    {
        $orders = auth('api_client')->user()->orders()->where('order_status' , 'rejected')->where('client_decision' , 'rejected')->get();
        return OrderResource::collection($orders)->additional(['msg' => 'orders rejected from client' , 'status' => 200]);
    }

    public function delivered_orders()
    {
        $orders = auth('api_client')->user()->orders()->where('order_status' , 'delivered')->get();
        return OrderResource::collection($orders)->additional(['msg' => 'orders delivered' , 'status' => 200]);
    }

    public function accept_order($orderid)
    {
        $order = Order::findOrFail($orderid);
        if($order['order_status'] == 'rejected' || $order['order_status'] == 'delivered')
        {
            return apiRes(400 , 'can not accept finished order');
        }
        if($order['restaurant_decision'] == 'pending')
        {
            return apiRes(400 , 'can not accept order pending restaurant decision');
        }
        $order->order_status = 'delivered';
        $order->client_decision = 'accepted';
        $order->save();
        return apiRes(200 , 'order accepted by you');
    }

    public function reject_order($orderid)
    {
        $order = Order::findOrFail($orderid);
        if($order['order_status'] == 'rejected' || $order['order_status'] == 'delivered')
        {
            return apiRes(400 , 'can not reject finished order');
        }
        $order->client_decision = 'rejected';
        $order->order_status = 'rejected';
        $order->save();
        return apiRes(200 , 'order has been rejected');
    }

    public function offers()
    {
        $offers = Offer::where('activated' , 1)->paginate(10);
        return OfferResource::collection($offers)->additional(['msg' => 'activated offers' , 'status' => 200]);
    }

    public function create_complaint(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'name' => 'required|string|min:3|max:190',
            'email' => 'required|email',
            'phone' => 'required|numeric',
            'content' => 'required|string|min:10',
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation error' , $validator->errors());
        }
        $comp = new Complaint;
        $comp->name = $request->input('name');
        $comp->email = $request->input('email');
        $comp->phone = $request->input('phone');
        $comp->content = $request->input('content');
        $client = auth('api_client')->user();
        $client->complaints()->save($comp);
        return apiRes(200 , 'complaint saved , thank you for your feedback');
    }

    public function create_suggestion(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'name' => 'required|string|min:3|max:190',
            'email' => 'required|email',
            'phone' => 'required|numeric',
            'content' => 'required|string|min:10',
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation error' , $validator->errors());
        }
        $comp = new Suggestion;
        $comp->name = $request->input('name');
        $comp->email = $request->input('email');
        $comp->phone = $request->input('phone');
        $comp->content = $request->input('content');
        $client = auth('api_client')->user();
        $client->suggestions()->save($comp);
        return apiRes(200 , 'suggestion saved , thank you for your feedback');
    }

    public function create_contact(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'name' => 'required|string|min:3|max:190',
            'email' => 'required|email',
            'phone' => 'required|numeric',
            'content' => 'required|string|min:10',
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation error' , $validator->errors());
        }
        $comp = new Contact;
        $comp->name = $request->input('name');
        $comp->email = $request->input('email');
        $comp->phone = $request->input('phone');
        $comp->content = $request->input('content');
        $client = auth('api_client')->user();
        $client->contacts()->save($comp);
        return apiRes(200 , 'contact saved , thank you for your feedback');
    }

    public function create_review(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'restaurant' => 'required|integer|min:1',
            'review' => 'required|string|max:191|min:2',
            'rating' => 'required|numeric|between:0.5,5.0'
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation error' , $validator->errors());
        }
        $restaurant = Restaurant::findOrFail($request->input('restaurant'));
        if(!$restaurant->orders()->where('client_id' , auth('api_client')->user()->id)->exists())
        {
            return apiRes(400 , 'error , you can not review a resturant you did not order from');
        }
        $review = new Review;
        $review->restaurant_id = $request->input('restaurant');
        $review->review = $request->input('review');
        $review->rating = $request->input('rating');
        auth('api_client')->user()->reviews()->save($review);
        return (new ReviewResource($review))->additional(['msg' => 'review data' , 'status' => 200]);
    }

    public function update_review(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'review_id' => 'required|integer|min:1',
            'review' => 'required|string|min:3|max:190',
            'rating' => 'required|numeric|between:0.5,5.0',
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation error' , $validator->errors());
        }
        $review = Review::findOrFail($request->input('review_id'));
        if($review['client_id'] != auth('api_client')->user()->id)
        {
            return apiRes(400 , 'error, this is not your review');
        }
        $review->review = $request->input('review');
        $review->rating = $request->input('rating');
        $review->save();
        return (new ReviewResource($review))->additional(['status' => 200 , 'msg' => 'review updated']);
    }

    public function destroy_review($reviewid)
    {
        $review = Review::findOrFail($reviewid);
        if($review['client_id'] != auth('api_client')->user()->id)
        {
            return apiRes(400 , 'error , it is not your review to delete');
        }
        $review->delete();
        return apiRes(200 , 'success , your review has been deleted');
    }


}
