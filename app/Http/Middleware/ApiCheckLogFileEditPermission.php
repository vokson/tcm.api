<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ApiAuthController;
use App\UploadedFile;
use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\Input;
use App\Log;
use Illuminate\Support\Facades\Log as MyLog;

class ApiCheckLogFileEditPermission
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        $token = $request->input('access_token');
//        $user = ApiUser::where('access_token', $token)->first();
//        $role = $user->role;
        MyLog::debug('ApiCheckLogFileEditPermission - START');
        $user = ApiAuthController::getUserByToken($request->input('access_token'));

        // Ограничиваем загрузку и удаление файлов Log для не собственников записей в случае
        // если роль не позволяет редактировать чужие файлы
        if ($user->mayDo('EDIT_NON_OWNED_LOG_RECORD_FILE')) {
            MyLog::debug('ApiCheckLogFileEditPermission - EDIT_NON_OWNED_LOG_RECORD_FILE = True');
            return $next($request);
        }

        MyLog::debug('ApiCheckLogFileEditPermission - EDIT_NON_OWNED_LOG_RECORD_FILE = False');

        if (Input::has('log_id')) { // если загрузка файла
            $log = Log::find(Input::get('log_id'));

        } else { // если удаление файла
            $file = UploadedFile::find(Input::get('id'));
            $log = Log::find($file->log);
        }

        MyLog::debug('ApiCheckLogFileEditPermission - log');
        MyLog::debug($log);

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

        MyLog::debug('ApiCheckLogFileEditPermission - FINISH');
        return $next($request);
    }
}
