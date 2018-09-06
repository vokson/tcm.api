<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function uploadFile(Request $request)
    {
        $path = Storage::putFile('avatars', $request->file('test_file'));
        return $path;
    }

    public function info()
    {
        phpinfo();
        return;
    }

}
