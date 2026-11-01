<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Cart as CartModel;
use App\Helpers\Auth;
use App\Helpers\CSRF;

class CartController {
    private $db;
    private $cart_model;

    public function __construct($db) {
        $this->db = $db;
        $this->cart_model = new CartModel($db);
    }

    public function add() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            return;
        }

        // Поддержка как JSON, так и FormData
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($content_type, 'application/json') !== false) {
            $data = json_decode(file_get_contents("php://input"), true);
        } else {
            $data = $_POST;
        }

        $product_id = $data['product_id'] ?? null;
        $quantity = $data['quantity'] ?? 1;
        $user_id = Auth::getCurrentUserId();
        $session_id = session_id();

        if (!$product_id) {
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            return;
        }

        $product_model = new Product($this->db);
        $product = $product_model->getById($product_id);

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        if ($quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock']);
            return;
        }

        try {
            $this->cart_model->add($user_id, $session_id, $product_id, $quantity);
            echo json_encode(['success' => true, 'message' => 'Product added to cart']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error adding product']);
        }
    }

    public function getItems() {
        header('Content-Type: application/json');

        $user_id = Auth::getCurrentUserId();
        $session_id = session_id();

        $items = $this->cart_model->getItems($user_id, $session_id);
        echo json_encode(['success' => true, 'items' => $items]);
    }

    public function getCount() {
        header('Content-Type: application/json');

        $user_id = Auth::getCurrentUserId();
        $session_id = session_id();

        $count = $this->cart_model->getCount($user_id, $session_id);
        echo json_encode(['count' => $count]);
    }

    public function getSummary() {
        header('Content-Type: application/json');

        $user_id = Auth::getCurrentUserId();
        $session_id = session_id();

        $items = $this->cart_model->getItems($user_id, $session_id);
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shipping = 300;
        $total = $subtotal + $shipping;

        echo json_encode([
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total
        ]);
    }

    public function remove() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            return;
        }

        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID required']);
            return;
        }

        try {
            $this->cart_model->remove($id);
            echo json_encode(['success' => true, 'message' => 'Product removed from cart']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error removing product']);
        }
    }

    public function update() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        // Получаем ID из URL параметра (установлен в index.php)
        $cart_id = $_GET['id'] ?? null;
        $quantity = $data['quantity'] ?? 0;

        if (!$cart_id || $quantity < 1) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            return;
        }

        try {
            $this->cart_model->update($cart_id, $quantity);
            echo json_encode(['success' => true, 'message' => 'Cart updated']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error updating cart']);
        }
    }
}
