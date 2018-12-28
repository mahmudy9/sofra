<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{

    protected $fillable = ['title' , 'content' , 'order_id'];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
