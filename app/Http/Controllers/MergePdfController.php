<?php

namespace App\Http\Controllers;

use App\PdfMergeFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController as Feedback;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MergePdfController extends Controller
{

    public function get(Request $request)
    {

        $items = DB::table('pdf_merge_files')
            ->select(['id', 'drop_id as group', 'owner', 'original_name as filename', 'created_at as date'])
            ->where('owner', ApiAuthController::id($request))
            ->orderBy('group')
            ->orderBy( 'filename')
            ->get();

        return Feedback::getFeedback(0, [
            'items' => $items->toArray(),
        ]);
    }

    public function clean(Request $request)
    {

        $deletedRows = PdfMergeFile::where('owner', ApiAuthController::id($request))->delete();

        return Feedback::getFeedback(0, [
            'items' => $deletedRows,
        ]);
    }

    public function upload(Request $request)
    {


        if (!$request->hasFile('pdf_file')) {
            return Feedback::getFeedback(601);
        };

        if (!$request->file('pdf_file')->isValid()) {
            return Feedback::getFeedback(602);
        }

        if (!Input::has('uin')) {
            return Feedback::getFeedback(605);
        }

        if (!Input::has('drop_uin')) {
            return Feedback::getFeedback(605);
        }

        $originalNameOfFile = $request->file('pdf_file')->getClientOriginalName();

        if (!MergePdfController::validateNameOfNewFile($originalNameOfFile)) {
            return Feedback::getFeedback(609);
        }

        $owner = ApiAuthController::id($request);
        $path_parts = pathinfo($originalNameOfFile);

        $drop_uin = $request->input('drop_uin');

        DB::beginTransaction(); // НАЧАЛО ТРАНЗАКЦИИ

        $maxDropIdForCurrentUin = PdfMergeFile::where('drop_uin', $drop_uin)->max('drop_id');
        $maxDropIdForCurrentUser = PdfMergeFile::where('owner', $owner)->max('drop_id');

        DB::commit(); // КОНЕЦ ТРАНЗАКЦИИ

        $drop_id = (is_null($maxDropIdForCurrentUin)) ? ($maxDropIdForCurrentUser + 1) : $maxDropIdForCurrentUin;

        $file = new PdfMergeFile();
        $file->original_name = $path_parts['filename'] . '.' . strtolower($path_parts['extension']);
        $file->size = $request->file('pdf_file')->getSize();
        $file->uin = $request->input('uin');
        $file->drop_id = $drop_id;
        $file->drop_uin = $drop_uin;
        $file->server_name = '';
        $file->owner = $owner;
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

        exec('cd ' . storage_path("app/pdf_merge_storage") . '; pdftk 1.pdf 2.pdf cat output MERGED.pdf 2>&1', $output, $result);
        $output[] = 'RETURN ' . $result;

        file_put_contents(storage_path("app/pdf_merge_storage/log.txt"), print_r($output, true));

        $headers = array(
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Filename',
            'Content-Filename' => 'MERGED.pdf'
        );

        return response()->download(storage_path("app/pdf_merge_storage/MERGED.pdf"), "", $headers);
    }
}
