<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SenderFile extends Model
{

    protected $dateFormat = 'U';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sender_files';

}
