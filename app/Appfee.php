<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Appfee extends Model
{
    public function paymentmethod()
    {
        return $this->belongsTo('App\Paymentmethod' , 'paymentmethod_id');
    }

    public function restaurant()
    {
        return $this->blengsTo('App\Restaurant' , 'restaurant_id');
    }
}
