<?php

namespace App\Http\Controllers;

use App\SenderFile;
use App\SenderFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SenderFolderController extends Controller
{
    function add(Request $request)
    {

        SenderFolder::create([
            'name' => trim(Input::get('name', '')),
            'owner' => ApiAuthController::id($request)
        ]);

        return Feedback::getFeedback(0);
    }

    function get()
    {

        $items = DB::table('sender_folders')
            ->select(['id', 'name', 'owner', 'created_at as date'])
            ->get();

        // Подменяем id на значения полей из других таблиц
        $items->transform(function ($item, $key) {
            $item->owner = ApiAuthController::getSurnameAndNameOfUserById($item->owner);
            return $item;
        });

        return Feedback::getFeedback(0, [
            'items' => $items->toArray(),
        ]);
    }

    function delete(Request $request)
    {
        $folder_id = intval(Input::get('id', 0));

        $files = SenderFile::where('folder', $folder_id)->get();

        foreach ($files as $file) {
            if ( (Storage::delete($file->server_name) === false) || ($file->delete() === false)) {
                return Feedback::getFeedback(603);
            }
        }

        return (SenderFolder::destroy($folder_id)) ? Feedback::getFeedback(0) : Feedback::getFeedback(901);
    }

    function count()
    {
        return Feedback::getFeedback(0, [
            'count' => SenderFolder::all()->count()
        ]);
    }
}
