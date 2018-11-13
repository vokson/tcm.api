<?php

namespace App\Http\Controllers;

use App\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\UploadedFile;
use App\Http\Controllers\FeedbackController as Feedback;

class ServiceController extends Controller
{
    public function getDatabaseBackup()
    {
        $headers = array(
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Filename',
            'Content-Filename' => rawurlencode(Carbon::now()->toDateTimeString() . ".sqlite"),
        );

        return response()->download(database_path('database.sqlite'), "", $headers);
    }

    public function updateAttachmentStatuses()
    {

        Log::where('is_attachment_exist', 1)->update(['is_attachment_exist' => 0]);

        $files = UploadedFile::all();

        foreach ($files as $file) {

            $log = Log::find($file->log);

            if (!is_null($log)) {
                $log->is_attachment_exist = 1;
                $log->save();
            }
        }

        return Feedback::getFeedback();

    }

    public function info()
    {
        phpinfo();
        return;
    }

}
