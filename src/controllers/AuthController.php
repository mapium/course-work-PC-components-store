<?php

namespace App\Controllers;

use App\Models\User;
use App\Helpers\Auth;
use App\Helpers\CSRF;

class AuthController {
    private $db;
    private $user_model;

    public function __construct($db) {
        $this->db = $db;
        $this->user_model = new User($db);
    }

    public function register() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            return;
        }

        // CSRF защита
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $email = $_POST['email'] ?? null;
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;
        $full_name = $_POST['full_name'] ?? null;
        $phone = $_POST['phone'] ?? null;

        if (!$email || !$username || !$password) {
            echo json_encode(['success' => false, 'message' => 'Required fields missing']);
            return;
        }

        // Проверка валидности email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email']);
            return;
        }

        // Проверка существования пользователя
        if ($this->user_model->getByEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'User already exists']);
            return;
        }

        $hashed_password = Auth::hashPassword($password);

        try {
            $this->user_model->create([
                'email' => $email,
                'username' => $username,
                'password' => $hashed_password,
                'full_name' => $full_name,
                'phone' => $phone
            ]);

            echo json_encode(['success' => true, 'message' => 'User registered successfully']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Registration error']);
        }
    }

    public function login() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            return;
        }

        // CSRF защита
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'Email and password required']);
            return;
        }

        $user = $this->user_model->getByEmail($email);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }

        if ($user['status'] === 'blocked') {
            echo json_encode(['success' => false, 'message' => 'User is blocked']);
            return;
        }

        if (!Auth::verifyPassword($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
            return;
        }

        Auth::login($user['id'], $user['role']);

        echo json_encode(['success' => true, 'message' => 'Login successful']);
    }

    public function logout() {
        Auth::logout();
        header('Location: ' . APP_URL . '/');
        exit;
    }
}
