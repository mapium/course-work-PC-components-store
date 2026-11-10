<?php

namespace App\Helpers;

use App\Models\Category;
use App\Models\Product;

class Catalog {
    const SORTS = ['relevance' => 'По соответствию запросу', 'newest' => 'Сначала новые', 'popularity' => 'По популярности', 'price_asc' => 'Сначала дешевле', 'price_desc' => 'Сначала дороже', 'rating' => 'По рейтингу'];
    const PAGE_SIZE = 12;
    const PAGE_SIZES = [12, 24, 36, 48];

    public static function load($pdo, $input, $category = null) {
        $model = new Product($pdo);
        $categories = (new Category($pdo))->getTree();
        $selected = Request::integer($input, 'category');
        $allowed = (new Category($pdo))->descendantIds($category ? $category['id'] : null);
        if (!in_array($selected, $allowed, true)) $selected = $category ? (int)$category['id'] : null;
        $filters = [];
        $params = [];
        if ($selected) {
            $filters['category_id'] = $selected;
            if (!$category || $selected !== (int)$category['id']) $params['category'] = $selected;
        }
        $search = Request::text($input, 'search');
        if (Request::length($search) > 120) $search = iconv_substr($search, 0, 120, 'UTF-8');
        if ($search !== '') {
            $params['search'] = $filters['search'] = $search;
            Search::remember($search);
        }
        foreach (['price_min', 'price_max'] as $field) {
            if (isset($input[$field]) && is_scalar($input[$field]) && is_numeric($input[$field]) && (float)$input[$field] >= 0) {
                $filters[$field] = round(min((float)$input[$field], 99999999.99), 2);
            }
        }
        if (isset($filters['price_min'], $filters['price_max']) && $filters['price_min'] > $filters['price_max']) {
            [$filters['price_min'], $filters['price_max']] = [$filters['price_max'], $filters['price_min']];
        }
        foreach (['price_min', 'price_max'] as $field) if (isset($filters[$field])) $params[$field] = $filters[$field];
        $brands = $model->getAvailableBrands($selected);
        $brand = Request::text($input, 'brand');
        if (in_array($brand, $brands, true)) $params['brand'] = $filters['brand'] = $brand;
        $specs = $selected ? $model->getAvailableSpecifications($selected, $filters['brand'] ?? '') : [];
        foreach (is_array($input['spec'] ?? null) ? $input['spec'] : [] as $name => $values) {
            if (!isset($specs[$name]) || !is_array($values)) continue;
            $values = array_values(array_unique(array_filter($values, function ($value) use ($specs, $name) {
                return is_string($value) && in_array($value, $specs[$name], true);
            })));
            if ($values) $params['spec'][$name] = $filters['specifications'][$name] = $values;
        }
        $sort = Request::text($input, 'sort', $search !== '' ? 'relevance' : 'newest');
        if (!isset(self::SORTS[$sort]) || ($sort === 'relevance' && $search === '')) $sort = 'newest';
        $filters['sort'] = $sort;
        if ($sort !== ($search !== '' ? 'relevance' : 'newest')) $params['sort'] = $sort;
        $per_page = Request::integer($input, 'per_page', self::PAGE_SIZE);
        if (!in_array($per_page, self::PAGE_SIZES, true)) $per_page = self::PAGE_SIZE;
        if ($per_page !== self::PAGE_SIZE) $params['per_page'] = $per_page;
        $total = $model->countWithFilters($filters);
        $pages = max(1, (int)ceil($total / $per_page));
        $page = max(1, min(1000000, Request::integer($input, 'page', 1)));
        $not_found = $page > $pages;
        if ($page > 1) $params['page'] = $page;
        $products = $not_found ? [] : $model->getWithFilters($filters, $per_page, ($page - 1) * $per_page);
        $path = $category ? Url::categoryPath($category, $pdo) : '/catalog';
        $url = APP_URL . $path . ($params ? '?' . http_build_query($params) : '');
        return compact('category', 'categories', 'selected', 'filters', 'params', 'brands', 'specs', 'sort', 'search', 'per_page', 'total', 'pages', 'page', 'products', 'path', 'url', 'not_found');
    }
}
