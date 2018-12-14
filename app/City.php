<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public function clients()
    {
        return $this->hasMany('App\Client' , 'city_id');
    }

    public function restaurants()
    {
        return $this->hasMany('App\Restaurant' , 'city_id');
    }

    public function neighborhoods()
    {
        return $this->hasMany('App\Neighborhood' , 'city_id');
    }
}
