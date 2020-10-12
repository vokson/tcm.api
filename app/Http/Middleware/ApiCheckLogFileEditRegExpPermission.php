<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ApiAuthController;
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
        $user = ApiAuthController::getUserByToken($request->input('access_token'));

        if (Input::has('log_id')) { // если загрузка файла
            $log = Log::find(Input::get('log_id'));

        } else { // если удаление файла
            $file = UploadedFile::find(Input::get('id'));
            $log = Log::find($file->log);
        }

        $title = Title::find($log->title);
        $result = preg_match($user->permission_expression, $title->name);

        if ($result != 1) {
            return Feedback::getFeedback(106);
        }


        return $next($request);
    }
}
