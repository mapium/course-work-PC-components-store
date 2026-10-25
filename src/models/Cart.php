<?php

namespace App\Models;

use PDO;

class Cart {
    private $conn;
    private $table = 'cart';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function add($user_id, $session_id, $product_id, $quantity) {
        $query = "INSERT INTO " . $this->table . " (user_id, session_id, product_id, quantity)
                 VALUES (:user_id, :session_id, :product_id, :quantity)
                 ON DUPLICATE KEY UPDATE quantity = quantity + :quantity2";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':quantity2', $quantity, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getItems($user_id, $session_id) {
        $query = "SELECT c.*, p.name, p.price, p.slug, pi.image_path as main_image
                 FROM " . $this->table . " c
                 JOIN products p ON c.product_id = p.id
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = TRUE
                 WHERE (c.user_id = :user_id OR c.session_id = :session_id)
                 ORDER BY c.added_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCount($user_id, $session_id) {
        $query = "SELECT SUM(quantity) as count FROM " . $this->table . "
                 WHERE (user_id = :user_id OR session_id = :session_id)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    public function remove($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function update($id, $quantity) {
        $query = "UPDATE " . $this->table . " SET quantity = :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function clear($user_id, $session_id) {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = :user_id OR session_id = :session_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':session_id', $session_id);
        return $stmt->execute();
    }
}
