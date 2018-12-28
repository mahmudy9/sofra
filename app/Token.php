<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{

    protected $fillable = ['type' , 'token'];

    public function tokenable()
    {
        return $this->morphTo();
    }
}
