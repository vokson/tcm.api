<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
//            'throttle:60,1',
//            'bindings',
            'cors',
            'route.permission'
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,

        'auth.api.token' => \App\Http\Middleware\ApiAccessToken::class,
        'auth.api.roles' => \App\Http\Middleware\ApiCheckRole::class,
        'cors' => \Barryvdh\Cors\HandleCors::class,
        'auth.log.edit' => \App\Http\Middleware\ApiCheckLogEditPermission::class,
        'reg_exp.log.edit' => \App\Http\Middleware\ApiCheckLogEditRegExpPermission::class,
        'auth.log.file.edit' => \App\Http\Middleware\ApiCheckLogFileEditPermission::class,
        'reg_exp.log.file.edit' => \App\Http\Middleware\ApiCheckLogFileEditRegExpPermission::class,
        'auth.log.new.message' => \App\Http\Middleware\ApiCheckLogMarkNewMessagePermission::class,
        'auth.checker.file.delete' => \App\Http\Middleware\ApiCheckCheckerFileDeletePermission::class,
        'auth.sender.folder.delete' => \App\Http\Middleware\ApiCheckSenderFolderDeletePermission::class,
        'auth.sender.folder.switch' => \App\Http\Middleware\ApiCheckSenderFolderSwitchPermission::class,
        'log.transmittal.record.create' => \App\Http\Middleware\ApiCheckCountOfLogsForTransmittal::class,

        'route.permission' => \App\Http\Middleware\CheckPermissionForRoute::class,
    ];
}
