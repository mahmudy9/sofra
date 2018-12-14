<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model
{
    public function clients()
    {
        return $this->hasMany('App\Client' , 'neighborhhod_id');
    }

    public function restaurants()
    {
        return $this->hasMany('App\Restaurant' , 'neighborhood_id');
    }

    public function city()
    {
        return $this->belongsTo('App\City' , 'city_id');
    }

}
