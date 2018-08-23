<?php

namespace App\Http\Controllers;

use App\Log;
use Carbon\Carbon;

class ServiceController extends Controller
{
    public function getDatabaseBackup()
    {
        $headers = array(
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Filename',
            'Content-Filename' => Carbon::now()->toDateTimeString() . ".sqlite",
        );

        return response()->download(database_path('database.sqlite'), "", $headers);
    }


}
