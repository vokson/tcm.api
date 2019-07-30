<?php

namespace App\Http\Controllers;

use App\Doc;
use App\Log;
use App\Title;
use App\UploadedFile;
use Hamcrest\Core\Set;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController as Feedback;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\DB;
use DateTime;
use Exception;

class DocsController extends Controller
{

//$queryCreator = new DocumentWhereQueryCreator();
//
//if ($request->input('only_last_rev') == 1) {
//$docs = DB::table('docs')
//->join('transmittals', function ($join) {
//    $join->on('docs.trans_id', '=', 'transmittals.id');
//})
//->join('max_revs', function ($join) {
//    // nipi_code, rev are used to avoid SQL error
//    $join->on('docs.nipigaz_code', '=', 'max_revs.nipi_code');
//    $join->on('docs.revision', '=', 'max_revs.rev');
//})
//->select('docs.*', 'transmittals.name as transmittal', 'transmittals.issued_at')
//->where($queryCreator->make($request))
//->get();
//} else {
//    $docs = DB::table('docs')
//        ->join('transmittals', function ($join) {
//            $join->on('docs.trans_id', '=', 'transmittals.id');
//        })
//        ->select('docs.*', 'transmittals.name as transmittal', 'transmittals.issued_at')
//        ->where($queryCreator->make($request))
//        ->get();
//}

    public function search(Request $request)
    {
        $parameters = [
            'code_1' => '',
            'code_2' => '',
            'class' => '',
            'revision' => '',
            'title_en' => '',
            'title_ru' => ''
        ];

        foreach ($parameters as $key => $value) {
            $parameters[$key] = $request->input($key, '');
        }

        $transmittalName = $request->input('transmittal', '');
        $date1 = intval(trim(Input::get('date1', '')));
        $date2 = intval(trim(Input::get('date2', '')));
        $isOnlyLast = $request->input('is_only_last', false);

        //DATE
        $dayStartDate = 1;
        $dayEndDate = 9999999999;

        if ($date1 != '' && $date2 != '') {
            $dayStartDate = intval(DateTime::createFromFormat('U', min($date1, $date2))->setTime(0, 0, 0)->format('U'));
            $dayEndDate = intval(DateTime::createFromFormat('U', max($date1, $date2))->setTime(23, 59, 59)->format('U'));
        }


        $firstLogs = DB::table('logs')
            ->select(DB::raw('MIN(created_at) as date, title, id'))
            ->groupBy('title');

        $maxRevs = DB::table('docs')
            ->select(DB::raw('MAX(revision),  id'))
            ->groupBy('code_1');

        $query = DB::table('docs');

        if ($isOnlyLast) {
            $query->joinSub($maxRevs, 'maxRevs', function ($join) {
                $join->on('docs.id', '=', 'maxRevs.id');
            });
        }

        $query->joinSub($firstLogs, 'firstLogs', function ($join) {
            $join->on('docs.transmittal', '=', 'firstLogs.title');
        });

        $query->join('titles', function ($join) {
            $join->on('docs.transmittal', '=', 'titles.id');
        });

        $query->select(
            'docs.id',
            'docs.code_1',
            'docs.code_2',
            'docs.revision',
            'docs.class',
            'docs.transmittal as transmittal_id',
            'docs.title_en',
            'docs.title_ru',
            'titles.name as transmittal',
            'firstLogs.date as date',
            'firstLogs.id as log_id'
        );

        foreach ($parameters as $key => $value) {
            if ($value != '') {
                $query->where($key, 'like', '%' . $value . '%');
            }
        }

        if ($transmittalName != '') {
            $query->where('titles.name', 'like', '%' . $transmittalName . '%');
        }

        $query->whereBetween('firstLogs.date', [$dayStartDate, $dayEndDate]);

//        DB::enableQueryLog();

        $docs = $query->get();

        // Ищем файлы

        foreach ($docs as $doc) {
            $files = UploadedFile::where('log', $doc->log_id)
                ->where('original_name', 'like', '%' . $doc->code_1 . '%')
                ->get();

            $doc->file_id = null;

            foreach ($files as $file) {
                if (preg_match(SettingsController::take('DOCS_REG_EXP_FOR_PDF_FILE'), $file->original_name)) {
                    $doc->file_id = $file->id;
                    break;
                }
            }
        }

        return Feedback::getFeedback(0, [
            'items' => $docs->toArray(),
//            'query' => DB::getQueryLog(),
//            'start' => $dayStartDate,
//            'end' => $dayEndDate
        ]);

    }

    public function getListOfTransmittal(Request $request)
    {

        $transmittal_name = trim(Input::get('transmittal', ''));

        $transmittal = Title::where('name', $transmittal_name)->first();

        if (is_null($transmittal)) {
            return Feedback::getFeedback(402);
        }

        $items = Doc::where('transmittal', $transmittal->id)->get();

        return Feedback::getFeedback(0, [
            'items' => $items->toArray()
        ]);

    }

