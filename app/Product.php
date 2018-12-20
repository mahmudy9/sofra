<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function restaurant()
    {
        return $this->belongsTo('App\Restaurant' , 'restaurant_id');
    }

    public function orders()
    {
        return $this->belongsToMany('App\Order' , 'order_product')->withPivot('quantity' , 'price' , 'special_order');
    }
}
