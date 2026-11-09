<?php
$category = (new \App\Models\Category($pdo))->getByPath(\App\Helpers\Request::text($_GET, 'slug'));
if (!$category) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    return;
}
include __DIR__ . '/catalog.php';
