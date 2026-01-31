<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class ReportsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $response->view('admin/reports/index', [
            'title' => 'Reports & Analytics',
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function export(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $type = $request->get('type', 'users');
        $format = $request->get('format', 'csv');
        $db = Database::getInstance();

        $data = [];
        $filename = '';

        switch ($type) {
            case 'users':
                $data = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
                $filename = 'users_' . date('Y-m-d') . '.csv';
                break;
            case 'employers':
                $data = $db->fetchAll("SELECT * FROM employers ORDER BY created_at DESC");
                $filename = 'employers_' . date('Y-m-d') . '.csv';
                break;
            case 'candidates':
                $data = $db->fetchAll("SELECT * FROM candidates ORDER BY created_at DESC");
                $filename = 'candidates_' . date('Y-m-d') . '.csv';
                break;
            case 'jobs':
                $data = $db->fetchAll("SELECT * FROM jobs ORDER BY created_at DESC");
                $filename = 'jobs_' . date('Y-m-d') . '.csv';
                break;
            case 'payments':
                $data = $db->fetchAll("SELECT * FROM employer_payments ORDER BY created_at DESC");
                $filename = 'payments_' . date('Y-m-d') . '.csv';
                break;
        }

        if ($format === 'csv' && !empty($data)) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit;
        }

        $response->redirect('/admin/reports');
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

