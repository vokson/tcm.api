<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = [
        'owner', 'name', 'value', 'is_switchable'
    ];

    protected $dateFormat = 'U';

    protected $table = 'user_settings';
}
