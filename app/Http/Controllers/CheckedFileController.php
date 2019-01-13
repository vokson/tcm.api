<?php

namespace App\Http\Controllers;

use App\CheckedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\SettingsController as Settings;
use App\Http\Controllers\ServiceController;

class CheckedFileController extends Controller
{
    public function upload(Request $request)
    {

//        $log_id = null;
//        if (Input::has('log_id')) {
//
//            if (!Log::where('id', '=', Input::get('log_id'))->exists()) {
//                return Feedback::getFeedback(604);
//            } else {
//                $log_id = $request->input('log_id');
//            }
//        }


        if (!$request->hasFile('log_file')) {
            return Feedback::getFeedback(601);
        };

        if (!$request->file('log_file')->isValid()) {
            return Feedback::getFeedback(602);
        }

        if (!Input::has('uin')) {
            return Feedback::getFeedback(605);
        }

        $file = new CheckedFile();
        $file->original_name = $request->file('log_file')->getClientOriginalName();
        $file->size = $request->file('log_file')->getSize();
        $file->uin = $request->input('uin');
        $file->check_id = 0;
        $file->server_name = '';
        $file->save();


        try {

            $path = Storage::putFile(
                'log_file_storage' . DIRECTORY_SEPARATOR . 'CHECKED_FILES' . DIRECTORY_SEPARATOR .
                ServiceController::createFolderForFileByNumber($file->id),
                $request->file('log_file')
            );

        } catch (QueryException $e) {
            $file->delete();
            return Feedback::getFeedback(607);
        }


        if ($path === false) {
            $file->delete();
            return Feedback::getFeedback(606);
        }

        $file->server_name = $path;
        $file->save();

        return Feedback::getFeedback(0, [
            'id' => $file->id,
            'uin' => $file->uin,
            'log_id' => $file->log
        ]);
    }





}
