<?php

namespace App\Http\Middleware;

use App\UploadedFile;
use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\ApiUser;
use Illuminate\Support\Facades\Input;
use App\Log;

class ApiCheckLogFileEditPermission
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
        $role = $user->role;

        // Ограничиваем загрузку и удаление файлов Log для не собственников записей в случае, если role = engineer
        if ($role == 'engineer') {

            if (Input::has('log_id')) { // если загрузка файла
                $log = Log::find(Input::get('log_id'));

            } else { // если удаление файла
                $file = UploadedFile::find(Input::get('id'));
                $log = Log::find($file->log);
            }

            if (is_null($log)) {
                return Feedback::getFeedback(104, [
                    'uin' => Input::get('uin', '')
                ]);
            }

            if ($log->owner != $user->id) {
                return Feedback::getFeedback(104, [
                    'uin' => Input::get('uin', '')
                ]);
            }


        }

        return $next($request);
    }
}
