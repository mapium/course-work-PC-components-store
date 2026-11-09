<?php

namespace App\Helpers;

class Flash {
    public static function add($message, $type = 'success') {
        $_SESSION['flash_messages'][] = [
            'message' => $message,
            'type' => in_array($type, ['success', 'error', 'info'], true) ? $type : 'info'
        ];
    }

    public static function consume() {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }
}
