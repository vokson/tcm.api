<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PdfMergeFile extends Model
{

    protected $dateFormat = 'U';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pdf_merge_files';

}
