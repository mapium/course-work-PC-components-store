<?php
// Генератор sitemap.xml для SEO

header('Content-Type: application/xml; charset=utf-8');

$base_url = 'https://pccomponents.local';

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Главная страница
echo '  <url>' . "\n";
echo '    <loc>' . htmlspecialchars($base_url) . '/</loc>' . "\n";
echo '    <lastmod>2026-10-25</lastmod>' . "\n";
echo '    <changefreq>daily</changefreq>' . "\n";
echo '    <priority>1.0</priority>' . "\n";
echo '  </url>' . "\n";

// Страница каталога
echo '  <url>' . "\n";
echo '    <loc>' . htmlspecialchars($base_url) . '/catalog</loc>' . "\n";
echo '    <lastmod>2026-10-25</lastmod>' . "\n";
echo '    <changefreq>daily</changefreq>' . "\n";
echo '    <priority>0.9</priority>' . "\n";
echo '  </url>' . "\n";

// Динамические товары из БД
require_once __DIR__ . '/../src/config/Database.php';
require_once __DIR__ . '/../src/models/Product.php';

$db = new \App\Config\Database();
$pdo = $db->connect();

if ($pdo) {
    $product_model = new \App\Models\Product($pdo);
    $products = $product_model->getAll(1000, 0);

    foreach ($products as $product) {
        echo '  <url>' . "\n";
        echo '    <loc>' . htmlspecialchars($base_url) . '/product/' . $product['id'] . '</loc>' . "\n";
        echo '    <lastmod>' . substr($product['updated_at'], 0, 10) . '</lastmod>' . "\n";
        echo '    <changefreq>weekly</changefreq>' . "\n";
        echo '    <priority>0.8</priority>' . "\n";
        echo '  </url>' . "\n";
    }
}

// Страницы информации
$info_pages = [
    '/about' => 'О компании',
    '/delivery' => 'Доставка',
    '/contacts' => 'Контакты',
    '/faq' => 'Часто задаваемые вопросы',
];

foreach ($info_pages as $url => $name) {
    echo '  <url>' . "\n";
    echo '    <loc>' . htmlspecialchars($base_url . $url) . '</loc>' . "\n";
    echo '    <lastmod>2026-10-25</lastmod>' . "\n";
    echo '    <changefreq>monthly</changefreq>' . "\n";
    echo '    <priority>0.7</priority>' . "\n";
    echo '  </url>' . "\n";
}

echo '</urlset>' . "\n";
