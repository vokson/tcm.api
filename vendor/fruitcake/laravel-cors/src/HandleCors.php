<?php

namespace Fruitcake\Cors;

use Closure;
use Asm89\Stack\CorsService;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Log as MyLog;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /** @var CorsService $cors */
    protected $cors;

    /** @var \Illuminate\Contracts\Container\Container $container */
    protected $container;

    public function __construct(CorsService $cors, Container $container)
    {
        $this->cors = $cors;
        $this->container = $container;
    }

    /**
     * Handle an incoming request. Based on Asm89\Stack\Cors by asm89
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        MyLog::debug('HandleCors - START');
        MyLog::debug('HandleCors - METHOD = ' . $request->method());


        MyLog::debug($request);
        // Check if we're dealing with CORS and if we should handle it
        if (! $this->shouldRun($request)) {
            MyLog::debug('HandleCors - NEXT 1');
            return $next($request);
        }

        MyLog::debug('HandleCors - NEXT 2');

        // For Preflight, return the Preflight response
        if ($this->cors->isPreflightRequest($request)) {
            MyLog::debug('HandleCors - NEXT 3');
            $response = $this->cors->handlePreflightRequest($request);

            $this->cors->varyHeader($response, 'Access-Control-Request-Method');

            return $response;
        }

        MyLog::debug('HandleCors - NEXT 4');

        // Add the headers on the Request Handled event as fallback in case of exceptions
        if (class_exists(RequestHandled::class) && $this->container->bound('events')) {
            MyLog::debug('HandleCors - NEXT 5');
            $this->container->make('events')->listen(RequestHandled::class, function (RequestHandled $event) {
                $this->addHeaders($event->request, $event->response);
            });
        }

        MyLog::debug('HandleCors - NEXT 6');

        // Handle the request
        $response = $next($request);

        MyLog::debug('HandleCors - NEXT 7');

        if ($request->getMethod() === 'OPTIONS') {
            MyLog::debug('HandleCors - NEXT 8');
            $this->cors->varyHeader($response, 'Access-Control-Request-Method');
        }
        MyLog::debug('HandleCors - NEXT 9');

        return $this->addHeaders($request, $response);
    }

    /**
     * Add the headers to the Response, if they don't exist yet.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    protected function addHeaders(Request $request, Response $response): Response
    {
        if (! $response->headers->has('Access-Control-Allow-Origin')) {
            // Add the CORS headers to the Response
            $response = $this->cors->addActualRequestHeaders($response, $request);
        }

        return $response;
    }

    /**
     * Determine if the request has a URI that should pass through the CORS flow.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldRun(Request $request): bool
    {
        return $this->isMatchingPath($request);
    }

    /**
     * The the path from the config, to see if the CORS Service should run
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isMatchingPath(Request $request): bool
    {
        // Get the paths from the config or the middleware
        $paths = $this->container['config']->get('cors.paths', []);
//        MyLog::debug('CONFIG_PATH = ' . __DIR__ . '/../config/cors.php');


//        MyLog::debug($paths);

        foreach ($paths as $path) {
//            MyLog::debug($path);
            if ($path !== '/') {
                $path = trim($path, '/');
            }

//            MyLog::debug(
//                '$request->fullUrlIs($path) || $request->is($path) = '.
//                $request->fullUrlIs($path) . ' || ' . $request->is($path)
//            );

            if ($request->fullUrlIs($path) || $request->is($path)) {
                return true;
            }
        }

        return false;
    }
}
