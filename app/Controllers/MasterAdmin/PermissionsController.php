<?php

declare(strict_types=1);

namespace App\Controllers\MasterAdmin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Permission;

class PermissionsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = \App\Core\Database::getInstance();
        $page = max(1, (int)($request->get('page') ?? 1));
        $perPage = min(100, max(5, (int)($request->get('perPage') ?? 20)));
        $search = trim((string)($request->get('search') ?? ''));
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(name LIKE :q OR slug LIKE :q OR module LIKE :q)';
            $params['q'] = "%{$search}%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $total = (int)($db->fetchOne("SELECT COUNT(*) AS c FROM permissions {$whereSql}", $params)['c'] ?? 0);
        
        // Order by Module first for grouping
        $rows = $db->fetchAll("SELECT * FROM permissions {$whereSql} ORDER BY module ASC, name ASC LIMIT {$perPage} OFFSET {$offset}", 
            $params
        );

        $response->view('masteradmin/permissions/index', [
            'title' => 'Permissions',
            'permissions' => $rows,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'search' => $search
        ], 200, 'masteradmin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        $db = \App\Core\Database::getInstance();
        $moduleRows = $db->fetchAll("SELECT DISTINCT module FROM permissions WHERE module IS NOT NULL AND module <> '' ORDER BY module ASC");
        $modules = [];
        foreach ($moduleRows as $row) {
            $value = trim((string)($row['module'] ?? ''));
            if ($value !== '') {
                $modules[] = $value;
            }
        }

        $response->view('masteradmin/permissions/create', [
            'title' => 'Create Permission',
            'modules' => $modules
        ], 200, 'masteradmin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        $name = trim((string)$request->post('name'));
        $slug = trim((string)$request->post('slug'));
        $module = trim((string)$request->post('module'));

        $db = \App\Core\Database::getInstance();
        $moduleRows = $db->fetchAll("SELECT DISTINCT module FROM permissions WHERE module IS NOT NULL AND module <> '' ORDER BY module ASC");
        $modules = [];
        foreach ($moduleRows as $row) {
            $value = trim((string)($row['module'] ?? ''));
            if ($value !== '') {
                $modules[] = $value;
            }
        }
        
        if ($name === '' || $slug === '') {
            $response->view('masteradmin/permissions/create', [
                'title' => 'Create Permission',
                'error' => 'Name and slug are required',
                'old' => ['name' => $name, 'slug' => $slug, 'module' => $module],
                'modules' => $modules
            ], 422, 'masteradmin/layout');
            return;
        }

        $perm = new Permission(['name'=>$name, 'slug'=>$slug, 'module'=>$module]);
        $perm->save();
        
        // Redirect with success message (if flash supported) or just redirect
        $response->redirect('/master/permissions');
    }

    public function edit(Request $request, Response $response, array $args): void
    {
        $id = (int)($args['id'] ?? 0);
        $db = \App\Core\Database::getInstance();
        $perm = $db->fetchOne("SELECT * FROM permissions WHERE id = :id", ['id' => $id]);

        if (!$perm) {
            $response->redirect('/master/permissions');
            return;
        }

        $response->view('masteradmin/permissions/edit', [
            'title' => 'Edit Permission',
            'permission' => $perm
        ], 200, 'masteradmin/layout');
    }

    public function update(Request $request, Response $response, array $args): void
    {
        $id = (int)($args['id'] ?? 0);
        $name = trim((string)$request->post('name'));
        $slug = trim((string)$request->post('slug'));
        $module = trim((string)$request->post('module'));

        if ($name === '' || $slug === '') {
             $response->view('masteradmin/permissions/edit', [
                'title' => 'Edit Permission',
                'error' => 'Name and slug are required',
                'permission' => ['id' => $id, 'name' => $name, 'slug' => $slug, 'module' => $module]
            ], 422, 'masteradmin/layout');
            return;
        }

        $db = \App\Core\Database::getInstance();
        $db->query("UPDATE permissions SET name = :name, slug = :slug, module = :module WHERE id = :id", [
            'name' => $name,
            'slug' => $slug,
            'module' => $module,
            'id' => $id
        ]);

        $response->redirect('/master/permissions');
    }

    public function delete(Request $request, Response $response, array $args): void
    {
        $id = (int)($args['id'] ?? 0);
        $db = \App\Core\Database::getInstance();
        
        // Check if used in role_permission (optional safety check)
        // $used = $db->fetchOne("SELECT COUNT(*) as c FROM permission_role WHERE permission_id = :id", ['id'=>$id])['c'];
        // if ($used > 0) { ... error ... }

        $db->query("DELETE FROM permissions WHERE id = :id", ['id' => $id]);
        $db->query("DELETE FROM permission_role WHERE permission_id = :id", ['id' => $id]); // Cleanup relations
        
        $response->redirect('/master/permissions');
    }

}
