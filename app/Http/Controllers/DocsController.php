<?php

namespace App\Http\Controllers;

use App\Doc;
use App\Log;
use App\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController as Feedback;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\DB;

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
        $transmittalName = $request->input('transmittal', '');
        $code_1 = $request->input('code_1', '');
        $code_2 = $request->input('code_2', '');
        $class = $request->input('class', '');
        $revision = $request->input('revision', '');
        $title_en = $request->input('title_en', '');
        $title_ru = $request->input('title_ru', '');
        $date1 = $request->input('date1', '');
        $date2 = $request->input('date2', '');
        $isOnlyLast = $request->input('is_only_last', false);

        // Ищем список трансмитталов, подходящих под запрос

//        $titlesWithTransmittal = Title::where('name', 'regexp', '"' . trim(SettingsController::take('TRANSMITTAL_REG_EXP'), '/') . '"')->get();

        $query = DB::table('titles');

        if ($transmittalName != '') {
            $query->where('name', 'like', '%' . $transmittalName . '%');
        }

        $titlesWithTransmittal = $query->get()->toArray();


//        return Feedback::getFeedback(0, [
//            'trans' => $titlesWithTransmittal
//        ]);

        $listOfTransmittalIds = [];
        $transmittalIssuanceDate = [];
        $transmittalName = [];

        // Составляем список ID трансмитталов и список дат их выпуска, имен
        // регулярное выражение имени транмиттала

        foreach ($titlesWithTransmittal as $item) {

            if (preg_match(SettingsController::take('TRANSMITTAL_REG_EXP'), $item->name)) {

                $listOfTransmittalIds[] = $item->id;

                $firstLogForTransmittal = Log::where('title', $item->id)->first();

                if (!is_null($firstLogForTransmittal)) {
                    $transmittalIssuanceDate[$item->id] = $firstLogForTransmittal->created_at;
                    $transmittalName[$item->id] = $item->name;
                }
            }

        }

        // Ищем записи

        $docs = DB::table('docs')
            ->select(
                'docs.id',
                'docs.code_1',
                'docs.code_2',
                'docs.revision',
                'docs.class',
                'docs.transmittal as transmittal_id',
                'docs.title_en',
                'docs.title_ru'
            )
            ->whereIn('transmittal_id', $listOfTransmittalIds)
            ->where('code_1', 'like', '%' . $code_1 . '%')
            ->where('code_2', 'like', '%' . $code_2 . '%')
            ->where('class', 'like', '%' . $class . '%')
            ->where('revision', 'like', '%' . $revision . '%')
            ->where('title_en', 'like', '%' . $title_en . '%')
            ->where('title_ru', 'like', '%' . $title_ru . '%')
            ->get();

//        return Feedback::getFeedback(0, [
//            'items' => $docs,
//            'name' => $transmittalName,
//            'date' => $transmittalIssuanceDate
//        ]);

        // Добавляем имена и даты от трансмитталов
        foreach ($docs->toArray() as $item) {
            $item->transmittal = $transmittalName[$item->transmittal_id];
            $item->date = $transmittalIssuanceDate[$item->transmittal_id];
        }

        return Feedback::getFeedback(0, [
            'items' => $docs
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

//        return Feedback::getFeedback(0, [
//            'list' => $list,
//            'error' => json_last_error_msg(),
//            'json1' => $s1,
//            'json2' => $s2,
//            'strcmp' => strcmp($s1, $s2),
//            'symbol_1' => substr($s1, 0,10),
//            'symbol_2' => substr($s2, 0,10)
//        ]);

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

//            $s = '';
//            $s .=
//
//            $log->what = '<p>' . $list['TRANSMITTAL'] . '</p>';
//            $log->what = '<p>' . $list['PURPOSE'] . '</p>';
//            $log->what = '<p>' . $list['DATE'] . '</p>';
//            $log->what = '<p>' . $list['SUMMARY'] . '</p>';
//            $log->what = '<p>' . $list['SUMMARY'] . '</p>';
//            $log->what = '<p>' . $list['SUMMARY'] . '</p>';


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
