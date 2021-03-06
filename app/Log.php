<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'from', 'to', 'title', 'what'
    ];

    protected $dateFormat = 'U';

}
