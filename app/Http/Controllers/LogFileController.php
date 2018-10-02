<?php

namespace App\Http\Controllers;

use App\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Log;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class LogFileController extends Controller
{
    public function upload(Request $request)
    {

        $log_id = null;
        if (Input::has('log_id')) {

            if (!Log::where('id', '=', Input::get('log_id'))->exists()) {
                return Feedback::getFeedback(604);
            } else {
                $log_id = $request->input('log_id');
            }
        }


        if (!$request->hasFile('log_file')) {
            return Feedback::getFeedback(601);
        };

        if (!$request->file('log_file')->isValid()) {
            return Feedback::getFeedback(602);
        }

        if (!Input::has('uin')) {
            return Feedback::getFeedback(605);
        }

        try {

            $path = Storage::putFile(
                'log_file_storage' . DIRECTORY_SEPARATOR . 'FILES' . DIRECTORY_SEPARATOR .
                $this->createFolderByNumber($log_id) . DIRECTORY_SEPARATOR . $log_id,
                $request->file('log_file')
            );

        } catch (QueryException $e) {

            return Feedback::getFeedback(607);
        }



        if ($path === false) {
            return Feedback::getFeedback(606);
        }

        $file = new UploadedFile();
        $file->original_name = $request->file('log_file')->getClientOriginalName();
        $file->size = $request->file('log_file')->getSize();
        $file->log = $log_id;
        $file->server_name = $path;
        $file->uin = $request->input('uin');
        $file->save();

        return Feedback::getFeedback(0, [
            'id' => $file->id,
            'uin' => $file->uin,
            'log_id' => $file->log
        ]);
    }

    protected function createFolderByNumber($number)
    {
        $min = intdiv($number, 1000) * 1000 + 1;
        $max = (intdiv($number, 1000) + 1) * 1000;

        if ($number % 1000 == 0) {
            $min = $min - 1000;
            $max = $max - 1000;
        }

//        echo "MIN = " . $min . '<br/>';
//        echo "MAX = " . $max . '<br/>';

        return sprintf("%05d-%05d", $min, $max);
    }

    public function get(Request $request)
    {
        $log_id = trim(Input::get('log_id', ''));

        $items = DB::table('uploaded_files')
            ->where('log', '=', $log_id)
            ->select(['id', 'uin', 'original_name', 'size'])
            ->orderBy('id', 'asc')
            ->get();


        return Feedback::getFeedback(0, [
            'items' => $items->toArray(),
        ]);


    }

    public function delete(Request $request)
    {
        $file_id = null;
        if (Input::has('id')) {

            if (!UploadedFile::where('id', '=', Input::get('id'))->exists()) {
                return Feedback::getFeedback(604);
            } else {
                $file_id = $request->input('id');
            }
        }

        $file = UploadedFile::find($file_id);

        try {

            Storage::delete($file->server_name);

        } catch (QueryException $e) {

            return Feedback::getFeedback(603);
        }

//        $log_id = $file->log;
        $uin = $file->uin;

        $file->delete();

        return Feedback::getFeedback(0, [
//            'log_id' => $log_id,
            'uin' => $uin
        ]);
    }

    public function download(Request $request)
    {
        $file_id = null;
        if (Input::has('id')) {

            if (!UploadedFile::where('id', '=', Input::get('id'))->exists()) {
                return Feedback::getFeedback(604);
            } else {
                $file_id = $request->input('id');
            }
        }

        $file = UploadedFile::find($file_id);

        $headers = array(
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Filename',
            'Content-Filename' => rawurlencode($file->original_name)
        );

        return response()->download(storage_path("app/" . $file->server_name), "", $headers);
    }

}
