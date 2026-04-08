<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private static ?Router $instance = null;
    private array $routes = [];
    private string $prefix = '';
    private array $groupMiddlewares = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddlewares = $this->groupMiddlewares;

        if (isset($attributes['prefix'])) {
            $this->prefix = $previousPrefix . $attributes['prefix'];
        }

        if (isset($attributes['middlewares'])) {
            $this->groupMiddlewares = array_merge($previousMiddlewares, $attributes['middlewares']);
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->groupMiddlewares = $previousMiddlewares;
    }

    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $this->prefix . $path, $handler, array_merge($this->groupMiddlewares, $middlewares));
    }

    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $this->prefix . $path, $handler, array_merge($this->groupMiddlewares, $middlewares));
    }

    public function put(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $this->prefix . $path, $handler, array_merge($this->groupMiddlewares, $middlewares));
    }

    public function delete(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $this->prefix . $path, $handler, array_merge($this->groupMiddlewares, $middlewares));
    }

    public function patch(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('PATCH', $this->prefix . $path, $handler, array_merge($this->groupMiddlewares, $middlewares));
    }

    private function addRoute(string $method, string $path, $handler, array $middlewares): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch(Request $request, Response $response): void
    {
        $method = $request->getMethod();
        $path = $request->getPath();
        
        foreach ($this->routes as $route) {
            $params = [];
            if ($route['method'] === $method && $this->matchPath($route['path'], $path, $params)) {
                $request->setParams($params);
                
                // Chain of middlewares and finally the handler
                $middlewareChain = $route['middlewares'];
                $handler = $route['handler'];
                
                $this->runChain($request, $response, $middlewareChain, $handler);
                return;
            }
        }

        // No route matched - return proper 404
        $this->handleNotFound($request, $response);
    }

    private function runChain(Request $request, Response $response, array $middlewares, $handler): void
    {
        $index = 0;

        $next = function (Request $req, Response $res) use (&$index, $middlewares, $handler, &$next) {
            if ($index < count($middlewares)) {
                $middleware = $middlewares[$index++];
                $middleware->handle($req, $res, $next);
            } else {
                $this->executeHandler($req, $res, $handler);
            }
        };

        $next($request, $response);
    }

    private function executeHandler(Request $request, Response $response, $handler): void
    {
        if (is_array($handler)) {
            [$controller, $method] = $handler;
            
            if (!class_exists($controller)) {
                $this->handleError($response, "Controller not found: {$controller}", 500);
                return;
            }
            
            $controllerInstance = new $controller();
            
            if (!method_exists($controllerInstance, $method)) {
                $this->handleError($response, "Method not found: {$controller}::{$method}", 500);
                return;
            }
            
            $reflection = new \ReflectionMethod($controller, $method);
            $params = $request->getParams();
            
            try {
                $arguments = [];
                foreach ($reflection->getParameters() as $index => $parameter) {
                    if ($index === 0) {
                        $arguments[] = $request;
                        continue;
                    }

                    if ($index === 1) {
                        $arguments[] = $response;
                        continue;
                    }

                    $arguments[] = $this->resolveRouteParameter($parameter, $params);
                }

                $controllerInstance->$method(...$arguments);
            } catch (\Throwable $e) {
                error_log("Router: Error executing {$controller}::{$method}: " . $e->getMessage());
                $this->handleError($response, $e->getMessage(), 500);
            }
        } elseif (is_callable($handler)) {
            $handler($request, $response, $request->getParams());
        }
    }

    private function handleNotFound(Request $request, Response $response): void
    {
        $response->setStatusCode(404);
        if ($request->isAjax()) {
            $response->json(['error' => 'Not Found', 'path' => $request->getPath()]);
        } else {
            $response->view('errors/404', ['title' => '404 - Not Found']);
        }
    }

    private function handleError(Response $response, string $message, int $code): void
    {
        $response->setStatusCode($code);
        $response->json(['error' => 'Internal Server Error', 'message' => $message]);
    }

    private function resolveRouteParameter(\ReflectionParameter $parameter, array $params)
    {
        $type = $parameter->getType();
        if ($type instanceof \ReflectionNamedType && $type->getName() === 'array') {
            return $params;
        }

        $name = $parameter->getName();
        $value = $params[$name] ?? null;

        if ($value === null) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            return null;
        }

        if ($type instanceof \ReflectionNamedType) {
            switch ($type->getName()) {
                case 'int':
                    return (int)$value;
                case 'float':
                    return (float)$value;
                case 'bool':
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                case 'string':
                    return (string)$value;
            }
        }

        return $value;
    }

    private function matchPath(string $routePath, string $requestPath, array &$params): bool
    {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '/?$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return true;
        }

        return false;
    }
}
