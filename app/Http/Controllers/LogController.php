<?php

namespace App\Http\Controllers;

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

            $token = $request->input('access_token');
            $user = ApiUser::where('access_token', $token)->first();

            $log->owner = $user->id;
            $log->is_new = true;

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

        if (UploadedFile::where('log', '=', $log->id)->exists()) {

            return Feedback::getFeedback(308);

        } else {
            $log->delete();
        }

        return Feedback::getFeedback(0);
    }

    public function get(Request $request)
    {

        $to = trim(Input::get('to', ''));
        $from = trim(Input::get('from', ''));
        $title = trim(Input::get('title', ''));
        $what = trim(Input::get('what', ''));
        $timestamp = trim(Input::get('date', ''));
        $isOnlyLast = trim(Input::get('is_only_last', false));
        $isNewMessageSearch = Input::has('is_new');
        $is_new = (bool)Input::get('is_new', false);

        //DATE
        $dayStartDate = 1;
        $dayEndDate = 9999999999;

        if ($timestamp != "") {
            $dayStartDate = DateTime::createFromFormat('U', $timestamp)->setTime(0, 0, 0)->format('U');
            $dayEndDate = DateTime::createFromFormat('U', $timestamp)->setTime(23, 59, 59)->format('U');
        }


        [$idUsersTo, $idNamesUsersTo] = $this->getNamesUsersTo($to); // TO
        [$idUsersFrom, $idNamesUsersFrom] =  $this->getNamesUsersFrom($from);  //FROM
        [$idTitles, $idNamesTitles] = $this->getNamesTitles($title); //TITLE


        $query = DB::table('logs')
            ->whereBetween('created_at', [$dayStartDate, $dayEndDate])
            ->where('what', 'like', '%' . $what . '%')
            ->whereIn('to', $idUsersTo)
            ->whereIn('from', $idUsersFrom)
            ->whereIn('title', $idTitles);

        if ($isNewMessageSearch == true) {

            $token = $request->input('access_token');
            $user = ApiUser::where('access_token', $token)->first();


            $query->where('is_new', '=', $is_new)
                ->where('to', '=', $user->id);
        }

        if ($isOnlyLast == true) {
            $query
                ->select(DB::raw('"id", "is_new", "is_attachment_exist", "what", "to", "from", "title", max("created_at") as "date"'))
                ->groupBy('title');

        } else {
            $query->select(['id', 'is_new',  "is_attachment_exist", 'what', 'to', 'from', 'title', 'created_at as date']);
        }

        $items = $query
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

    private function getNamesUsersTo($to)
    {
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

        return [$idUsersTo, array_combine($idUsersTo->toArray(), $namesUsersTo->toArray())];
    }

    private function getNamesUsersFrom($from) {
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

        return [$idUsersFrom, array_combine($idUsersFrom->toArray(), $namesUsersFrom->toArray())];
    }

    public function getNamesTitles($title) {

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

        return [ $idTitles, array_combine($idTitles->toArray(), $namesTitles->toArray())];
    }

    public function getLatestArticles(Request $request)
    {

        list( ,$idNamesUsersTo) = $this->getNamesUsersTo(""); // TO
        list(, $idNamesUsersFrom) =  $this->getNamesUsersFrom("");  //FROM
        list(, $idNamesTitles) = $this->getNamesTitles(""); //TITLE

        $items = DB::table('logs')
            ->select(['id', 'is_new', 'what', 'to', 'from', 'title', 'created_at as date'])
            ->take(Settings::take('COUNT_OF_NEW_LOGS'))
            ->orderBy('id', 'desc')
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
