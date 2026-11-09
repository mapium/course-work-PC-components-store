<?php

namespace App\Models;

use PDO;

class Order {
    const STATUSES = [
        'pending' => ['label' => 'Ожидает обработки', 'class' => 'badge-warning'],
        'processing' => ['label' => 'В обработке', 'class' => 'badge-info'],
        'assembled' => ['label' => 'Собран', 'class' => 'badge-secondary'],
        'shipped' => ['label' => 'Отправлен', 'class' => 'badge-secondary'],
        'delivered' => ['label' => 'Доставлен', 'class' => 'badge-success'],
        'cancelled' => ['label' => 'Отменён', 'class' => 'badge-destructive']
    ];

    private $conn;
    private $table = 'orders';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                 (user_id, order_number, total_amount, delivery_address, delivery_method, payment_method, notes)
                 VALUES (:user_id, :order_number, :total_amount, :delivery_address, :delivery_method, :payment_method, :notes)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':order_number', $data['order_number']);
        $stmt->bindParam(':total_amount', $data['total_amount']);
        $stmt->bindParam(':delivery_address', $data['delivery_address']);
        $stmt->bindParam(':delivery_method', $data['delivery_method']);
        $stmt->bindParam(':payment_method', $data['payment_method']);
        $notes = $data['notes'] ?? null;
        $stmt->bindValue(':notes', $notes);

        return $stmt->execute();
    }

    public function getById($id) {
        $query = "SELECT o.*, u.username, u.email FROM " . $this->table . " o
                  LEFT JOIN users u ON o.user_id = u.id WHERE o.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUserId($user_id, $limit = 20, $offset = 0) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserOrders($user_id) {
        return $this->getByUserId($user_id, 100, 0);
    }

    public function getAll($limit = 20, $offset = 0) {
        $query = "SELECT o.*, u.username, u.email FROM " . $this->table . " o
                  LEFT JOIN users u ON o.user_id = u.id
                  ORDER BY o.created_at DESC, o.id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        if (!isset(self::STATUSES[$status])) {
            throw new \InvalidArgumentException('Недопустимый статус заказа');
        }
        $query = "UPDATE " . $this->table . " SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    public function getItems($order_id) {
        $query = "SELECT oi.*, p.name, p.slug, p.category_id,
                    (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = oi.product_id
                     ORDER BY pi.is_main DESC, pi.sort_order, pi.id LIMIT 1) AS main_image
                  FROM order_items oi
                  LEFT JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = :order_id ORDER BY oi.id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function statusInfo($status) {
        return self::STATUSES[$status] ?? ['label' => $status, 'class' => ''];
    }

    public function getSalesReport($period = 'month') {
        if ($period === 'month') {
            $date_filter = "DATE_FORMAT(created_at, '%Y-%m')";
        } else {
            $date_filter = "YEAR(created_at)";
        }

        $query = "SELECT $date_filter as period, COUNT(*) as orders_count, SUM(total_amount) as total_sales
                 FROM " . $this->table . "
                 WHERE status != 'cancelled'
                 GROUP BY $date_filter
                 ORDER BY period DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
