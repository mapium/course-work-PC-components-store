<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Helpers\CSRF;

class ProductController {
    private $db;
    private $product_model;

    public function __construct($db) {
        $this->db = $db;
        $this->product_model = new Product($db);
    }

    public function search() {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $query = $_GET['q'] ?? '';

        if (!is_string($query) || preg_match('/\A.{0,120}\z/us', $query) !== 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Запрос должен содержать не более 120 символов'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $query = trim(preg_replace('/\s+/u', ' ', $query));
        if ($query === '') {
            echo json_encode(['success' => true, 'products' => [], 'categories' => []]);
            return;
        }

        try {
            $products = array_map(function ($product) {
                return array_intersect_key($product, array_flip([
                    'id', 'name', 'price', 'brand', 'stock', 'main_image', 'category_name'
                ]));
            }, $this->product_model->search($query, 8));

            $terms = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
            $categories = array_filter((new Category($this->db))->getAll(), function ($category) use ($terms) {
                foreach ($terms as $term) {
                    if (preg_match('/' . preg_quote($term, '/') . '/iu', $category['name'] . ' ' . $category['slug']) !== 1) {
                        return false;
                    }
                }
                return true;
            });
            $categories = array_map(function ($category) {
                return array_intersect_key($category, array_flip(['id', 'name', 'slug']));
            }, array_values($categories));

            echo json_encode([
                'success' => true,
                'products' => $products,
                'categories' => $categories
            ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\PDOException $exception) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Поиск временно недоступен'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function create() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            return;
        }
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }
        $data = [
            'name' => $_POST['name'] ?? '',
            'slug' => $_POST['slug'] ?? $this->generateSlug($_POST['name'] ?? ''),
            'category_id' => $_POST['category_id'] ?? 1,
            'price' => $_POST['price'] ?? 0,
            'old_price' => !empty($_POST['old_price']) ? $_POST['old_price'] : null,
            'description' => $_POST['description'] ?? '',
            'detailed_description' => $_POST['detailed_description'] ?? '',
            'brand' => $_POST['brand'] ?? '',
            'stock' => $_POST['stock'] ?? 0,
            'sku' => $_POST['sku'] ?? ''
        ];
        if (empty($data['name']) || empty($data['price'])) {
            echo json_encode(['success' => false, 'message' => 'Название и цена обязательны']);
            return;
        }
        $result = $this->product_model->create($data);
        if ($result) {
            echo json_encode(['success' => true, 'product_id' => $this->db->lastInsertId()]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка']);
        }
    }

    public function delete() {
        header('Content-Type: application/json');
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false]);
            return;
        }
        $result = $this->product_model->delete($_POST['id'] ?? 0);
        echo json_encode(['success' => $result]);
    }

    private function generateSlug($name) {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        return preg_replace('/-+/', '-', trim($slug, '-'));
    }
}
