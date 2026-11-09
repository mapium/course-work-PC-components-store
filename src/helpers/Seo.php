<?php

namespace App\Helpers;

class Seo {
    public static function text($text, $limit = 170) {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text ?? '')));
        return Request::length($text) > $limit ? rtrim(iconv_substr($text, 0, $limit - 1, 'UTF-8')) . '…' : $text;
    }

    public static function productDescription($product) {
        if (trim($product['description'] ?? '') !== '') return $product['description'];
        return $product['name'] . (!empty($product['brand']) ? ' от ' . $product['brand'] : '') . '. Характеристики, фотографии и отзывы покупателей. Заказ в PCComponents Store с доставкой.';
    }

    public static function categoryDescription($category) {
        return trim($category['description'] ?? '') !== '' ? $category['description'] : $category['name'] . ' в PCComponents Store. Сравните цены и характеристики, посмотрите фотографии и отзывы, выберите подходящие комплектующие для вашего ПК.';
    }

    public static function breadcrumbs($items) {
        $list = [];
        foreach ($items as $item) $list[] = ['@type' => 'ListItem', 'position' => count($list) + 1, 'name' => $item['name'], 'item' => $item['url']];
        return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list];
    }

    public static function render($data = []) {
        $path = parse_url(Request::currentPath(), PHP_URL_PATH) ?: '/';
        $services = ['/cart' => 'Корзина покупок', '/checkout' => 'Оформление заказа', '/auth' => 'Вход и регистрация',
            '/account' => 'Личный кабинет', '/admin' => 'Панель управления', '/order-success' => 'Заказ оформлен'];
        $data += [
            'title' => ($services[$path] ?? 'Комплектующие для ПК') . ' — PCComponents Store',
            'description' => isset($services[$path]) ? $services[$path] . ' в PCComponents Store.' : 'Комплектующие для ПК: процессоры, видеокарты, материнские платы, память и накопители. Цены, характеристики и отзывы в PCComponents Store.',
            'url' => APP_URL . $path, 'image' => APP_URL . '/public/images/banner2.png',
            'type' => 'website', 'noindex' => isset($services[$path]), 'schemas' => []
        ];
        $escape = function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
        echo '<title data-store-meta>' . $escape($data['title']) . "</title>\n";
        echo '<link data-store-meta rel="canonical" href="' . $escape($data['url']) . '">' . "\n";
        $tags = ['description' => $data['description'], 'robots' => $data['noindex'] ? 'noindex, follow' : 'index, follow, max-image-preview:large',
            'twitter:card' => 'summary_large_image', 'twitter:title' => $data['title'], 'twitter:description' => $data['description'], 'twitter:image' => $data['image']];
        if (!empty($data['keywords'])) $tags['keywords'] = $data['keywords'];
        foreach ($tags as $name => $value) echo '<meta data-store-meta name="' . $name . '" content="' . $escape($value) . '">' . "\n";
        foreach (['type' => $data['type'], 'locale' => 'ru_RU', 'title' => $data['title'], 'description' => $data['description'], 'url' => $data['url'], 'image' => $data['image'], 'site_name' => 'PCComponents Store'] as $name => $value) {
            echo '<meta data-store-meta property="og:' . $name . '" content="' . $escape($value) . '">' . "\n";
        }
        if (isset($data['price'])) {
            echo '<meta data-store-meta property="product:price:amount" content="' . $escape($data['price']) . '"><meta data-store-meta property="product:price:currency" content="RUB">' . "\n";
        }
        foreach (['prev', 'next'] as $rel) if (!empty($data[$rel])) echo '<link data-store-meta rel="' . $rel . '" href="' . $escape($data[$rel]) . '">' . "\n";
        foreach ($data['schemas'] as $schema) {
            echo '<script data-store-meta type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . "</script>\n";
        }
    }
}
