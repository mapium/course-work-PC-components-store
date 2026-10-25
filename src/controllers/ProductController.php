<?php

namespace App\Controllers;

use App\Models\Product;
use App\Helpers\CSRF;

class ProductController {
    private $db;
    private $product_model;

    public function __construct($db) {
        $this->db = $db;
        $this->product_model = new Product($db);
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
