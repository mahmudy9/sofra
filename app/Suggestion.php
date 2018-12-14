<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    public function restaurant()
    {
        return $this->belongsTo('App\Restaurant' , 'restaurant_id');
    }

    public function client()
    {
        return $this->belongsTo('App\Client' , 'client_id');
    }

}
