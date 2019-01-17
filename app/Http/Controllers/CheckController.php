<?php

namespace App\Http\Controllers;

use App\Check;
use App\CheckedFile;
use App\UploadedFile;
use Illuminate\Http\Request;
use App\ApiUser;
use App\Log;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController as Feedback;
use App\Title;
use Illuminate\Support\Facades\DB;
use DateTime;
use App\Http\Controllers\SettingsController as Settings;

class CheckController extends Controller
{

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

        if (self::validateNameOfNewFile($uploadedFile->original_name)) {
            return self::addRecordOfNewFile($path_parts['filename'], $file_id, $owner_id);
        } else {
            return self::addRecordOfCheckedFile($path_parts['filename'], $file_id, $owner_id);
        }

    }

    public static function addRecordOfNewFile($nameOfFileWithoutExtension, $file_id, $owner_id)
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
            $record->status = 0;
            $record->mistake_count = 0;
            $record->owner = $owner_id;
            $record->save();
        }

        return 0;

    }

    public static function addRecordOfCheckedFile($nameOfFileWithoutExtension, $file_id, $owner_id)
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
            $record->status = $status;
            $record->mistake_count = $countOfMistakes;
            $record->owner = $owner_id;
            $record->save();
        }

        return 0;

    }
}
