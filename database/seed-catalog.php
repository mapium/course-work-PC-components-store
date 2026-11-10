<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// Не запускает старые SQL-сиды, не очищает таблицы и не зависит от ID категорий.
try {
    $data = json_decode(file_get_contents(__DIR__ . '/catalog-products.json'), true, 512, JSON_THROW_ON_ERROR);
    $import = require __DIR__ . '/seed-catalog-data.php';
    require_once dirname(__DIR__) . '/src/config/Database.php';
    $pdo = (new App\Config\Database())->connect();
    if (!$pdo) throw new RuntimeException('Не удалось подключиться к базе магазина');
    $result = $import($pdo, $data);
    echo 'Добавлено товаров: ' . $result['added'] . PHP_EOL
        . 'Пропущено существующих: ' . $result['skipped'] . PHP_EOL
        . 'Добавлено категорий: ' . $result['categories_added'] . PHP_EOL
        . 'Активных товаров в базе: ' . $result['active_total'] . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, 'Импорт не выполнен: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
