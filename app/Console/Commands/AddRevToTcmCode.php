<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Doc;

class AddRevToTcmCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vlg:rev';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $docs = Doc::all();

        foreach ($docs as $doc) {
            $doc->code_1 = $doc->code_1 . '-IS' . $doc->revision;
            $doc->save();
        }
    }
}
