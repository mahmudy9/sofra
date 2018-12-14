<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Restaurant extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $hidden = ['password'];


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function category()
    {
        return $this->belongsTo('App\Category' , 'category_id');
    }

    public function contacts()
    {
        return $this->hasMany('App\Contact' , 'restaurant_id');
    }

    public function complaints()
    {
        return $this->hasMany('App\Complaint' , 'restaurant_id');
    }

    public function suggestions()
    {
        return $this->hasMany('App\Suggestion' , 'restaurant_id');
    }

    public function city()
    {
        return $this->belongsTo('App\City' , 'city_id');
    }

    public function neighborhood()
    {
        return $this->belongsTo('App\Neighborhood' , 'neighborhood_id');
    }

    public function products()
    {
        return $this->hasMany('App\Product' , 'restaurant_id');
    }

    public function offers()
    {
        return $this->hasMany('App\Offer' , 'restaurant_id');
    }

    public function appfees()
    {
        return $this->hasMany('App\Appfee' , 'restaurant_id');
    }

    public function reviews()
    {
        return $this->hasMany('App\Review' , 'restaurant_id');
    }

}