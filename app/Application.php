<?php

declare(strict_types=1);

namespace App;

use App\Routing\Router;
use Illuminate\Http\Request;
use Laravel\Lumen\Http\Request as LumenRequest;
use Laravel\Lumen\Routing\Controller as LumenController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Application extends \Laravel\Lumen\Application
{
    /**
     * The Router instance.
     *
     * @var Router
     */
    public $router;

    public function bootstrapRouter(): void
    {
        $this->router = new Router($this);
    }

    /**
     * Dispatch the incoming request.
     *
     * @param null|\Symfony\Component\HttpFoundation\Request $request
     */
    public function dispatch($request = null)
    {
        [$method, $cmd] = $this->parseIncomingRequest($request);

        try {
            $this->boot();

            return $this->sendThroughPipeline($this->middleware, function ($request) use ($method, $cmd) {
                $this->instance(Request::class, $request);

                if (isset($this->router->getRoutes()[$key = $method.$cmd])) {
                    return $this->handleFoundRoute([true, $this->router->getRoutes()[$method.$cmd]['action'], []]);
                }

                return $this->handleDispatcherResponse(
                    $this->createDispatcher()->dispatch($method, $cmd)
                );
            });
        } catch (Throwable $e) {
            return $this->prepareResponse($this->sendExceptionToHandler($e));
        }
    }

    /**
     * Parse the incoming request and return the method and path info.
     *
     * @param null|\Symfony\Component\HttpFoundation\Request $request
     *
     * @return string[]
     */
    protected function parseIncomingRequest($request): array
    {
        if (!$request) {
            $request = LumenRequest::capture();
        }

        $this->instance(Request::class, $this->prepareRequest($request));

        return [
            $request->getMethod(),
            '/'.trim($request->getPathInfo(), '/').'?cmd='.trim($request->query('cmd', ''), '/'),
        ];
    }

    /**
     * Call a controller based route.
     *
     * @param array $routeInfo
     */
    protected function callControllerAction($routeInfo)
    {
        [$controller, $method] = $routeInfo[1]['uses'];

        if (!method_exists($instance = $this->make($controller), $method)) {
            throw new NotFoundHttpException();
        }

        if ($instance instanceof LumenController) {
            return $this->callLumenController($instance, $method, $routeInfo);
        }

        return $this->callControllerCallable(
            [$instance, $method],
            $routeInfo[2]
        );
    }
}
