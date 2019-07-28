<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SettingsController;
use App\Log;
use App\Title;
use Closure;
use App\Http\Controllers\FeedbackController as Feedback;

class ApiCheckCountOfLogsForTransmittal
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $log_id = $request->input('id', null);

        if (!is_null($log_id)) {
            return $next($request);
        }

        $title_id = $request->input('title');
        $title_name = Title::find($title_id)->name;

        // Проверяем создана ли первая запись для трансмиттала
        // если ДА, то запрещаем создавать вторую запись

        $reg_exp = SettingsController::take('TRANSMITTAL_REG_EXP');

        if (preg_match($reg_exp, $title_name) && !is_null(Log::where('title', $title_id)->first())) {
            return Feedback::getFeedback(309);
        }

        return $next($request);
    }
}
