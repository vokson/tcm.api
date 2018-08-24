<?php

namespace App\Http\Controllers;

use App\Status;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\DB;
use App\Title;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SettingsController;

class TitleController extends Controller
{
    public function set(Request $request)
    {
        if (!Input::has('name')) {
            return Feedback::getFeedback(402);
        }

        if (!Status::where('id', '=', Input::get('status'))->exists()) {
            return Feedback::getFeedback(403);
        }

        if (!Input::has('predecessor')) {
            return Feedback::getFeedback(404);
        }

        $id = null;
        if (Input::has('id')) {

            if (!Title::where('id', '=', Input::get('id'))->exists()) {
                return Feedback::getFeedback(401);
            } else {
                $id = $request->input('id');
            }
        }

        if (is_null($id)) {
            $title = new Title;
        } else {
            $title = Title::find($id);
        }

        $title->name = trim($request->input('name'));
        $title->status = $request->input('status');
        $title->predecessor = $request->input('predecessor');

        if ($title->name == "") {
            return Feedback::getFeedback(402);
        }

        $title->save();

        LogController::createNewLog([
            'to' => SettingsController::take('SYSTEM_USER_ID'),
            'from' => SettingsController::take('SYSTEM_USER_ID'),
            'title' => $title->id,
            'what' => 'СТАТУС => ' . Status::find($title->status)->name
        ]);

        return Feedback::getFeedback(0);
    }

    public function get(Request $request)
    {
        $name = "";
        if (Input::has('name')) $name = trim($request->input('name'));

        $status = "";
        if (Input::has('status')) $status = trim($request->input('status'));

        $predecessor = "";
        if (Input::has('predecessor')) $predecessor = trim($request->input('predecessor'));


        // STATUS

        $statuses = DB::table('statuses')
            ->where('name', 'like', '%' . $status . '%')
            ->select('id', 'name')
            ->get();

        $idStatuses = $statuses->map(function ($item) {
            return $item->id;
        });

        $namesStatuses = $statuses->map(function ($item) {
            return $item->name;
        });

        $idNamesStatuses = array_combine($idStatuses->toArray(), $namesStatuses->toArray());


        $items = DB::table('titles')
            ->where('name', 'like', '%' . $name . '%')
            ->where(function ($query) use ($predecessor) {

                $query->where('predecessor', 'like', '%' . $predecessor . '%');

                if ($predecessor == "") {
                    $query->orWhereNull('predecessor');
                }

            })
            ->whereIn('status', $idStatuses)
            ->select(['id', 'name', 'status', 'predecessor'])
            ->orderBy('name', 'asc')
            ->get();


        // Подменяем id на значения полей из других таблиц

        $items->transform(function ($item, $key) use ($idNamesStatuses) {

            $item->status = $idNamesStatuses[$item->status];
            return $item;
        });


        return Feedback::getFeedback(0, [
            'items' => $items->toArray(),
        ]);


    }

    public function delete(Request $request)
    {

        if (!Input::has('id')) {
            return Feedback::getFeedback(401);
        }

        if (!Title::where('id', '=', Input::get('id'))->exists()) {
            return Feedback::getFeedback(401);
        }

        $title = Title::find($request->input('id'));


        try {
            $title->delete();
        } catch (QueryException $e) {
            return Feedback::getFeedback(206);
        }

        return Feedback::getFeedback(0);
    }
}
