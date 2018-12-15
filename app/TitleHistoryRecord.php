<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TitleHistoryRecord extends Model
{
    protected $table = 'titles_history';
    protected $dateFormat = 'U';
}
