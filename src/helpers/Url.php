<?php

namespace App\Helpers;

use App\Models\Category;

class Url {
    private static $trees;

    public static function clear($pdo) {
        if (self::$trees !== null) unset(self::$trees[$pdo]);
    }

    private static function categories($pdo) {
        if (self::$trees === null) self::$trees = new \WeakMap();
        if (!isset(self::$trees[$pdo])) {
            $rows = [];
            foreach ((new Category($pdo))->getTree(true) as $row) $rows[$row['id']] = $row;
            self::$trees[$pdo] = $rows;
        }
        return self::$trees[$pdo];
    }

    public static function categoryPath($category, $pdo) {
        $row = self::categories($pdo)[$category['id']] ?? $category;
        return '/category/' . ($row['path'] ?? $row['slug']);
    }

    public static function productPath($product, $pdo) {
        $category = self::categories($pdo)[$product['category_id']] ?? null;
        return '/product/' . ($category ? $category['path'] . '/' : '') . $product['slug'];
    }

    public static function product($product, $pdo) {
        return APP_URL . self::productPath($product, $pdo);
    }

    public static function category($category, $pdo) {
        return APP_URL . self::categoryPath($category, $pdo);
    }
}
