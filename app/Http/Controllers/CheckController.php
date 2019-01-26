<?php

namespace App\Http\Controllers;

use App\Check;
use App\CheckedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController as Feedback;
use Illuminate\Support\Facades\DB;
use DateTime;

class CheckController extends Controller
{

    public function get(Request $request)
    {

        $status_yes = Input::get('status_yes', true);
        $status_no = Input::get('status_no', true);
        $status_question = Input::get('status_question', true);

        $statuses = [];
        if ($status_yes) $statuses[] = 1;
        if ($status_question) $statuses[] = 0;
        if ($status_no) $statuses[] = -1;


        $owner = trim(Input::get('owner', ''));
        $filename = trim(Input::get('filename', ''));
        $extension = trim(Input::get('extension', ''));
        $mistake_count = trim(Input::get('mistake_count', ''));
        $timestamp = trim(Input::get('date', ''));
        $isOnlyLast = Input::get('is_only_last', false);

        //DATE
        $dayStartDate = 1;
        $dayEndDate = 9999999999;

        if ($timestamp != "") {
            $dayStartDate = DateTime::createFromFormat('U', $timestamp)->setTime(0, 0, 0)->format('U');
            $dayEndDate = DateTime::createFromFormat('U', $timestamp)->setTime(23, 59, 59)->format('U');
        }


        [$idUsers, $idNamesUsers] = $this->getNamesUsers($owner);

        $query = DB::table('checks')
            ->whereBetween('created_at', [$dayStartDate, $dayEndDate])
            ->where('filename', 'like', '%' . $filename . '%')
            ->where('extension', 'like', '%' . $extension . '%')
            ->where('mistake_count', 'like', '%' . $mistake_count . '%')
            ->whereIn('owner', $idUsers);

        if ($isOnlyLast == true) {
            $query
                ->select(DB::raw('"id", "file_id", "filename", "extension", "status", "mistake_count", "owner", max("created_at") as "date"'))
                ->groupBy('filename');

        } else {
            $query->select(['id', 'file_id', "filename", "extension", 'status', 'mistake_count', 'owner', 'created_at as date']);
        }


        $items = $query
            ->orderBy('filename', 'asc')
            ->orderBy('date', 'asc')
            ->get();

        // Подменяем id на значения полей из других таблиц
        $items->transform(function ($item, $key) use ($idNamesUsers) {
            $item->owner = $idNamesUsers[$item->owner];
            return $item;
        });


        // Statuses отдельно, чтобы иметь возможность отоборать статусы после выбора последних записей
        $items = $items->whereIn('status', $statuses);

        return Feedback::getFeedback(0, [
            // array_values добавлено, потому что whereIn (также как и array_filter) выдает
            // ассоциативынй массив, что в данном случае не нужно
            'items' => array_values($items->toArray()),
        ]);

    }


    private function getNamesUsers($userIdPattern)
    {
        $users = DB::table('api_users')
            ->where('surname', 'like', '%' . $userIdPattern . '%')
            ->select('id', 'name', 'surname')
            ->get();

        $idUsers = $users->map(function ($item) {
            return $item->id;
        });

        $namesUsers = $users->map(function ($item) {
            return $item->surname . ' ' . $item->name;
        });

        return [$idUsers, array_combine($idUsers->toArray(), $namesUsers->toArray())];
    }

    public static function validateNameOfNewFile($fileNameWithExtension)
    {
        $regExpForNewFile = "/^0055-TCM-NKK-\d{1}\.\d{1}\.\d{1}\.\d{2}\.\d{3}-(VK|NV|NVK|NK|PT|TM|THM|TI|VS|GSV|GSN|TS|TT|OE|AD|GP|GT|IZT|PJ|AR|AS|KJ|KM|VL|RZA|EPO|EPZ|ER|ERZ|EOE|EHZ|TH|AK|AKZ|ANK|ANV|AE|OV|OVO|OVK|ASK|ATH|SSL|OS|AUP|SS|RT|PS|SM|MG|AOV|AOVK|AES|AGSN|AMG|ANK|ANVK|APT|ATS|AVK|EG|EM| EN|EO|ES|ET){1}\d{0,3}-(OD|PL|KP|PR|CP|DT|ID|SC|RR|CJ|LT|LS|LR|OS|UO|PID|PFD|IS|SP|OL|IZ|NI|PU|TDA|TT|PO|PI){1}-\d{4}_(VD|SD|A1|B1|\d{2}){1}_(RU|EN|ER){1}(_(CRS|ACRS){1})?(_(Att|ATT)\d{1,2})?\.(dwg|DWG|pdf|PDF|doc|DOC|docx|DOCX|xls|XLS|xlsx|XLSX|zip|ZIP){1}$/";
        return (preg_match($regExpForNewFile, $fileNameWithExtension) === 1);
    }

