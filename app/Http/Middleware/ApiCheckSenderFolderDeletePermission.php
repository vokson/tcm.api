<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\ApiUser;
use Illuminate\Support\Facades\Input;
use App\Check;

class ApiCheckSenderFolderDeletePermission
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
        // Ограничиваем удаление файлов Checker для не собственников файлов, а также если запись не последняя

        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();
        $role = $user->role;

        // Ограничиваем удаление файлов Sender для всех, кроме ГИП, администратор
        if ($role == 'pm' || $role == 'admin') {
            return $next($request);
        } else {
            return Feedback::getFeedback(104);
        }

    }
}
