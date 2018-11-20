<?php

namespace App\Http\Middleware;

use App\UploadedFile;
use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\ApiUser;
use Illuminate\Support\Facades\Input;
use App\Log;
use App\Title;

class ApiCheckLogFileEditRegExpPermission
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

        if (Input::has('log_id')) { // если загрузка файла
            $log = Log::find(Input::get('log_id'));

        } else { // если удаление файла
            $file = UploadedFile::find(Input::get('id'));
            $log = Log::find($file->log);
        }

        $title = Title::find($log->title);
        $title_name = $title->name;
        $result = preg_match($reg_exp, $title_name);

        //        return $reg_exp. " IN ".$title_name . " = " . $result;

        if ($result != 1) {
            return Feedback::getFeedback(106);
        }


        return $next($request);
    }
}
