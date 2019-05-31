<?php

namespace App\Console\Commands;

use App\SenderFolder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\SenderCreateFolderNotification;

class MakeMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agpz:mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        Mail::to('noskov_as@niik.ru')
            ->send(new SenderCreateFolderNotification(SenderFolder::find(419)));
    }

}
