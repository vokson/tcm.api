<?php

namespace App\Console\Commands;

use App\Http\Controllers\StatisticController;
use Illuminate\Console\Command;
use App\Http\Controllers\SettingsController;

class GetCountOfMistakesByProbability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checker:probability';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get count of mistakes by probability';

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
        SettingsController::save(
            'RATING_MISTAKE_COUNT',
            StatisticController::getCountOfMistakesByProbability(SettingsController::take('RATING_MISTAKE_PROBABILITY'))
        );
    }

}
