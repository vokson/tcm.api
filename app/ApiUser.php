<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiUser extends Model
{
    protected $fillable = [
        'name', 'surname', 'email', 'password', 'updated_at'
    ];

    protected $hidden = [
        'password', 'access_token',
    ];

    public $timestamps = true;
}
