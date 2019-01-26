<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SenderFolder extends Model
{
    protected $fillable = [
        'name', 'owner'
    ];

    protected $dateFormat = 'U';

    protected $table = 'sender_folders';

}
