<?php
namespace App\Controllers\Front;

use App\Core\Request;
use App\Core\Response;
use App\Services\HomeService;
use App\Services\SeoService;

class HomeController
{
    private HomeService $homeService;

    public function __construct()
    {
        $this->homeService = new HomeService();
    }

    /**
     * Handles the home page request
     */
    public function index(Request $request, Response $response): void
    {
        // Redirect logged-in users to their dashboard
        if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
            $role = $_SESSION['user_role'];
            if ($role === 'candidate') {
                $response->redirect('/candidate/dashboard');
                return;
            } elseif ($role === 'employer') {
                $response->redirect('/employer/dashboard');
                return;
            } elseif ($role === 'admin') {
                $response->redirect('/admin/dashboard');
                return;
            }
        }

        // Get all data required for the home page via the Service layer
        $data = $this->homeService->getHomeData();

        // Initialize SEO
        $seo = SeoService::getInstance()->resolve('home', [
            'job_count' => $data['stats']['jobs'] ?? 0,
            'city' => 'India' // Default context
        ]);

        $data['seo'] = $seo;
        $data['title'] = 'Job Portal'; // Overridden by SEO service

        // Return the view with data
        $response->view('home', $data, 200, 'layout');
    }
}