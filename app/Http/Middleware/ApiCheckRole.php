<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\ApiUser;

class ApiCheckRole
{
    private $permissions;

    function __construct() {

        $engineer = [
            "api/test_engineer"
        ];

        $pm = [
            "api/test_pm"
        ];

        $admin = [
            "api/test_admin"
        ];

        $this->permissions = [
            "engineer" => array_merge( $engineer),
            "pm" => array_merge( $engineer, $pm),
            "admin" => array_merge( $engineer, $pm, $admin)
        ];
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();
        $role = $user->role;
        $permittedUrls = $this->permissions[$role];

        if (! in_array($request->path(), $permittedUrls)) {
            return Feedback::getFeedback(104);
        }

        return $next($request);

    }
}
