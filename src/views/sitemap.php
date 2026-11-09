<?php
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=300');
$urls = [['url' => APP_URL . '/'], ['url' => APP_URL . '/catalog']];
$category_model = new \App\Models\Category($pdo);
foreach ($category_model->getTree() as $row) $urls[] = ['url' => \App\Helpers\Url::category($row, $pdo), 'modified' => $row['updated_at'] ?: ($row['created_at'] ?? null)];
$ids = $category_model->descendantIds();
$stmt = $pdo->query('SELECT id, category_id, slug, updated_at, created_at FROM products WHERE is_active = TRUE AND category_id IN (' . implode(',', $ids ?: [0]) . ') ORDER BY id');
foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) $urls[] = ['url' => \App\Helpers\Url::product($row, $pdo), 'modified' => $row['updated_at'] ?: $row['created_at']];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $item) {
    echo '<url><loc>' . htmlspecialchars($item['url'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
    if (!empty($item['modified']) && strtotime($item['modified']) !== false) echo '<lastmod>' . date('c', strtotime($item['modified'])) . '</lastmod>';
    echo "</url>\n";
}
echo '</urlset>';
