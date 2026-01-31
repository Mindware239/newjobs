<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private static ?Router $instance = null;
    private array $routes = [];
    private array $middlewares = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    public function delete(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
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
        
        // Debug logging (remove in production)
        if ($path === '/candidate/profile/complete') {
            error_log("Router: Attempting to match path: {$path}, method: {$method}");
            error_log("Router: Total routes: " . count($this->routes));
        }

        foreach ($this->routes as $route) {
            $params = [];
            if ($route['method'] === $method && $this->matchPath($route['path'], $path, $params)) {
                // Execute route-specific middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $middleware->handle($request, $response);
                }

                // Execute handler
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$controller, $method] = $handler;
                    
                    // Check if controller class exists
                    if (!class_exists($controller)) {
                        error_log("Router: Controller class not found: {$controller}");
                        $response->setStatusCode(500);
                        $response->json(['error' => 'Controller not found: ' . $controller]);
                        return;
                    }
                    
                    $controllerInstance = new $controller();
                    $request->setParams($params);
                    
                    // Check if method exists
                    if (!method_exists($controllerInstance, $method)) {
                        error_log("Router: Method not found: {$controller}::{$method}");
                        $response->setStatusCode(500);
                        $response->json(['error' => 'Method not found: ' . $method]);
                        return;
                    }
                    
                    // Check if method expects params as third argument
                    $reflection = new \ReflectionMethod($controller, $method);
                    $paramCount = $reflection->getNumberOfParameters();
                    
                    try {
                        if ($paramCount === 3) {
                            $controllerInstance->$method($request, $response, $params);
                        } else {
                            $controllerInstance->$method($request, $response);
                        }
                    } catch (\Exception $e) {
                        error_log("Router: Error executing {$controller}::{$method}: " . $e->getMessage());
                        throw $e;
                    }
                } elseif (is_callable($handler)) {
                    $request->setParams($params);
                    $handler($request, $response, $params);

                }
                return;
            }
        }

        // No route matched - return proper 404
        $response->setStatusCode(404);
        if ($request->isAjax() || strpos($request->header('Accept') ?? '', 'application/json') !== false) {
            $response->json(['error' => 'Not Found', 'path' => $path, 'method' => $method]);
        } else {
            // Try to show a 404 view if it exists
            $viewPath = __DIR__ . '/../../resources/views/errors/404.php';
            if (file_exists($viewPath)) {
                $response->view('errors/404', ['message' => 'Page not found', 'path' => $path]);
            } else {
                $response->setHeader('Content-Type', 'text/html; charset=utf-8');
                echo "<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested path '{$path}' was not found.</p></body></html>";
            }
        }
    }

    private function matchPath(string $routePath, string $requestPath, array &$params): bool
    {
        // Extract parameter names
        preg_match_all('/\{(\w+)\}/', $routePath, $paramNames);
        $paramNames = $paramNames[1] ?? [];

        // Convert route pattern to regex
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $routePath);
        // Allow optional trailing slash
        $pattern = '#^' . $pattern . '/?$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            array_shift($matches); // Remove full match
            if (!empty($paramNames) && !empty($matches)) {
                $params = array_combine($paramNames, $matches);
            } else {
                $params = [];
            }
            return true;
        }

        return false;
    }
}

