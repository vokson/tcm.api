<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ApiUser;
use App\Log;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\FeedbackController as Feedback;
use App\Title;

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

        $log = new Log;
        $log->to = $request->input('to');
        $log->from = $request->input('from');
        $log->title = $request->input('title');
        $log->what = trim($request->input('what'));

        if ($log->what == "") {
            return Feedback::getFeedback(304);
        }

        $log->save();

        return Feedback::getFeedback(0);

    }
}
