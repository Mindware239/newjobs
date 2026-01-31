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
            
            // Execute middlewares
            foreach ($this->middlewares as $middleware) {
                $middleware->handle($this->request, $this->response);
            }

            // Dispatch route
            $this->router->dispatch($this->request, $this->response);
        } catch (\Exception $e) {
            // Don't catch errors for captcha - let them propagate
            if (strpos($this->request->getPath(), '/admin/captcha/generate') === 0) {
                throw $e;
            }
            
            $isAjax = $this->request->isAjax();
            $path = $this->request->getPath();
            $message = $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'An unexpected error occurred';
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
            if ($isAjax) {
                $this->response->setStatusCode(500);
                $this->response->json([
                    'error' => 'Internal Server Error',
                    'message' => $message
                ]);
            } else {
                $this->response->view(
                    strpos($path, '/admin') === 0 ? 'admin/error' : 'about',
                    [
                        'title' => 'Error',
                        'errorMessage' => $message
                    ],
                    500,
                    strpos($path, '/admin') === 0 ? 'admin/layout' : null
                );
            }
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

