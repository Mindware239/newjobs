<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Company;
use App\Middlewares\CsrfMiddleware;

class FeaturedCompaniesController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $companyModel = new Company();
        $companyModel->ensureFeaturedSchema();

        $db = Database::getInstance();
        $featured = [];
        $others = [];
        try {
            $featured = $db->fetchAll(
                "SELECT id, name, slug, logo_url, employer_id, featured_order 
                 FROM companies 
                 WHERE is_featured = 1 
                 ORDER BY featured_order ASC, name ASC"
            );
            $others = $db->fetchAll(
                "SELECT id, name, slug, logo_url, employer_id 
                 FROM companies 
                 WHERE (is_featured = 0 OR is_featured IS NULL)
                 ORDER BY name ASC
                 LIMIT 100"
            );
        } catch (\Exception $e) {}

        CsrfMiddleware::generateToken();
        $response->view('admin/companies/featured', [
            'title' => 'Featured Companies',
            'featured' => $featured,
            'others' => $others,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function updateOrder(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $companyModel = new Company();
        $companyModel->ensureFeaturedSchema();

        $order = $request->post('order') ?? [];
        $featuredIds = $request->post('featured_ids') ?? [];
        if (!is_array($order)) $order = [];
        if (!is_array($featuredIds)) $featuredIds = [];

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            // Reset all featured flags first for provided IDs range (optional)
            // Then set featured for provided list
            foreach ($featuredIds as $pos => $companyId) {
                $cid = (int)$companyId;
                $ord = isset($order[$pos]) ? (int)$order[$pos] : ($pos + 1);
                $companyModel->setFeatured($cid, true, $ord);
            }
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
        }

        $response->redirect('/admin/companies/featured');
    }

    private function requireAdmin(Request $request, Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }
}
