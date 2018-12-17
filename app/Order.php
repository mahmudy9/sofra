<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function products()
    {
        return $this->belongsToMany('App\Product' , 'order_product');
    }

    public function client()
    {
        return $this->belongsTo('App\Client' , 'client_id');
    }

    public function offer()
    {
        return $this->belongsTo('App\Offer' , 'offer_id');
    }

    public function restaurant()
    {
        return $this->belongsTo('App\Restaurant' , 'restaurant_id');
    }
}
