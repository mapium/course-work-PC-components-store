<?php

namespace App\Helpers;

use App\Models\Product;
use App\Models\ProductView;

class BrowsingHistory {
    const SESSION_KEY = 'viewed_products';
    const PROFILE_LIMIT = 12;

    public static function remember($pdo, $product_id) {
        $product_id = (int)$product_id;
        if ($product_id <= 0) return;
        $viewed_at = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s.u');
        if (Auth::isLoggedIn()) {
            (new ProductView($pdo))->record(Auth::getCurrentUserId(), $product_id, $viewed_at);
            return;
        }
        $entries = self::guestEntries();
        unset($entries[$product_id]);
        $_SESSION[self::SESSION_KEY] = array_slice([$product_id => $viewed_at] + $entries, 0, ProductView::MAX_ITEMS, true);
    }

    public static function recentIds($pdo, $limit = ProductView::MAX_ITEMS) {
        $limit = max(1, min(ProductView::MAX_ITEMS, (int)$limit));
        if (Auth::isLoggedIn()) {
            return (new ProductView($pdo))->getRecentIds(Auth::getCurrentUserId(), $limit);
        }
        $products = (new Product($pdo))->getByIds(array_keys(self::guestEntries()));
        return array_slice(array_map('intval', array_column($products, 'id')), 0, $limit);
    }

    public static function mergeGuest($pdo, $user_id) {
        if ((int)$user_id <= 0) return;
        (new ProductView($pdo))->recordMany($user_id, self::guestEntries());
        unset($_SESSION[self::SESSION_KEY]);
    }

    private static function guestEntries() {
        $entries = [];
        foreach (is_array($_SESSION[self::SESSION_KEY] ?? null) ? $_SESSION[self::SESSION_KEY] : [] as $id => $viewed_at) {
            if (filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false || !is_string($viewed_at)) continue;
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s.u', $viewed_at, new \DateTimeZone('UTC'));
            if ($date && $date->format('Y-m-d H:i:s.u') === $viewed_at) $entries[(int)$id] = $viewed_at;
        }
        arsort($entries, SORT_STRING);
        return array_slice($entries, 0, ProductView::MAX_ITEMS, true);
    }
}
