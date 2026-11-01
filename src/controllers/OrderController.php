<?php

namespace App\Controllers;

use App\Models\Order;
use App\Models\Cart as CartModel;
use App\Helpers\Auth;

class OrderController {
    private $db;
    private $order_model;

    public function __construct($db) {
        $this->db = $db;
        $this->order_model = new Order($db);
    }

    public function create() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            return;
        }

        $user_id = Auth::getCurrentUserId();
        $session_id = session_id();

        // Получаем данные из POST
        $name = $_POST['name'] ?? null;
        $email = $_POST['email'] ?? null;
        $phone = $_POST['phone'] ?? null;
        $city = $_POST['city'] ?? null;
        $postal_code = $_POST['postal_code'] ?? null;
        $address = $_POST['address'] ?? null;
        $comment = $_POST['comment'] ?? null;
        $payment_method = $_POST['payment_method'] ?? 'card';

        // Валидация
        if (!$name || !$email || !$phone || !$city || !$address) {
            echo json_encode(['success' => false, 'message' => 'Пожалуйста, заполните все обязательные поля']);
            return;
        }

        // Получаем корзину
        $cart_model = new CartModel($this->db);
        $items = $cart_model->getItems($user_id, $session_id);

        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => 'Корзина пуста']);
            return;
        }

        // Подсчитываем сумму
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $shipping = 300;
        $total = $subtotal + $shipping;

        // Генерируем номер заказа
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

        try {
            $this->db->beginTransaction();

            // Создаем заказ
            $full_address = $city . ', ' . $address;
            if ($postal_code) {
                $full_address = $postal_code . ', ' . $full_address;
            }

            $order_data = [
                'user_id' => $user_id,
                'order_number' => $order_number,
                'total_amount' => $total,
                'delivery_address' => $full_address,
                'delivery_method' => 'courier',
                'payment_method' => $payment_method
            ];

            $this->order_model->create($order_data);
            $order_id = $this->db->lastInsertId();

            // Добавляем товары в заказ
            $stmt = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($items as $item) {
                $item_total = $item['price'] * $item['quantity'];
                $stmt->execute([
                    $order_id,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                    $item_total
                ]);
            }

            // Очищаем корзину
            $cart_model->clear($user_id, $session_id);

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Заказ успешно оформлен',
                'order_id' => $order_id,
                'order_number' => $order_number
            ]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Ошибка при создании заказа: ' . $e->getMessage()]);
        }
    }

    public function getById() {
        header('Content-Type: application/json');

        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID required']);
            return;
        }

        $order = $this->order_model->getById($id);

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }

        $items = $this->order_model->getItems($id);

        echo json_encode([
            'success' => true,
            'order' => $order,
            'items' => $items
        ]);
    }

    public function getUserOrders() {
        header('Content-Type: application/json');

        $user_id = Auth::getCurrentUserId();

        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $orders = $this->order_model->getByUserId($user_id);

        echo json_encode([
            'success' => true,
            'orders' => $orders
        ]);
    }
}
