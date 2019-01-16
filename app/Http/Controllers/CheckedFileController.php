<?php

namespace App\Http\Controllers;

use App\ApiUser;
use App\CheckedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\CheckController;

class CheckedFileController extends Controller
{
    public function upload(Request $request)
    {

        if (!$request->hasFile('log_file')) {
            return Feedback::getFeedback(601);
        };

        if (!$request->file('log_file')->isValid()) {
            return Feedback::getFeedback(602);
        }

        if (!Input::has('uin')) {
            return Feedback::getFeedback(605);
        }

        $originalNameOfFile = $request->file('log_file')->getClientOriginalName();

        if (!CheckController::validateNameOfNewFile($originalNameOfFile) &&
            !CheckController::validateNameOfCheckedFile($originalNameOfFile)) {
            return Feedback::getFeedback(609);
        }


        $file = new CheckedFile();
        $file->original_name = $originalNameOfFile;
        $file->size = $request->file('log_file')->getSize();
        $file->uin = $request->input('uin');
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

        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();

        $errorCode =  CheckController::add($file->id, $user->id);

        if ($errorCode == 0) {
            return Feedback::getFeedback(0, [
                'id' => $file->id,
                'uin' => $file->uin,
                'log_id' => $file->log
            ]);
        } else {
            return Feedback::getFeedback($errorCode);
        }
    }

    public static function delete($id)
    {
        $file = CheckedFile::find($id);

        if (!is_null($file)) {

            try {
                Storage::delete($file->server_name);

            } catch (QueryException $e) {

                return false;
            }
        }

        CheckedFile::destroy($id);

        return true;
    }


}
