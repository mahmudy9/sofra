<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function restaurant()
    {
        return $this->belongsTo('App\Product' , 'restaurant_id');
    }

    public function orders()
    {
        return $this->belongsToMany('App\Order' , 'order_product');
    }
}
