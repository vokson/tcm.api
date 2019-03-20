<?php

namespace App\Http\Controllers;

use App\PdfMergeFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController as Feedback;
use Illuminate\Support\Facades\Storage;

class MergePdfController extends Controller
{

    public function upload(Request $request)
    {

        $folder_id = $request->input('folder_id', 1);

        if (!$request->hasFile('pdf_file')) {
            return Feedback::getFeedback(601);
        };

        if (!$request->file('pdf_file')->isValid()) {
            return Feedback::getFeedback(602);
        }

        if (!Input::has('uin')) {
            return Feedback::getFeedback(605);
        }

        $originalNameOfFile = $request->file('pdf_file')->getClientOriginalName();

        if (!MergePdfController::validateNameOfNewFile($originalNameOfFile))  {
            return Feedback::getFeedback(609);
        }

        $path_parts = pathinfo($originalNameOfFile);

        $file = new PdfMergeFile();
        $file->original_name = $path_parts['filename'] . '.' . strtolower($path_parts['extension']);
        $file->size = $request->file('pdf_file')->getSize();
        $file->uin = $request->input('uin');
        $file->folder = $folder_id;
        $file->server_name = '';
        $file->save();


        try {

            $path = Storage::putFile(
                'log_file_storage' . DIRECTORY_SEPARATOR . 'PDF_MERGE_FILES',
                $request->file('pdf_file')
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
        ]);
    }

    public static function validateNameOfNewFile($fileNameWithExtension)
    {
        $regExpForNewFile = "/.*\.(pdf|PDF){1}$/";
        return (preg_match($regExpForNewFile, $fileNameWithExtension) === 1);
    }


    public function download(Request $request)
    {

        $output = [];
        $result = null;

//        exec('cd ' . storage_path("app/pdf_merge_storage"), $output, $result);
//        $output[] = 'RETURN ' . $result;
//
//        exec('pwd', $output, $result);
//        $output[] = 'RETURN ' . $result;

        exec('cd ' . storage_path("app/pdf_merge_storage").'; pdftk 1.pdf 2.pdf cat output MERGED.pdf 2>&1', $output, $result);
        $output[] = 'RETURN ' . $result;

        file_put_contents(storage_path("app/pdf_merge_storage/log.txt"),print_r( $output, true));

        $headers = array(
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Filename',
            'Content-Filename' => 'MERGED.pdf'
        );

        return response()->download(storage_path("app/pdf_merge_storage/MERGED.pdf"), "", $headers);
    }
}
