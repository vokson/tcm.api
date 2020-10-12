<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ApiAuthController;
use Closure;
use App\Http\Controllers\FeedbackController As Feedback;

class ApiCheckSenderFolderSwitchPermission
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

        // Ограничиваем удаление файлов Sender
        if ($user->mayDo('SWITCH_STATUS_OF_SENDER_FOLDER')) {
            return $next($request);
        }

        return Feedback::getFeedback(104);
    }
}
