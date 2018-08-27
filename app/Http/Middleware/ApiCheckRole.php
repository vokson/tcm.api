<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\ApiUser;

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
            "api/statuses/get"
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

        return $next($request);

    }
}
