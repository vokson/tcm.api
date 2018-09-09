<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\ApiUser;
use Illuminate\Support\Facades\Input;
use App\Log;

class ApiCheckRole
{
    private $permissions;

    function __construct()
    {

        $engineer = [
            'api/auth/change_password',
            "api/logs/get",
            "api/logs/set",
            "api/logs/delete",
            "api/titles/get",
            "api/users/get",
            "api/statuses/get",
            "api/logs/file/upload",
            "api/logs/file/download",
            "api/logs/file/get",
            "api/logs/file/delete",
        ];

        $group_leader = [];

        $pm = [
            "api/titles/set",
            "api/titles/delete",
        ];

        $admin = [
            "api/settings/get",
            "api/settings/set",
            "api/statuses/set",
            "api/statuses/add",
            "api/statuses/delete",
            "api/users/set",
            "api/users/set/default/password",
            "api/users/delete",
            'api/service/database/backup'
        ];

        $this->permissions = [
            "engineer" => array_merge($engineer),
            "group_leader" => array_merge($engineer, $group_leader),
            "pm" => array_merge($engineer, $group_leader, $pm),
            "admin" => array_merge($engineer, $group_leader, $pm, $admin)
        ];
    }

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
        $permittedUrls = $this->permissions[$role];

        if (!in_array($request->path(), $permittedUrls)) {
            return Feedback::getFeedback(104);
        }

        // Ограничиваем редактирование записей Log для не собственников записей
        // в случае, если role = engineer
        if (
            $role == 'engineer' &&
            Input::has('id') &&
            (
                $request->path() == "api/logs/set" ||
                $request->path() == "api/logs/delete" ||
                $request->path() == "api/logs/file/upload" ||
                $request->path() == "api/logs/file/delete"
            )
        ) {
            $log = Log::find(Input::get('id'));

            if (is_null($log)) {
                return Feedback::getFeedback(104);
            }

            if ($log->owner != $user->id) {
                return Feedback::getFeedback(104);
            }
        }

        return $next($request);

    }
}
