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
            "api/titles/history/get",
            "api/users/get",
            "api/statuses/get",
            "api/logs/file/upload",
            "api/logs/file/download",
            "api/logs/file/download/all",
            "api/logs/file/get",
            "api/logs/file/delete",
            "api/logs/new/message/switch",
            "api/logs/new/message/count",
            "api/logs/get/last/articles",
            "api/charts/logs/created/get",
            "api/charts/titles/created/get",
            "api/charts/titles/status/get",
            "api/charts/storage/get",
            "api/checker/get",
            "api/checker/delete",
            "api/checker/file/upload",
            "api/checker/file/download",
            "api/checker/file/download/all",
            "api/sender/folder/add",
            "api/sender/folder/get",
            "api/sender/folder/delete",
            "api/sender/folder/count",
            "api/sender/file/upload",
            "api/sender/file/get",
            "api/sender/file/delete",
            "api/sender/file/download",
            "api/sender/file/download/all",
        ];

        $group_leader = [
            "api/titles/set",
            "api/titles/delete",
        ];

        $pm = [

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
            'api/service/database/backup',
            'api/service/database/update/attachments',
            'api/logs/clean/files/without/articles'
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
