<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy database.sqlite file into another folder';

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
        $filename = Carbon::now()->toDateTimeString() . ".sqlite";
        $filename = str_replace(' ', '_', $filename);
        $filename = str_replace(':', '_', $filename);

        $path = config('filesystems.databaseBackupPath') . DIRECTORY_SEPARATOR .  $filename;

        File::copy(database_path('database.sqlite'), $path);
    }
}
