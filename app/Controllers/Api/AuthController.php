<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Models\User;

class AuthController extends ApiController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="User login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful")
     * )
     */
    public function login(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $user = $this->authService->login($data['email'], $data['password']);

        if (!$user) {
            $this->error($response, 'Invalid credentials', 401);
            return;
        }

        $token = $this->authService->generateToken($user);

        $this->success($response, [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status
            ]
        ], 'Login successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/register-candidate",
     *     summary="Candidate registration",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="full_name", type="string"),
     *             @OA\Property(property="mobile", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Registration successful")
     * )
     */
    public function registerCandidate(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required|password_strong|min:8',
            'full_name' => 'required',
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $user = $this->authService->registerCandidate($data);

        if (!$user) {
            $this->error($response, 'Registration failed or email already exists', 400);
            return;
        }

        $token = $this->authService->generateToken($user);

        $this->success($response, [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]
        ], 'Registration successful', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/register-employer",
     *     summary="Employer registration",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="company_name", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Registration successful")
     * )
     */
    public function registerEmployer(Request $request, Response $response): void
    {
        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required|password_strong|min:8',
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $user = $this->authService->registerEmployer($data);

        if (!$user) {
            $this->error($response, 'Registration failed or email already exists', 400);
            return;
        }

        $token = $this->authService->generateToken($user);

        $this->success($response, [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]
        ], 'Registration successful', 201);
    }

    public function me(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $this->success($response, [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status
        ]);
    }
}
