<?php

namespace App\Helpers;

class Request {
    public static function isAjax() {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    public static function text($data, $key, $default = '') {
        return isset($data[$key]) && is_string($data[$key]) ? trim($data[$key]) : $default;
    }

    public static function integer($data, $key, $default = 0) {
        $value = $data[$key] ?? null;
        if (!is_scalar($value) || is_bool($value)) {
            return $default;
        }
        $number = filter_var($value, FILTER_VALIDATE_INT);
        return $number === false ? $default : $number;
    }

    public static function input() {
        if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
            return is_array($data) ? $data : [];
        }
        return $_POST;
    }

    public static function localPath($value, $fallback = '/') {
        // Никогда не используем присланный клиентом внешний URL для перенаправления.
        if (!is_string($value) || preg_match('~\A/(?!/)[^\x00-\x20\\\\]*\z~u', $value) !== 1) {
            return $fallback;
        }
        return $value;
    }

    public static function currentPath($fallback = '/') {
        $uri = $_SERVER['REQUEST_URI'] ?? $fallback;
        $base = parse_url(APP_URL, PHP_URL_PATH) ?: '';
        if ($base !== '' && ($uri === $base || strpos($uri, $base . '/') === 0 || strpos($uri, $base . '?') === 0)) {
            $uri = substr($uri, strlen($base)) ?: '/';
        }
        return self::localPath($uri, $fallback);
    }

    public static function length($text) {
        return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : iconv_strlen($text, 'UTF-8');
    }

    public static function validPhone($phone) {
        return preg_match('/\A[\d+\-\s()]+\z/', $phone) === 1
            && strlen(preg_replace('/\D/', '', $phone)) >= 10
            && strlen($phone) <= 20;
    }
}
