<?php

namespace App\Http\Middleware;

use App\ApiUser;
use App\Http\Controllers\FeedbackController as Feedback;
use Closure;

class CheckPermissionForRoute
{

    public function handle($request, Closure $next)
    {
        $uri = str_replace('api/', '', $request->path());

        $token = $request->input('access_token', null);

        if (is_null($token)) {
            $user = ApiUser::where('email', 'guest@mail.com')->first();
        } else {
            $user = ApiUser::where('access_token', $token)->first();
        }

        if (!$user->mayDo($uri)) {
            return Feedback::getFeedback(104);
        }

        return $next($request);
    }
}
