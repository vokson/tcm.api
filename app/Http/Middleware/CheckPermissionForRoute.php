<?php

namespace App\Http\Middleware;

use App\ApiUser;
use App\Http\Controllers\FeedbackController as Feedback;
use Closure;
use Illuminate\Support\Facades\Log as MyLog;

class CheckPermissionForRoute
{

    public function handle($request, Closure $next)
    {
        MyLog::debug('CheckPermissionForRoute - START');

        $uri = str_replace('api/', '', $request->path());

        $token = $request->input('access_token', null);

        if (is_null($token)) {
            $user = ApiUser::where('email', 'guest@mail.com')->first();
        } else {
            $user = ApiUser::where('access_token', $token)->first();
        }

        if (!$user->mayDo($uri)) {
            MyLog::debug('CheckPermissionForRoute - URL is restricted');
            return Feedback::getFeedback(104);
        }

        MyLog::debug('CheckPermissionForRoute - FINISH');
        return $next($request);
    }
}
