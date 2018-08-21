<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ApiUser;
use App\Log;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController as Feedback;
use App\Title;
use Illuminate\Support\Facades\DB;
use DateTime;

class LogController extends Controller
{
    public function set(Request $request)
    {
        if (!ApiUser::where('id', '=', Input::get('to'))->exists()) {
            return Feedback::getFeedback(301);
        }
        if (!ApiUser::where('id', '=', Input::get('from'))->exists()) {
            return Feedback::getFeedback(302);
        }
        if (!Title::where('id', '=', Input::get('title'))->exists()) {
            return Feedback::getFeedback(303);
        }
        if (!Input::has('what')) {
            return Feedback::getFeedback(304);
        }
        if (!Input::has('date')) {
            return Feedback::getFeedback(306);
        }


        $id = null;
        if (Input::has('id')) {

            if (!Log::where('id', '=', Input::get('id'))->exists()) {
                return Feedback::getFeedback(305);
            } else {
                $id = $request->input('id');
            }
        }

        if (is_null($id)) {
            $log = new Log;
        } else {
            $log = Log::find($id);
        }

        $log->to = $request->input('to');
        $log->from = $request->input('from');
        $log->title = $request->input('title');
        $log->created_at = $request->input('date');

        $log->what = trim($request->input('what'));
        if ($log->what == "") {
            return Feedback::getFeedback(304);
        }

        $log->save();

        return Feedback::getFeedback(0);
    }

    public function delete(Request $request)
    {

        if (!Input::has('id')) {
            return Feedback::getFeedback(305);
        }

        if (!Log::where('id', '=', Input::get('id'))->exists()) {
            return Feedback::getFeedback(305);
        }

        $log = Log::find($request->input('id'));
        $log->delete();

        return Feedback::getFeedback(0);
    }

    public function get(Request $request)
    {

        $to = "";
        if (Input::has('to')) $to = trim($request->input('to'));

        $from = "";
        if (Input::has('from')) $from = trim($request->input('from'));

        $title = "";
        if (Input::has('title')) $title = trim($request->input('title'));

        $what = "";
        if (Input::has('what')) $what = trim($request->input('what'));

        $timestamp = "";
        if (Input::has('date')) $timestamp = trim($request->input('date'));


        //DATE
        $dayStartDate = 1;
        $dayEndDate = 9999999999;

        if ($timestamp != "") {
            $dayStartDate = DateTime::createFromFormat('U', $timestamp)->setTime(0, 0, 0)->format('U');
            $dayEndDate = DateTime::createFromFormat('U', $timestamp)->setTime(23, 59, 59)->format('U');
        }

        // TO

        $usersTo = DB::table('api_users')
            ->where('surname', 'like', '%' . $to . '%')
            ->select('id', 'name', 'surname')
            ->get();

        $idUsersTo = $usersTo->map(function ($item) {
            return $item->id;
        });

        $namesUsersTo = $usersTo->map(function ($item) {
            return $item->surname . ' ' . $item->name;
        });

        $idNamesUsersTo = array_combine($idUsersTo->toArray(), $namesUsersTo->toArray());

        //FROM

        $usersFrom = DB::table('api_users')
            ->where('surname', 'like', '%' . $from . '%')
            ->select('id', 'name', 'surname')
            ->get();

        $idUsersFrom = $usersFrom->map(function ($item) {
            return $item->id;
        });

        $namesUsersFrom = $usersFrom->map(function ($item) {
            return $item->surname . ' ' . $item->name;
        });

        $idNamesUsersFrom = array_combine($idUsersFrom->toArray(), $namesUsersFrom->toArray());

        //TITLE

        $titles = DB::table('titles')
            ->where('name', 'like', '%' . $title . '%')
            ->select('id', 'name')
            ->get();

        $idTitles = $titles->map(function ($item) {
            return $item->id;
        });

        $namesTitles = $titles->map(function ($item) {
            return $item->name;
        });

        $idNamesTitles = array_combine($idTitles->toArray(), $namesTitles->toArray());


        $items = DB::table('logs')
            ->whereBetween('created_at', [$dayStartDate, $dayEndDate])
            ->where('what', 'like', '%' . $what . '%')
            ->whereIn('to', $idUsersTo)
            ->whereIn('from', $idUsersFrom)
            ->whereIn('title', $idTitles)
            ->select(['id', 'what', 'to', 'from', 'title', 'created_at as date'])
            ->orderBy('date', 'asc')
            ->get();


        // Подменяем id на значения полей из других таблиц

        $items->transform(function ($item, $key) use ($idNamesUsersTo, $idNamesUsersFrom, $idNamesTitles) {
            $item->to = $idNamesUsersTo[$item->to];
            $item->from = $idNamesUsersFrom[$item->from];
            $item->title = $idNamesTitles[$item->title];
            return $item;
        });


        return Feedback::getFeedback(0, [
            'items' => $items->toArray(),
        ]);

    }
}
