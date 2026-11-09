<?php
// Запуск: php database/migrate-catalog.php. Добавляет только недостающие поля.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/src/config/Database.php';
$pdo = (new \App\Config\Database())->connect();
if (!$pdo) exit(1);
$columns = array_column($pdo->query('SHOW COLUMNS FROM categories')->fetchAll(PDO::FETCH_ASSOC), 'Field');
foreach (['sort_order' => 'INT NOT NULL DEFAULT 0', 'is_visible' => 'BOOLEAN NOT NULL DEFAULT TRUE'] as $name => $type) {
    if (!in_array($name, $columns, true)) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN $name $type");
        echo "Added categories.$name\n";
    }
}
echo "Catalog schema is up to date.\n";
