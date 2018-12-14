<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Paymentmethod extends Model
{
    public function appfees()
    {
        return $this->hasMany('App\Appfee' , 'paymentmethod_id');
    }
}