    public static function validateNameOfCheckedFile($fileNameWithExtension)
    {
        $regExpForCheckedFile = "/^0055-TCM-NKK-\d{1}\.\d{1}\.\d{1}\.\d{2}\.\d{3}-(VK|NV|NVK|NK|PT|TM|THM|TI|VS|GSV|GSN|TS|TT|OE|AD|GP|GT|IZT|PJ|AR|AS|KJ|KM|VL|RZA|EPO|EPZ|ER|ERZ|EOE|EHZ|TH|AK|AKZ|ANK|ANV|AE|OV|OVO|OVK|ASK|ATH|SSL|OS|AUP|SS|RT|PS|SM|MG|AOV|AOVK|AES|AGSN|AMG|ANK|ANVK|APT|ATS|AVK|EG|EM| EN|EO|ES|ET){1}\d{0,3}-(OD|PL|KP|PR|CP|DT|ID|SC|RR|CJ|LT|LS|LR|OS|UO|PID|PFD|IS|SP|OL|IZ|NI|PU|TDA|TT|PO|PI){1}-\d{4}_(VD|SD|A1|B1|\d{2}){1}_(RU|EN|ER){1}(_(CRS|ACRS){1})?(_(Att|ATT)\d{1,2})?\[\d+\]\.(dwg|DWG|pdf|PDF|doc|DOC|docx|DOCX|xls|XLS|xlsx|XLSX|zip|ZIP){1}$/";
        return (preg_match($regExpForCheckedFile, $fileNameWithExtension) === 1);
    }

    public static function add($file_id, $owner_id)
    {
        $uploadedFile = CheckedFile::find($file_id);
        $path_parts = pathinfo($uploadedFile->original_name); // Filename without extension
        $path_parts['extension'] = strtolower($path_parts['extension']);

        if (self::validateNameOfNewFile($uploadedFile->original_name)) {
            return self::addRecordOfNewFile($path_parts['filename'], $path_parts['extension'], $file_id, $owner_id);
        } else {
            return self::addRecordOfCheckedFile($path_parts['filename'], $path_parts['extension'], $file_id, $owner_id);
        }
    }

    public static function addRecordOfNewFile($nameOfFileWithoutExtension, $extension, $file_id, $owner_id)
    {
        $record = Check::where('filename', $nameOfFileWithoutExtension)->latest()->first();

        if (!is_null($record) && $record->status == 0 && $record->owner == $owner_id) {
            // Пользователь хочет подменить файл
            if (!CheckedFileController::deleteById($record->file_id)) return 603;

            $record->file_id = $file_id;
            $record->save();
        } else {
            // Пользователь загружает файл в первый раз
            $record = new Check();
            $record->file_id = $file_id;
            $record->filename = $nameOfFileWithoutExtension;
            $record->extension = $extension;
            $record->status = 0;
            $record->mistake_count = 0;
            $record->owner = $owner_id;
            $record->save();
        }

        return 0;

    }

    public static function addRecordOfCheckedFile($nameOfFileWithoutExtension, $extension, $file_id, $owner_id)
    {
        //Разделяем имя файла
        $arr = explode('[', $nameOfFileWithoutExtension);

        $filename = $arr[0];
        $countOfMistakes = intval(substr($arr[1], 0, -1));
        $status = ($countOfMistakes === 0) ? 1 : -1;

        // Если согласовано положительно, то удаляем загруженный файл
        if ($status === 1) {
            if (!CheckedFileController::deleteById($file_id)) return 603;
        }

        $record = Check::where('filename', $filename)->latest()->first();

        if (!is_null($record) && $record->status == $status && $record->owner == $owner_id) {
            // Пользователь хочет подменить файл

            if ($status === -1) {
                if (!CheckedFileController::deleteById($record->file_id)) return 603;
                $record->file_id = $file_id;
                $record->mistake_count = $countOfMistakes;
                $record->save();
            }

        } else {
            // Пользователь загружает файл в первый раз
            $record = new Check();
            if ($status === -1) $record->file_id = $file_id;
            $record->filename = $filename;
            $record->extension = $extension;
            $record->status = $status;
            $record->mistake_count = $countOfMistakes;
            $record->owner = $owner_id;
            $record->save();
        }

        return 0;

    }

    public function delete(Request $request)
    {

        if (!Input::has('id')) {
            return Feedback::getFeedback(701);
        }

        if (!Check::where('id', '=', Input::get('id'))->exists()) {
            return Feedback::getFeedback(701);
        }

        $check = Check::find($request->input('id'));

        if (!is_null($check->file_id)) {
            if (!CheckedFileController::deleteById($check->file_id)) return 603;
        }

        $check->delete();

        return Feedback::getFeedback(0);
    }
}
