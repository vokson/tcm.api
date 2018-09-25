<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Log;
use App\Http\Controllers\FeedbackController as Feedback;

class LogNewMessageController extends Controller
{
    public function set(Request $request)
    {

        $id = Input::get('id', null);

        if (!Log::where('id', '=', $id)->exists()) {
            return Feedback::getFeedback(305);
        }

        $log = Log::find($id);
        $log->is_new = !$log->is_new;
        $log->save();

        return Feedback::getFeedback(0);
    }
}
