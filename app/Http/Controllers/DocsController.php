<?php

namespace App\Http\Controllers;

use App\Doc;
use App\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController as Feedback;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\SettingsController;

class DocsController extends Controller
{
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

        $list = json_decode(file_get_contents(storage_path("app/" . $path)));

//        if (is_null($list)) {
//            return Feedback::getFeedback(1014);
//        }

        return Feedback::getFeedback(0,[
            'list' => $list,
            'error' => json_last_error_msg(),
            'json' => file_get_contents(storage_path("app/" . $path))
        ]);

        try {

            foreach ($list['DOCS'] as $item) {

                $doc = new Doc;

                $doc->code_1 = $item->code_1;
                $doc->code_2 = $item->code_2;
                $doc->revision = $item->revision;
                $doc->class = $item->class;
                $doc->transmittal = $transmittal->id;
                $doc->title_en = $item->title_en;
                $doc->title_ru = $item->title_ru;

                $doc->save();
            }

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


}
