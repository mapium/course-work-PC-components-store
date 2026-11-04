<?php

namespace App\Helpers;

class Auth {
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function login($user_id, $role) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;
        $_SESSION['login_time'] = time();
    }

    public static function logout() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUserId() {
        return self::getCurrentUserId();
    }

    public static function getCurrentRole() {
        return $_SESSION['role'] ?? 'guest';
    }

    public static function getUserRole() {
        return self::getCurrentRole();
    }

    public static function hasRole($role) {
        return self::getCurrentRole() === $role;
    }

    public static function checkSessionTimeout() {
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
                self::logout();
                return false;
            }
        }
        return true;
    }
}
