<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

try {
    require_once dirname(__DIR__) . '/src/config/Database.php';
    $pdo = (new App\Config\Database())->connect();
    if (!$pdo) throw new RuntimeException('Не удалось подключиться к базе магазина');
    $stmt = $pdo->query(file_get_contents(__DIR__ . '/migrate-product-discovery.sql'));
    // Проверяем результат каждого SQL-оператора, включая ошибки после первого ALTER TABLE.
    while ($stmt->nextRowset()) {}
    $stmt->closeCursor();
    echo 'Счётчик orders_count, индекс популярности и таблица product_views готовы.' . PHP_EOL
        . 'Повторный запуск сохраняет счётчики и историю просмотров.' . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, 'Миграция не выполнена: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
