<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\ApiUser;
use Illuminate\Support\Facades\Input;
use App\Check;

class ApiCheckCheckerFileDeletePermission
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


        if (!Input::has('id')) {
            return Feedback::getFeedback(701);
        }

        if (!Check::where('id', '=', Input::get('id'))->exists()) {
            return Feedback::getFeedback(701);
        }

        $check = Check::find($request->input('id'));
        $lastRecord = Check::where('filename', $check->filename)->latest()->first();

        if (($check->id != $lastRecord->id) || ($user->id != $check->owner)) {
            return Feedback::getFeedback(104);
        }


        return $next($request);
    }
}
