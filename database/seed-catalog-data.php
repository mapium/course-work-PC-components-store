<?php

// Повторяемое дополнение каталога: существующие товары и категории не изменяются.
return static function (PDO $pdo, array $data, ?string $imageDirectory = null): array {
    if ($pdo->inTransaction()) throw new RuntimeException('Импорт должен запускаться вне другой транзакции');
    $imageDirectory = $imageDirectory ?? dirname(__DIR__) . '/public/images/products';
    if (!isset($data['categories'], $data['products']) || !is_array($data['categories']) || !is_array($data['products']) || !$data['products']) {
        throw new InvalidArgumentException('Набор должен содержать категории и товары');
    }
    $text = static function ($value, int $limit, string $field): string {
        if (!is_string($value) || trim($value) === '' || iconv_strlen($value, 'UTF-8') === false || iconv_strlen($value, 'UTF-8') > $limit) {
            throw new InvalidArgumentException('Некорректное поле: ' . $field);
        }
        return $value;
    };
    $slug = static function ($value, int $limit) use ($text): string {
        $text($value, $limit, 'slug');
        if (preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $value) !== 1) throw new InvalidArgumentException('Некорректный slug');
        return $value;
    };
    $categories = [];
    foreach ($data['categories'] as $category) {
        if (!is_array($category)) throw new InvalidArgumentException('Некорректная категория');
        $key = $slug($category['slug'] ?? null, 100);
        if (isset($categories[$key])) throw new InvalidArgumentException('Повторная категория: ' . $key);
        $text($category['name'] ?? null, 100, 'category.name');
        $text($category['description'] ?? null, 10000, 'category.description');
        if (!is_int($category['sort_order'] ?? 0)) throw new InvalidArgumentException('Некорректный порядок категории');
        if (isset($category['parent_slug'])) $slug($category['parent_slug'], 100);
        $categories[$key] = $category;
    }
    $slugs = $skus = [];
    foreach ($data['products'] as $product) {
        if (!is_array($product)) throw new InvalidArgumentException('Некорректный товар');
        $key = $slug($product['slug'] ?? null, 255);
        $sku = $text($product['sku'] ?? null, 50, 'sku');
        if (isset($slugs[$key]) || isset($skus[$sku])) throw new InvalidArgumentException('Повторный товар: ' . $key);
        $slugs[$key] = $skus[$sku] = true;
        $slug($product['category'] ?? null, 100);
        $text($product['name'] ?? null, 255, 'name');
        $text($product['brand'] ?? null, 100, 'brand');
        $text($product['description'] ?? null, 10000, 'description');
        $text($product['detailed_description'] ?? null, 50000, 'detailed_description');
        foreach (['price', 'old_price'] as $field) {
            $value = $product[$field] ?? null;
            if ($field === 'old_price' && $value === null) continue;
            if ((!is_int($value) && !is_float($value)) || !is_finite((float)$value) || $value <= 0 || $value > 99999999.99) {
                throw new InvalidArgumentException('Некорректная цена: ' . $key);
            }
        }
        if (!is_int($product['stock'] ?? null) || $product['stock'] < 0 || $product['stock'] > 2147483647
            || !in_array($product['is_featured'] ?? 0, [0, 1], true)) throw new InvalidArgumentException('Некорректный остаток или признак товара: ' . $key);
        $ordersCount = $product['orders_count'] ?? 0;
        if (!is_int($ordersCount) || $ordersCount < 0 || $ordersCount > 4294967295) throw new InvalidArgumentException('Некорректное количество заказов: ' . $key);
        if (!is_array($product['images'] ?? null) || !$product['images'] || count($product['images']) > 12) throw new InvalidArgumentException('Отсутствует галерея: ' . $key);
        $imageNames = [];
        foreach ($product['images'] as $image) {
            $name = is_array($image) ? ($image['file'] ?? null) : null;
            if (!is_string($name) || preg_match('/\A[a-z0-9][a-z0-9._-]*\.(jpg|jpeg|png|webp)\z/i', $name, $match) !== 1 || isset($imageNames[$name])) {
                throw new InvalidArgumentException('Некорректное или повторное имя изображения: ' . $key);
            }
            $imageNames[$name] = true;
            $text($image['alt_text'] ?? null, 255, 'image.alt_text');
            $type = ['jpg' => IMAGETYPE_JPEG, 'jpeg' => IMAGETYPE_JPEG, 'png' => IMAGETYPE_PNG, 'webp' => IMAGETYPE_WEBP][strtolower($match[1])];
            foreach (['/' => 1800, '/thumbs/' => 420] as $suffix => $max) {
                $file = $imageDirectory . $suffix . $name;
                $size = is_file($file) ? @getimagesize($file) : false;
                if (!$size || $size[2] !== $type || min($size[0], $size[1]) < 1 || max($size[0], $size[1]) > $max || filesize($file) > 10485760) {
                    throw new RuntimeException('Изображение отсутствует, повреждено или не оптимизировано: ' . $file);
                }
            }
        }
        if (!is_array($product['specifications'] ?? null) || !$product['specifications']) throw new InvalidArgumentException('Отсутствуют характеристики: ' . $key);
        foreach ($product['specifications'] as $name => $value) {
            $text($name, 100, 'spec_name');
            $text($value, 255, 'spec_value');
        }
    }

    $pdo->beginTransaction();
    try {
        $result = ['added' => 0, 'skipped' => 0, 'categories_added' => 0];
        $findCategory = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
        $addCategory = $pdo->prepare('INSERT INTO categories (name, slug, description, parent_id, sort_order, is_visible) VALUES (?, ?, ?, ?, ?, 1)');
        $resolved = $resolving = [];
        $resolve = static function (string $key) use ($pdo, $categories, $findCategory, $addCategory, &$resolved, &$resolving, &$resolve, &$result): int {
            if (isset($resolved[$key])) return $resolved[$key];
            $findCategory->execute([$key]);
            $existing = $findCategory->fetchColumn();
            if ($existing !== false) return $resolved[$key] = (int)$existing;
            if (!isset($categories[$key])) throw new RuntimeException('Категория не найдена: ' . $key);
            if (isset($resolving[$key])) throw new RuntimeException('Цикл категорий: ' . $key);
            $resolving[$key] = true;
            $category = $categories[$key];
            $parent = isset($category['parent_slug']) ? $resolve($category['parent_slug']) : null;
            $addCategory->execute([$category['name'], $key, $category['description'], $parent, $category['sort_order'] ?? 0]);
            $resolved[$key] = (int)$pdo->lastInsertId();
            unset($resolving[$key]);
            $result['categories_added']++;
            return $resolved[$key];
        };
        foreach (array_keys($categories) as $key) $resolve($key);
        $findProduct = $pdo->prepare('SELECT id FROM products WHERE sku = ? OR slug = ?');
        $addProduct = $pdo->prepare('INSERT INTO products (category_id, name, slug, description, detailed_description, price, old_price, stock, brand, sku, meta_title, meta_description, is_featured, orders_count, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)');
        $addImage = $pdo->prepare('INSERT INTO product_images (product_id, image_path, alt_text, is_main, sort_order) VALUES (?, ?, ?, ?, ?)');
        $addSpecification = $pdo->prepare('INSERT INTO product_specifications (product_id, spec_name, spec_value) VALUES (?, ?, ?)');
        foreach ($data['products'] as $product) {
            $findProduct->execute([$product['sku'], $product['slug']]);
            if ($findProduct->fetchColumn() !== false) {
                $result['skipped']++;
                continue;
            }
            $addProduct->execute([
                $resolve($product['category']), $product['name'], $product['slug'], $product['description'], $product['detailed_description'],
                $product['price'], $product['old_price'] ?? null, $product['stock'], $product['brand'], $product['sku'],
                iconv_substr($product['name'] . ' — PCComponents Store', 0, 255, 'UTF-8'), iconv_substr($product['description'], 0, 220, 'UTF-8'), $product['is_featured'] ?? 0, $product['orders_count'] ?? 0
            ]);
            $id = (int)$pdo->lastInsertId();
            foreach (array_values($product['images']) as $order => $image) $addImage->execute([$id, $image['file'], $image['alt_text'], $order === 0 ? 1 : 0, $order]);
            foreach ($product['specifications'] as $name => $value) $addSpecification->execute([$id, $name, $value]);
            $result['added']++;
        }
        $result['active_total'] = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
        $pdo->commit();
        return $result;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
};
