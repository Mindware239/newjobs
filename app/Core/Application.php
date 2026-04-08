<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Database;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Middlewares\MiddlewareInterface;

class Application
{
    private Router $router;
    private Request $request;
    private Response $response;
    private array $middlewares = [];
    private static ?Application $instance = null;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        Database::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setRouter(Router $router): void
    {
        $this->router = $router;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function addMiddleware(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function run(): void
    {
        try {
            // Skip middlewares for captcha generation (image output)
            $path = $this->request->getPath();
            if (strpos($path, '/admin/captcha/generate') === 0 || strpos($path, '/captcha') === 0) {
                // Dispatch route directly without middlewares for captcha
                $this->router->dispatch($this->request, $this->response);
                return;
            }
            
            // Execute global middlewares in a chain
            $this->runMiddlewareChain($this->request, $this->response, $this->middlewares, function($req, $res) {
                // After all global middlewares, dispatch the router
                $this->router->dispatch($req, $res);
            });

        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    private function runMiddlewareChain(Request $request, Response $response, array $middlewares, callable $final): void
    {
        $index = 0;

        $next = function (Request $req, Response $res) use (&$index, $middlewares, $final, &$next) {
            if ($index < count($middlewares)) {
                $middleware = $middlewares[$index++];
                $middleware->handle($req, $res, $next);
            } else {
                $final($req, $res);
            }
        };

        $next($request, $response);
    }

    private function handleException(\Throwable $e): void
    {
        // Don't catch errors for captcha - let them propagate
        if (strpos($this->request->getPath(), '/admin/captcha/generate') === 0) {
            throw $e;
        }
        
        $path = $this->request->getPath();
        $message = $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'An unexpected error occurred';
        
        // Log to database
        try {
            Database::getInstance()->query(
                "INSERT INTO system_logs (type, module, message, user_id, created_at)
                 VALUES ('error', :module, :message, :user_id, NOW())",
                [
                    'module' => $path,
                    'message' => $e->getMessage(),
                    'user_id' => (int)($_SESSION['user_id'] ?? 0)
                ]
            );
        } catch (\Throwable $ignore) {}

        // Log to PHP error log
        error_log("Exception in {$path}: " . $e->getMessage() . "\n" . $e->getTraceAsString());

        if ($this->request->isAjax()) {
            $this->response->setStatusCode(500);
            $this->response->json([], 500, $message, false, [
                'type' => get_class($e),
                'file' => $_ENV['APP_DEBUG'] === 'true' ? $e->getFile() : null,
                'line' => $_ENV['APP_DEBUG'] === 'true' ? $e->getLine() : null
            ]);
        } else {
            $view = strpos($path, '/admin') === 0 ? 'admin/error' : 'errors/500';
            $layout = strpos($path, '/admin') === 0 ? 'admin/layout' : null;
            
            $this->response->view(
                $view,
                [
                    'title' => 'Error',
                    'errorMessage' => $message
                ],
                500,
                $layout
            );
        }
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
