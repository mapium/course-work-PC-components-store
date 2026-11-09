<?php
header('Content-Type: text/plain; charset=utf-8');
$base = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?: '', '/');
echo "User-agent: *\nAllow: " . $base . "/\n";
foreach (['/admin', '/account', '/api/', '/cart', '/checkout', '/auth', '/logout', '/order-success', '/src/', '/database/', '/tests/'] as $path) echo 'Disallow: ' . $base . $path . "\n";
echo 'Sitemap: ' . APP_URL . "/sitemap.xml\n";
