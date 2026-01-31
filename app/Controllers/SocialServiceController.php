<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class SocialServiceController extends BaseController
{
    /**
     * Frontend Social Services Page
     * URL: /social-services
     */
    public function index(Request $request, Response $response): void
    {
        // Frontend demo data (later DB se aayega)
        $services = [
            [
                'title' => 'Hospital Patient Assistance',
                'category' => 'Hospital Help',
                'description' => 'Helping patients with registration, medicines and hospital guidance.',
                'service_type' => 'Unpaid',
                'city' => 'Delhi'
            ],
            [
                'title' => 'Old Age Home Support',
                'category' => 'Old Age Home',
                'description' => 'Daily care and food support for senior citizens.',
                'service_type' => 'Paid',
                'city' => 'Noida'
            ]
        ];

        // Render frontend view
        $response->view('social-services/index', [
            'services' => $services
        ]);
    }
 
public function findjob(Request $request, Response $response): void
{
    $response->view('social-services/find-a-job');
}


     public function roles(Request $request, Response $response): void
{
    $response->view('social-services/roles', []);
}
  public function createjob(Request $request, Response $response): void
  {
    $response->view('social-services/createjob',[]);

  }
  public function candidate(Request $request, Response $response): void
    {
        $response->view('social-services/candidate');
    }
 public function listings(Request $request, Response $response): void
    {
        $response->view('social-services/listings');
    }
 public function subscriptions(Request $request, Response $response): void
    {
        $response->view('social-services/subscriptions');
    }

    public function newsubscriptions(Request $request, Response $response): void
    {
        $response->view('social-services/newsubscriptions');
    }
     public function employers(Request $request, Response $response): void
    {
        $response->view('social-services/employers');
    }
       public function pricing(Request $request, Response $response): void
    {
        $response->view('social-services/pricing');
    }
      public function aboutus(Request $request, Response $response): void
    {
        $response->view('social-services/aboutus');
    }
      public function specials(Request $request, Response $response): void
    {
        $response->view('social-services/specials');
    }

    public function terms(Request $request, Response $response): void
    {
        $response->view('social-services/terms');
    }

    public function privacy(Request $request, Response $response): void
    {
        $response->view('social-services/privacy');
    }

    public function grievances(Request $request, Response $response): void
    {
        $response->view('social-services/grievances');
    }
    
    public function supports(Request $request, Response $response): void
    {
        $response->view('social-services/supports');
    }
       public function cart(Request $request, Response $response): void
    {
        $response->view('social-services/cart');
    }
}

