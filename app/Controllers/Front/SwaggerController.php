<?php
namespace App\Controllers\Front;

use App\Core\Request;
use App\Core\Response;

class SwaggerController
{
    /**
     * Display the Swagger UI
     */
    public function ui(Request $request, Response $response): void
    {
        $swaggerPath = __DIR__ . '/../../../public/swagger-ui.html';
        if (file_exists($swaggerPath)) {
            header('Content-Type: text/html; charset=utf-8');
            echo file_get_contents($swaggerPath);
            exit;
        }
        
        $response->setStatusCode(404);
        $response->json(['error' => 'Swagger UI not found']);
    }

    /**
     * Serve the Swagger JSON specification
     */
    public function json(Request $request, Response $response): void
    {
        $swaggerPath = __DIR__ . '/../../../public/swagger.json';
        if (file_exists($swaggerPath)) {
            header('Content-Type: application/json; charset=utf-8');
            echo file_get_contents($swaggerPath);
            exit;
        }
        
        $response->setStatusCode(404);
        $response->json(['error' => 'Swagger JSON not found']);
    }
}
