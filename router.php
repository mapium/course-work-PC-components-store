<?php
// Синтаксис: php -S localhost:8000 -t public

// Перенаправление всех запросов на index.php
if (php_sapi_name() == 'cli-server') {
    if (preg_match('/\.(?:js|css|png|jpg|jpeg|gif|svg|woff|woff2)$/', $_SERVER["REQUEST_URI"])) {
        return false;
    }
}

// Подключение основного приложения
require __DIR__ . '/public/index.php';
