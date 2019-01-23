<?php

namespace App\Console\Commands;

use App\CheckedFile;
use App\Check;
use Illuminate\Console\Command;
use DateTime;

class AddExtensionToCheckFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agpz:add-ext-to-checked-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add extension to database for already uploaded files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $checks = Check::where('extension', '')->get();

        foreach ($checks as $record) {

            if ($record->file_id == null) {
                $record->extension = 'pdf';
            } else {
                $file = CheckedFile::find($record->file_id);
                $path_parts = pathinfo($file->original_name);
                $record->extension = strtolower($path_parts['extension']);
            }

            $record->save();

        }

    }

}
