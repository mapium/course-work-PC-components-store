<?php

// Перенаправляем все запросы на public/index.php
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = '/course-work-PC-components-store';

// Удаляем index.php если это нее
if (strpos($request_uri, '/index.php') !== false) {
    $request_uri = str_replace('/index.php', '', $request_uri);
}

// Удаляем базовый путь
if (strpos($request_uri, $base_path) === 0) {
    $path = substr($request_uri, strlen($base_path));
} else {
    $path = $request_uri;
}

$path = ltrim($path, '/');

// Перенаправляем через параметр url
if (!empty($path)) {
    $_GET['url'] = $path;
}

// Включаем главный файл приложения
require __DIR__ . '/public/index.php';

