<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    public function restaurant()
    {
        return $this->belongsTo('App\Restaurant' , 'restaurant_id');
    }

    public function orders()
    {
        return $this->hasMany('App\Order'  , 'offer_id');
    }
}
