<?php

namespace App\Models;

use PDO;

class Product {
    private $conn;
    private $table = 'products';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($limit = 20, $offset = 0) {
        $query = "SELECT p.*, pi.image_path as main_image
                  FROM " . $this->table . " p
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = TRUE
                  WHERE p.is_active = TRUE
                  ORDER BY p.created_at DESC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT p.*, pi.image_path as main_image
                  FROM " . $this->table . " p
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = TRUE
                  WHERE p.id = :id AND p.is_active = TRUE";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCategoryId($category_id, $limit = 20, $offset = 0) {
        $query = "SELECT p.*, pi.image_path as main_image
                  FROM " . $this->table . " p
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = TRUE
                  WHERE p.category_id = :category_id AND p.is_active = TRUE
                  ORDER BY p.created_at DESC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($query, $limit = 20, $offset = 0) {
        $sql = "SELECT p.*, pi.image_path as main_image
                FROM " . $this->table . " p
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = TRUE
                WHERE (MATCH(p.name, p.description) AGAINST(:query IN BOOLEAN MODE) OR p.name LIKE :like_query)
                AND p.is_active = TRUE
                LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        $search_query = '%' . $query . '%';
        $stmt->bindParam(':query', $query);
        $stmt->bindParam(':like_query', $search_query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFeatured($limit = 6) {
        $query = "SELECT p.*, pi.image_path as main_image
                  FROM " . $this->table . " p
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = TRUE
                  WHERE p.is_featured = TRUE AND p.is_active = TRUE
                  ORDER BY p.created_at DESC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                 (name, slug, category_id, price, description, brand, stock, sku)
                 VALUES (:name, :slug, :category_id, :price, :description, :brand, :stock, :sku)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':slug', $data['slug']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':description', $data['description'] ?? null);
        $stmt->bindParam(':brand', $data['brand'] ?? null);
        $stmt->bindParam(':stock', $data['stock'] ?? 0);
        $stmt->bindParam(':sku', $data['sku'] ?? null);

        return $stmt->execute();
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET name = :name, price = :price, description = :description, stock = :stock WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':stock', $data['stock']);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "UPDATE " . $this->table . " SET is_active = FALSE WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getSpecifications($product_id) {
        $query = "SELECT * FROM product_specifications WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSimilarProducts($product_id, $category_id, $limit = 4) {
        $query = "SELECT p.*, pi.image_path as main_image
                  FROM " . $this->table . " p
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = TRUE
                  WHERE p.category_id = :category_id
                  AND p.id != :product_id
                  AND p.is_active = TRUE
                  ORDER BY RAND()
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReviews($product_id) {
        $query = "SELECT r.*, u.username, u.full_name
                  FROM reviews r
                  LEFT JOIN users u ON r.user_id = u.id
                  WHERE r.product_id = :product_id
                  AND r.is_approved = TRUE
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getImages($product_id) {
        $query = "SELECT * FROM product_images WHERE product_id = :product_id ORDER BY sort_order";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
