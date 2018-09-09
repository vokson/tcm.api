<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UploadedFile extends Model
{
    protected $fillable = [
        'uin', 'log', 'filename'
    ];

    protected $dateFormat = 'U';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'uploaded_files';

}
