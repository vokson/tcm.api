<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\ApiUser;
use Illuminate\Support\Facades\Input;
use App\Log;
use App\Title;

class ApiCheckLogEditRegExpPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();
        $reg_exp = $user->permission_expression;

        if (Input::has('id')) {
            $log_id = $request->input('id');
            $log = Log::find($log_id);
            $title = Title::find($log->title);
        } else {
            $title =  Title::find($request->input('title'));
        }

        $title_name = $title->name;
        $result = preg_match($reg_exp, $title_name);

//        return $reg_exp. " IN ".$title_name . " = " . $result;

        if ($result != 1) {
            return Feedback::getFeedback(106);
        }

        return $next($request);
    }
}
