<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\RestResetPasswordNotification;

class Restaurant extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $hidden = ['password'];


    protected $guard = "api_rest";

    protected $appends = ['rating' , 'is_activated' , 'commissions_sum'];

    public function getRatingAttribute()
    {
        $rating = $this->reviews()->avg('rating');
        $rating = round($rating , 2);
        return $rating;
    }

    public function getCommissionsSumAttribute()
    {
        $com = $this->orders()->where('order_status' , 'delivered')->sum('commission');
        return $com;
    }

    public function getIsActivatedAttribute()
    {
        $paidfees = $this->appfees()->sum('amount_paid');
        if($this->commissions_sum > $paidfees + 500 )
        {
            return false;
        }
        return true;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new RestResetPasswordNotification($token));
    }

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

    public function notifications()
    {
        return $this->morphMany('App\Notification' , 'notifiable');
    }

    public function player()
    {
        return $this->morphOne('App\Player' , 'playable');
    }

    public function tokens()
    {
        return $this->morphMany('App\Token' , 'tokenable');
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

    public function orders()
    {
        return $this->hasMany('App\Order' , 'restaurant_id');
    }

}