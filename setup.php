<?php

// Файл для инициализации и тестирования базы данных
// Используйте: php setup.php

require_once __DIR__ . '/src/config/Config.php';
require_once __DIR__ . '/src/config/Database.php';

echo "=== PCComponents Store Setup ===\n\n";

$db = new \App\Config\Database();
$pdo = $db->connect();

if (!$pdo) {
    die("❌ Database connection failed\n");
}

echo "✓ Database connection successful\n";

// Чтение и выполнение SQL-скрипта создания таблиц
$schema_sql = file_get_contents(__DIR__ . '/database/schema.sql');

try {
    $pdo->exec($schema_sql);
    echo "✓ Database tables created successfully\n";
} catch (\PDOException $e) {
    echo "⚠ Tables might already exist: " . $e->getMessage() . "\n";
}

// Чтение и выполнение SQL-скрипта с тестовыми данными
$seeds_sql = file_get_contents(__DIR__ . '/database/seeds.sql');

try {
    $pdo->exec($seeds_sql);
    echo "✓ Test data inserted successfully\n";
} catch (\PDOException $e) {
    echo "⚠ Data might already exist: " . $e->getMessage() . "\n";
}

echo "\n=== Setup Complete ===\n";
echo "\nDefault users:\n";
echo "1. Admin: admin@pccomponents.local (password: admin)\n";
echo "2. Client: client@example.local (password: client)\n";
echo "3. Manager: manager@pccomponents.local (password: manager)\n";
echo "\nNote: Update passwords in production!\n";
