<?php

namespace App\Http\Middleware;

use App\ApiUser;
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\FeedbackController as Feedback;
use Closure;
use Illuminate\Support\Facades\Log as MyLog;

class CheckPermissionForRoute
{

    public function handle($request, Closure $next)
    {
//        MyLog::debug('CheckPermissionForRoute - START');

        $uri = str_replace('api/', '', $request->path());
        $user = ApiAuthController::getUserByToken($request->input('access_token'));

        if (!$user->mayDo($uri)) {
//            MyLog::debug('CheckPermissionForRoute - URL is restricted');
            return Feedback::getFeedback(104);
        }

//        MyLog::debug('CheckPermissionForRoute - FINISH');
        return $next($request);
    }
}
