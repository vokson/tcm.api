<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Log;
use App\Title;
use DateTime;
use App\Http\Controllers\SettingsController;

class TextLogImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agpz:import-text-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import logs fromt *.txt files to database';

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
        $directory = storage_path('app/import_txt_logs');
        $scanned_directory = array_diff(scandir($directory), array('..', '.'));

        foreach ($scanned_directory as $filename) {
            $this->analyzeTextLog($directory . '/' . $filename);
        }
    }

    private function analyzeTextLog($path)
    {
        $path_parts = pathinfo($path);
        $title = $path_parts['filename'];

        $lines = file($path, FILE_SKIP_EMPTY_LINES);

        $blocks = [];
        $lastDate = 'HEADER';
        $firstDate = null;

        foreach ($lines as $line) {

            if (trim($line) != "") {

                $date = DateTime::createFromFormat('d.m.Y', trim($line));

                if ($date === false) {
                    $blocks[$lastDate][] = $line;
                } else {

                    $lastDate = trim($line);

                    if (is_null($firstDate)) {
                        $firstDate = $lastDate;
                    }

                }
            }

        }

        $title_model = Title::where('name', '=', $title)->first();


        if (Title::where('name', '=', $title)->exists()) {
            $title_id = $title_model->id;
        } else {
            $title_model = new Title();
            $title_model->name = $title;
            $title_model->status = 1;
            $title_model->save();
            $title_id = $title_model->id;
        }

        $system_user_id = SettingsController::take('SYSTEM_USER_ID');

        foreach ($blocks as $key => $arr) {

            $dateStr = ($key === 'HEADER') ? $firstDate : $key;
            $date = DateTime::createFromFormat('d.m.Y', $dateStr);
            $timestamp = $date->getTimestamp();

            //Убаевляем одну секунду, если HEADER
            $timestamp -= 1;

            $what = '';

            foreach ($arr as $s) {
                $what .= '<p>' . mb_convert_encoding(trim($s), "UTF-8", "CP1251") . '</p>';
            }

            // Create new log
            $log = new Log();
            $log->to = $system_user_id;
            $log->from = $system_user_id;
            $log->title = $title_id;
            $log->created_at = $timestamp;
            $log->what = $what;
            $log->save();
        }


    }
}
