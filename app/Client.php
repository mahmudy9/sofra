<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ClientResetPasswordNotification;

class Client extends Authenticatable implements JWTSubject
{
    use Notifiable;


    protected $hidden = ['password'];

    protected $guard = "api_client";

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ClientResetPasswordNotification($token));
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

    public function city()
    {
        return $this->belongsTo('App\City' , 'city_id');
    }

    public function neighborhood()
    {
        return $this->belongsTo('App\Neighborhood' , 'neighborhood_id');
    }

    public function notifications()
    {
        return $this->hasMany('App\Notification' , 'client_id');
    }

    public function contacts()
    {
        return $this->hasMany('App\Contact' , 'client_id');
    }

    public function complaints()
    {
        return $this->hasMany('App\Complaint' , 'client_id');
    }

    public function suggestions()
    {
        return $this->hasMany('App\Suggestion' , 'client_id');
    }

    public function orders()
    {
        return $this->hasMany('App\Order' , 'client_id');
    }

    public function reviews()
    {
        return $this->hasMany('App\Review' , 'client_id');
    }

}
