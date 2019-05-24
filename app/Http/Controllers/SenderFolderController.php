<?php

namespace App\Http\Controllers;

use App\SenderFile;
use App\SenderFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Mail\SenderCreateFolderNotification;
use Illuminate\Support\Facades\Mail;


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
            ->select(['id', 'name', 'owner', 'is_ready', 'created_at as date'])
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
            if ((Storage::delete($file->server_name) === false) || ($file->delete() === false)) {
                return Feedback::getFeedback(603);
            }
        }

        return (SenderFolder::destroy($folder_id)) ? Feedback::getFeedback(0) : Feedback::getFeedback(901);
    }

    function switch (Request $request)
    {
        $id = intval(Input::get('id', 0));
        $folder = SenderFolder::find($id);

        if (is_null($folder)) {
            return Feedback::getFeedback(901);
        }

        $folder->is_ready = ($folder->is_ready == 1) ? 0 : 1;
        $folder->save();

        Mail::to('noskov_as@niik.ru')
            ->send(new SenderCreateFolderNotification($folder));
        
        return Feedback::getFeedback(0);
    }

    function count()
    {
        return Feedback::getFeedback(0, [
            'count' => SenderFolder::all()->count()
        ]);
    }
}
