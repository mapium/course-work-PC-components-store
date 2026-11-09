<?php

namespace App\Helpers;

class Response {
    public static function send($result, $status = 200, $redirect = null) {
        if ($redirect !== null && !Request::isAjax()) {
            Flash::add($result['message'] ?? '', !empty($result['success']) ? 'success' : 'error');
            self::redirect($redirect);
            return;
        }

        if ($redirect !== null) $result['redirect'] = APP_URL . Request::localPath($redirect);
        http_response_code($status);
        header('Cache-Control: no-store');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public static function redirect($path) {
        header('Location: ' . APP_URL . Request::localPath($path), true, 303);
    }
}
