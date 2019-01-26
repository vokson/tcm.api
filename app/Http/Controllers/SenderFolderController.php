<?php

namespace App\Http\Controllers;

use App\SenderFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\DB;

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
        $isDeleted = SenderFolder::destroy(intval(Input::get('id', 0)));
        return ($isDeleted) ? Feedback::getFeedback(0) : Feedback::getFeedback(901);
    }
}
