<?php

namespace App\Helpers;

class CSRF {
    /**
     * Генерирует CSRF токен для защиты форм
     * @return string CSRF токен
     */
    public static function generateToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Проверяет валидность CSRF токена
     * @param string $token Токен для проверки
     * @return bool True если токен валиден
     */
    public static function validateToken($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Генерирует HTML input для CSRF токена
     * @return string HTML input элемент
     */
    public static function getTokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Регенерирует CSRF токен (использовать после успешной отправки формы)
     */
    public static function regenerateToken() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
