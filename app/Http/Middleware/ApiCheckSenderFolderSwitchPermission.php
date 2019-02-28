<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\ApiUser;

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

        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();
        $role = $user->role;

        // Ограничиваем смену статуса Sender Folder
        // в случае, если role = engineer

        if ($role == 'engineer') {
            return Feedback::getFeedback(104);
        }

        return $next($request);
    }
}
