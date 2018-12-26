<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public function notifiable()
    {
        return $this->morphTo();
    }
}