    public function addNewDocumentToTransmittal(Request $request)
    {

        $transmittal_name = trim(Input::get('transmittal', ''));

        $transmittal = Title::where('name', $transmittal_name)->first();

        if (is_null($transmittal)) {
            return Feedback::getFeedback(402);
        }

        try {

            $doc = new Doc;

            $doc->code_1 = '???';
            $doc->revision = '???';
            $doc->transmittal = $transmittal->id;

            $doc->save();

        } catch (Exception $e) {

            return Feedback::getFeedback(1012, [
                'exception' => $e

            ]);

        }

        return Feedback::getFeedback(0);

    }

    public function saveListOfTransmittal(Request $request)
    {

        if (!Input::has('items')) {
            return Feedback::getFeedback(1001);
        }

        foreach ($request->input('items') as $item) {

            if (!array_key_exists('id', $item)) {
                return Feedback::getFeedback(1008);
            }

            if (!array_key_exists('code_1', $item)) {
                return Feedback::getFeedback(1002);
            }

            if ($item['code_1'] == '') {
                return Feedback::getFeedback(1009);
            }

            if (!array_key_exists('code_2', $item)) {
                return Feedback::getFeedback(1003);
            }

            if (!array_key_exists('revision', $item)) {
                return Feedback::getFeedback(1004);
            }
            if ($item['revision'] == '') {
                return Feedback::getFeedback(1010);
            }

            if (!array_key_exists('class', $item)) {
                return Feedback::getFeedback(1005);
            }

            if (!array_key_exists('title_ru', $item)) {
                return Feedback::getFeedback(1006);
            }

            if (!array_key_exists('title_en', $item)) {
                return Feedback::getFeedback(1007);
            }

            $doc = Doc::find($item['id']);

            if (is_null($doc)) {
                return Feedback::getFeedback(1011);
            }

            $doc->code_1 = $item['code_1'];
            $doc->code_2 = $item['code_2'];
            $doc->revision = $item['revision'];
            $doc->class = $item['class'];
            $doc->title_en = $item['title_en'];
            $doc->title_ru = $item['title_ru'];

            $doc->save();

        }

        return Feedback::getFeedback(0);

    }

    public function deleteDocumentFromTransmittal(Request $request)
    {

        if (!Input::has('doc_id')) {
            return Feedback::getFeedback(1008);
        }

        return (Doc::destroy($request->input('doc_id'))) ? Feedback::getFeedback(0) : Feedback::getFeedback(1013);

    }

    public function upload(Request $request)
    {

        $transmittal_name = trim(Input::get('transmittal', ''));
        $transmittal = Title::where('name', $transmittal_name)->first();

        if (is_null($transmittal)) {
            return Feedback::getFeedback(402);
        }

        if (!$request->hasFile('log_file')) {
            return Feedback::getFeedback(601);
        };

        if (!$request->file('log_file')->isValid()) {
            return Feedback::getFeedback(602);
        }

        $originalNameOfFile = $request->file('log_file')->getClientOriginalName();

        if (!$this->validateNameOfNewFile($originalNameOfFile)) {
            return Feedback::getFeedback(609);
        }

        try {

            $path = Storage::putFile(
                'log_file_storage' . DIRECTORY_SEPARATOR . 'TEMPORARY_FILES',
                $request->file('log_file')
            );

        } catch (QueryException $e) {
            return Feedback::getFeedback(607);
        }


        if ($path === false) {
            return Feedback::getFeedback(606);
        }

        // Читаем JSON файл

        $list = json_decode($this->removeUtf8ByteOrderMark(file_get_contents(storage_path("app/" . $path))), true);

        Storage::delete($path);

        if (is_null($list)) {
            return Feedback::getFeedback(1014);
        }

        try {

            foreach ($list['DOCS'] as $item) {

                $doc = new Doc;

                $doc->code_1 = $item['CODE_1'];
                $doc->code_2 = $item['CODE_2'];
                $doc->revision = $item['REVISION'];
                $doc->class = $item['CLASS'];
                $doc->transmittal = $transmittal->id;
                $doc->title_en = $item['TITLE_EN'];
                $doc->title_ru = $item['TITLE_RU'];

                $doc->save();
            }

            unset($list['DOCS']);

            $log = Log::where('title', $transmittal->id)->first();

            if (is_null($log)) {
                return Feedback::getFeedback(1015);
            }

            $log->what = print_r($list, true);
            $log->save();

        } catch (Exception $e) {
            return Feedback::getFeedback(1014);
        }


        return Feedback::getFeedback(0);
    }

    private function validateNameOfNewFile($fileNameWithExtension)
    {
        $regExpForNewFile = SettingsController::take('DOCS_REG_EXP_FOR_LIST_FILE');
        return (preg_match($regExpForNewFile, $fileNameWithExtension) === 1);
    }

    private function removeUtf8ByteOrderMark($text)
    {
        $bom = pack('H*', 'EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }

}
