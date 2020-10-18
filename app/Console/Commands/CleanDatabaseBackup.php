<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as MyLog;

class CleanDatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup_clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean too old backups of database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

//    const LIFE_TIME = 14*24*60*60; // 2 weeks
//    const LOG_FILENAME = 'error_log.txt';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $directory = config('filesystems.databaseBackupPath');
        $scanned_directory = array_diff(scandir($directory), array('..', '.'));

        foreach ($scanned_directory as $filename) {
            $path = $directory . '/' . $filename;

            if (file_exists($path) && (time() - filemtime($path) > intval(config('database.backup_lifetime')) )) {

//            echo "path";
//            echo time();
//            echo filemtime($path);

                try {
                    unlink($path);

                } catch (Exception $e) {
                    MyLog::error($e->getMessage());
//                    file_put_contents(
//                        $directory . '/' . self::LOG_FILENAME,
//                        date("Y-m-d H:i:s") . ' - ' . $e->getMessage(),
//                        FILE_APPEND
//                    );
                }

            }
        }


    }
}
