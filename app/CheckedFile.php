<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckedFile extends Model
{

    protected $dateFormat = 'U';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'checked_files';

}
