<?php

declare(strict_types=1);

namespace App\Controllers\MasterAdmin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\User;
use App\Core\Database;

class RoleController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $search = trim((string)($request->get('search') ?? ''));
        $page = max(1, (int)($request->get('page') ?? 1));
        $perPage = min(50, max(5, (int)($request->get('perPage') ?? 10)));
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(name LIKE :q OR slug LIKE :q)';
            $params['q'] = "%{$search}%";
        }
        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $total = (int)($db->fetchOne("SELECT COUNT(*) AS c FROM roles {$whereSql}", $params)['c'] ?? 0);
        $rows = $db->fetchAll("SELECT * FROM roles {$whereSql} ORDER BY name ASC LIMIT {$perPage} OFFSET {$offset}", $params);

        $roleIds = array_map(fn($r) => (int)($r['id'] ?? 0), $rows);
        $counts = [];
        if (!empty($roleIds)) {
            $q = 'SELECT role_id, COUNT(*) AS cnt FROM role_user WHERE role_id IN (' . implode(',', array_map('intval', $roleIds)) . ') GROUP BY role_id';
            foreach ($db->fetchAll($q) as $c) { $counts[(int)$c['role_id']] = (int)$c['cnt']; }
        }

        // Assigned users preview per role (limited)
        $assignedUsers = [];
        foreach ($roleIds as $rid) {
            try {
                $assignedUsers[$rid] = $db->fetchAll(
                    'SELECT u.id, u.email, u.role, u.status, u.phone, u.last_login FROM users u INNER JOIN role_user ru ON ru.user_id = u.id WHERE ru.role_id = :rid ORDER BY u.email LIMIT 10',
                    ['rid' => $rid]
                );
            } catch (\Throwable $t) { $assignedUsers[$rid] = []; }
        }

        // One-time credentials banner
        $created = $_SESSION['last_created_credentials'] ?? null;
        unset($_SESSION['last_created_credentials']);

        $response->view('masteradmin/roles/index', [
            'title' => 'Roles',
            'roles' => $rows,
            'counts' => $counts,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'search' => $search,
            'created_user_email' => $created['email'] ?? null,
            'created_user_password' => $created['password'] ?? null,
            'created_user_role' => $created['role'] ?? null,
            'assignedUsers' => $assignedUsers,
            'assignedUsersAll' => $db->fetchAll(
                "SELECT r.name AS role_name, r.slug AS role_slug,
                        ru.role_id AS role_id,
                        u.id AS user_id, u.email, u.status, u.phone, u.last_login,
                        u.created_at AS assigned_date,
                        COALESCE(u.google_name, u.apple_name, u.email) AS full_name
                 FROM role_user ru
                 INNER JOIN roles r ON r.id = ru.role_id
                 INNER JOIN users u ON u.id = ru.user_id
                 ORDER BY r.slug, u.email"
            )
        ], 200, 'masteradmin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        $roles = \App\Models\Role::all();
        // Fetch all permissions for the role creation form, ordered by module
        $db = Database::getInstance();
        $permissions = $db->fetchAll("SELECT * FROM permissions ORDER BY module ASC, name ASC");

        $response->view('masteradmin/roles/create', [
            'title' => 'Create Role',
            'availableRoles' => array_map(fn($r) => $r->toArray(), $roles),
            'permissions' => $permissions
        ], 200, 'masteradmin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        $selectedSlug = trim((string)$request->post('assign_role_slug'));
        $name = trim((string)$request->post('name'));
        $slug = trim((string)$request->post('slug'));
        $desc = trim((string)$request->post('description'));
        $permissionIds = (array)$request->post('permission_ids');

        if ($selectedSlug !== '') {
            $existingRole = Role::where('slug', '=', $selectedSlug)->first();
            if ($existingRole) {
                $role = $existingRole;
            } else {
                $role = new Role(['name' => ucfirst(str_replace('_',' ', $selectedSlug)), 'slug' => $selectedSlug, 'description' => '']);
                $role->save();
            }
        } else {
            if ($name === '' || $slug === '') {
                $response->view('masteradmin/roles/create', [
                    'title' => 'Create Role',
                    'error' => 'Name and slug are required',
                    'availableRoles' => array_map(fn($r) => $r->toArray(), \App\Models\Role::all()),
                    'permissions' => Database::getInstance()->fetchAll("SELECT * FROM permissions ORDER BY module ASC, name ASC")
                ], 422, 'masteradmin/layout');
                return;
            }
            $existingRole = Role::where('slug', '=', $slug)->first();
            if ($existingRole) {
                $role = $existingRole;
            } else {
                $role = new Role(['name'=>$name,'slug'=>$slug,'description'=>$desc]);
                $role->save();
                // Assign permissions to the new role
                if (!empty($permissionIds)) {
                    $this->syncPermissions((int)$role->id, array_map('intval', $permissionIds));
                }
            }
        }

        if ($selectedSlug !== '') {
            $existingRole = Role::where('slug', '=', $selectedSlug)->first();
            if ($existingRole) {
                $role = $existingRole;
            } else {
                $role = new Role(['name' => ucfirst(str_replace('_',' ', $selectedSlug)), 'slug' => $selectedSlug, 'description' => '']);
                $role->save();
            }
        } else {
            if ($name === '' || $slug === '') {
                $response->view('masteradmin/roles/create', [
                    'title' => 'Create Role',
                    'error' => 'Name and slug are required'
                ], 422, 'masteradmin/layout');
                return;
            }
            $existingRole = Role::where('slug', '=', $slug)->first();
            if ($existingRole) {
                $role = $existingRole;
            } else {
                $role = new Role(['name'=>$name,'slug'=>$slug,'description'=>$desc]);
                $role->save();
            }
        }

        $email = trim((string)$request->post('user_email'));
        $fullName = trim((string)$request->post('user_name'));
        $password = (string)$request->post('user_password');
        if ($email !== '') {
            $db = Database::getInstance();
            $existing = User::where('email', '=', $email)->first();
            if (!$existing) {
                // Create staff/admin user and assign role
                $u = new User(['email' => $email, 'role' => ($selectedSlug !== '' ? $selectedSlug : 'admin'), 'status' => 'active']);
                if ($fullName !== '') { $u->fill(['google_name' => $fullName]); }
                $finalPassword = $password;
                if ($finalPassword === '' || strlen($finalPassword) < 8) {
                    $finalPassword = bin2hex(random_bytes(8));
                }
                $u->setPassword($finalPassword);
                $u->save();
                $db->query("INSERT INTO role_user (user_id, role_id) VALUES (:uid, :rid)", ['uid' => (int)$u->id, 'rid' => (int)$role->id]);
                $_SESSION['last_created_credentials'] = ['email' => $email, 'password' => $finalPassword, 'role' => $selectedSlug !== '' ? $selectedSlug : $slug];
                $response->redirect('/master/roles');
                return;
            } else {
                // Update existing user and assign role if not already
                $updateData = ['status' => 'active', 'role' => ($selectedSlug !== '' ? $selectedSlug : ($existing->attributes['role'] ?? 'admin'))];
                if ($fullName !== '') { $updateData['google_name'] = $fullName; }
                $existing->fill($updateData);
                $finalPassword = null;
                if ($password !== '' && strlen($password) >= 8) { 
                    $existing->setPassword($password); 
                    $finalPassword = $password;
                } else {
                    // Generate strong password if none or too short provided
                    $finalPassword = bin2hex(random_bytes(8));
                    $existing->setPassword($finalPassword);
                }
                $existing->save();
                $existsMap = $db->fetchOne("SELECT 1 FROM role_user WHERE user_id = :uid AND role_id = :rid", ['uid' => (int)$existing->id, 'rid' => (int)$role->id]);
                if (!$existsMap) {
                    $db->query("INSERT INTO role_user (user_id, role_id) VALUES (:uid, :rid)", ['uid' => (int)$existing->id, 'rid' => (int)$role->id]);
                }
                $_SESSION['last_created_credentials'] = ['email' => $email, 'password' => $finalPassword, 'role' => $selectedSlug !== '' ? $selectedSlug : $slug];
                $response->redirect('/master/roles?success=' . urlencode('User assigned to role'));
                return;
            }
        }
        $response->redirect('/master/roles');
    }

    public function resetUserPassword(Request $request, Response $response): void
    {
        $roleId = (int)$request->param('roleId');
        $userId = (int)$request->param('userId');
        $new = trim((string)$request->post('new_password'));
        if ($new === '' || strlen($new) < 8) {
            $response->redirect('/master/roles?error=' . urlencode('Password must be at least 8 characters'));
            return;
        }
        $user = User::find($userId);
        if ($user) {
            $user->setPassword($new);
            $user->save();
        }
        $response->redirect('/master/roles?success=' . urlencode('Password updated'));
    }

    public function removeUserAssignment(Request $request, Response $response): void
    {
        $roleId = (int)$request->param('roleId');
        $userId = (int)$request->param('userId');
        $db = Database::getInstance();
        $db->query('DELETE FROM role_user WHERE role_id = :rid AND user_id = :uid', ['rid' => $roleId, 'uid' => $userId]);
        $response->redirect('/master/roles?success=' . urlencode('Assignment removed'));
    }

    public function delete(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $role = Role::find($id);
        if ($role) {
            // Prevent deleting roles that have assigned users
            $db = Database::getInstance();
            $rc = (int)($db->fetchOne('SELECT COUNT(*) AS c FROM role_user WHERE role_id = :rid', ['rid' => $id])['c'] ?? 0);
            if ($rc === 0) {
                $role->delete();
            }
        }
        $response->redirect('/master/roles');
    }

    public function edit(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $role = Role::find($id);
        if (!$role) {
            $response->redirect('/master/roles');
            return;
        }
        $permissions = Permission::all();
        $assigned = $this->getAssignedPermissionIds($id);
        $response->view('masteradmin/roles/edit', [
            'title' => 'Edit Role',
            'role' => $role->toArray(),
            'permissions' => array_map(fn($p) => $p->toArray(), $permissions),
            'assigned' => $assigned
        ], 200, 'masteradmin/layout');
    }

    public function update(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $role = Role::find($id);
        if (!$role) {
            $response->redirect('/master/roles');
            return;
        }
        $role->fill([
            'name' => (string)$request->post('name'),
            'slug' => (string)$request->post('slug'),
            'description' => (string)$request->post('description')
        ]);
        $role->save();

        $permIds = (array)$request->post('permission_ids');
        $this->syncPermissions($id, array_map('intval', $permIds));
        $response->redirect('/master/roles');
    }

    private function getAssignedPermissionIds(int $roleId): array
    {
        $rows = PermissionRole::where('role_id', '=', $roleId)->get();
        return array_map(fn($r) => (int)($r->attributes['permission_id'] ?? 0), $rows);
    }

    private function syncPermissions(int $roleId, array $permissionIds): void
    {
        PermissionRole::where('role_id', '=', $roleId)->delete();
        foreach ($permissionIds as $pid) {
            $pr = new PermissionRole(['role_id'=>$roleId,'permission_id'=>$pid]);
            $pr->save();
        }
    }
}
