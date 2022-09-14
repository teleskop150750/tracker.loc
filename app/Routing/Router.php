<?php

declare(strict_types=1);

namespace App\Routing;

use Closure;
use Illuminate\Support\Arr;
use Laravel\Lumen\Application;

class Router extends \Laravel\Lumen\Routing\Router
{
    /**
     * The application instance.
     */
    public $app;

    /**
     * Register a set of routes with a set of shared attributes.
     */
    public function group(array $attributes, Closure $callback): void
    {
        if (isset($attributes['middleware']) && \is_string($attributes['middleware'])) {
            $attributes['middleware'] = explode('|', $attributes['middleware']);
        }

        $this->updateGroupStack($attributes);

        $callback($this);

        array_pop($this->groupStack);
    }

    /**
     * Merge the given group attributes.
     *
     * @param mixed $new
     * @param mixed $old
     */
    public function mergeGroup($new, $old): array
    {
        $new['path'] = static::formatGroupPath($new, $old);

        if (isset($new['domain'])) {
            unset($old['domain']);
        }

        if (isset($old['as'])) {
            $new['as'] = $old['as'].(isset($new['as']) ? '.'.$new['as'] : '');
        }

        return array_merge_recursive(Arr::except($old, ['path', 'as']), $new);
    }

    /**
     * {@inheritDoc}
     */
    public function addRoute($method, $uri, $action): void
    {
        $action = $this->parseAction($action);

        $attributes = ['path' => '/'];

        if ($this->hasGroupStack()) {
            $attributes = $this->mergeWithLastGroup([]);
        }

        if (isset($attributes) && \is_array($attributes)) {
            if (isset($attributes['path'])) {
                $uri = trim($attributes['path'], '/').'?cmd='.trim($uri);
            }

            $action = $this->mergeGroupAttributes($action, $attributes);
        }

        $uri = '/'.trim($uri, '/');

        if (isset($action['as'])) {
            $this->namedRoutes[$action['as']] = $uri;
        }

        if (\is_array($method)) {
            foreach ($method as $verb) {
                $this->routes[$verb.$uri] = ['method' => $verb, 'uri' => $uri, 'action' => $action];
            }
        } else {
            $this->routes[$method.$uri] = ['method' => $method, 'uri' => $uri, 'action' => $action];
        }
    }

    /**
     * Determine if the router currently has a group stack.
     */
    public function hasGroupStack(): bool
    {
        return !empty($this->groupStack);
    }

    /**
     * Update the group stack with the given attributes.
     */
    protected function updateGroupStack(array $attributes): void
    {
        if (!empty($this->groupStack)) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }

        $this->groupStack[] = $attributes;
    }

    protected static function formatGroupPath(array $new, array $old): ?string
    {
        $oldPath = $old['path'] ?? null;
        $new['path'] = $new['path'] ?? '/';

        if (isset($new['path'])) {
            return trim($oldPath ?? '', '/').'/'.trim($new['path'], '/');
        }

        return $oldPath;
    }

    /**
     * Parse the action into an array format.
     *
     * @param mixed $action
     */
    protected function parseAction($action): array|Closure
    {
        if (!\is_array($action)) {
            return [$action];
        }

        if (isset($action['middleware']) && \is_string($action['middleware'])) {
            $action['middleware'] = explode('|', $action['middleware']);
        }

        return $action;
    }

    /**
     * Merge the group attributes into the action.
     */
    protected function mergeGroupAttributes(array $action, array $attributes): array
    {
        $middleware = $attributes['middleware'] ?? null;
        $as = $attributes['as'] ?? null;

        return $this->mergeMiddlewareGroup(
            $this->mergeAsGroup($action, $as),
            $middleware
        );
    }

    /**
     * Merge the middleware group into the action.
     *
     * @param mixed      $action
     * @param null|mixed $middleware
     */
    protected function mergeMiddlewareGroup($action, $middleware = null): array
    {
        if (isset($middleware)) {
            if (isset($action['middleware'])) {
                $action['middleware'] = array_merge($middleware, $action['middleware']);
            } else {
                $action['middleware'] = $middleware;
            }
        }

        return $action;
    }

    /**
     * Merge the as group into the action.
     *
     * @param mixed      $action
     * @param null|mixed $as
     */
    protected function mergeAsGroup($action, $as = null): array
    {
        if (isset($as) && !empty($as)) {
            if (isset($action['as'])) {
                $action['as'] = $as.'.'.$action['as'];
            } else {
                $action['as'] = $as;
            }
        }

        return $action;
    }
}
