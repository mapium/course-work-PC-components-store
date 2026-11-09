<?php

namespace App\Helpers;

use App\Models\Product;
use App\Models\Category;

class Search {
    public static function results($pdo, $query) {
        $query = trim(preg_replace('/\s+/u', ' ', $query));
        if ($query === '' || preg_match('/\A.{1,120}\z/us', $query) !== 1) {
            return ['products' => [], 'categories' => []];
        }
        $terms = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
        $categories = array_filter((new Category($pdo))->getTree(), function ($category) use ($terms) {
            foreach ($terms as $term) {
                if (preg_match('/' . preg_quote($term, '/') . '/iu', $category['name'] . ' ' . $category['slug']) !== 1) {
                    return false;
                }
            }
            return true;
        });
        return ['products' => (new Product($pdo))->search($query, 8), 'categories' => array_values($categories)];
    }

    public static function remember($query) {
        if ($query === '' || Request::length($query) > 120) {
            return;
        }
        $history = array_filter($_SESSION['search_history'] ?? [], function ($item) use ($query) {
            return is_string($item) && strcasecmp($item, $query) !== 0;
        });
        $_SESSION['search_history'] = array_slice(array_merge([$query], array_values($history)), 0, 5);
    }

    public static function suggestions() {
        return [
            ['title' => 'Видеокарты RTX', 'icon' => 'videogame_asset'],
            ['title' => 'Процессоры Ryzen', 'icon' => 'memory'],
            ['title' => 'Оперативная память DDR5', 'icon' => 'dns'],
            ['title' => 'SSD NVMe', 'icon' => 'storage']
        ];
    }
}
