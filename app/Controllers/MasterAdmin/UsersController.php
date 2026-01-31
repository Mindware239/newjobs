<?php

declare(strict_types=1);

namespace App\Controllers\MasterAdmin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class UsersController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
        
        $response->view('masteradmin/users/index', [
            'title' => 'User Management',
            'users' => $users
        ], 200, 'masteradmin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        $response->view('masteradmin/users/create', [
            'title' => 'Create New User'
        ], 200, 'masteradmin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        $input = $request->getParsedBody();
        $db = Database::getInstance();

        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'sales_executive';
        $gender = $input['gender'] ?? null;
        $dob = !empty($input['dob']) ? $input['dob'] : null;
        $phone = trim($input['phone'] ?? '');
        $address = trim($input['address'] ?? '');
        $education = trim($input['education'] ?? '');
        $username = trim($input['username'] ?? '');
        $joiningDate = !empty($input['joining_date']) ? $input['joining_date'] : date('Y-m-d');

        if (empty($name) || empty($email) || empty($password)) {
            // Basic validation
            $response->redirect('/master/users/create');
            return;
        }

        // Check if email or username exists
        $exists = $db->fetchOne("SELECT id FROM users WHERE email = :e OR username = :u", ['e' => $email, 'u' => $username]);
        if ($exists) {
            // Handle error (ideally show message)
            $response->redirect('/master/users/create');
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Handle profile image upload if present (simplified for now)
        $profileImage = ''; 

        $sql = "INSERT INTO users (
            name, email, password_hash, role, username, gender, dob, phone, address, education, joining_date, status, is_email_verified, created_at, updated_at
        ) VALUES (
            :name, :email, :pass, :role, :username, :gender, :dob, :phone, :address, :education, :joining_date, 'active', 1, NOW(), NOW()
        )";

        $db->query($sql, [
            'name' => $name,
            'email' => $email,
            'pass' => $passwordHash,
            'role' => $role,
            'username' => $username,
            'gender' => $gender,
            'dob' => $dob,
            'phone' => $phone,
            'address' => $address,
            'education' => $education,
            'joining_date' => $joiningDate
        ]);

        $response->redirect('/master/users');
    }

    public function edit(Request $request, Response $response): void
    {
        $id = (int)$request->getRouteParam('id');
        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $id]);

        if (!$user) {
            $response->redirect('/master/users');
            return;
        }

        $roles = $db->fetchAll("SELECT * FROM roles ORDER BY name ASC");

        $response->view('masteradmin/users/edit', [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => $roles
        ], 200, 'masteradmin/layout');
    }

    public function update(Request $request, Response $response): void
    {
        $id = (int)$request->getRouteParam('id');
        $input = $request->getParsedBody();
        $db = Database::getInstance();

        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $role = $input['role'] ?? 'sales_executive';
        $gender = $input['gender'] ?? null;
        $dob = !empty($input['dob']) ? $input['dob'] : null;
        $phone = trim($input['phone'] ?? '');
        $address = trim($input['address'] ?? '');
        $education = trim($input['education'] ?? '');
        $username = trim($input['username'] ?? '');
        $joiningDate = !empty($input['joining_date']) ? $input['joining_date'] : null;

        $updateSql = "UPDATE users SET 
            name = :name, 
            email = :email, 
            role = :role, 
            username = :username, 
            gender = :gender, 
            dob = :dob, 
            phone = :phone, 
            address = :address, 
            education = :education, 
            joining_date = :joining_date,
            updated_at = NOW()
            WHERE id = :id";
        
        $params = [
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'username' => $username,
            'gender' => $gender,
            'dob' => $dob,
            'phone' => $phone,
            'address' => $address,
            'education' => $education,
            'joining_date' => $joiningDate,
            'id' => $id
        ];

        // Handle Password Update if provided
        if (!empty($input['password'])) {
            $updateSql = str_replace("WHERE id = :id", ", password_hash = :pass WHERE id = :id", $updateSql);
            $params['pass'] = password_hash($input['password'], PASSWORD_BCRYPT);
        }

        $db->query($updateSql, $params);

        // Sync with role_user table
        $roleId = $db->fetchOne("SELECT id FROM roles WHERE slug = :slug", ['slug' => $role])['id'] ?? null;
        if ($roleId) {
            // Remove existing role assignment
            $db->query("DELETE FROM role_user WHERE user_id = :uid", ['uid' => $id]);
            // Add new role assignment
            $db->query("INSERT INTO role_user (user_id, role_id) VALUES (:uid, :rid)", ['uid' => $id, 'rid' => $roleId]);
        }

        $response->redirect('/master/users');
    }
}
