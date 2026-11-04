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
        return $this->getWithFilters(['search' => $query], $limit, $offset);
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
        // Получаем основные отзывы (не ответы)
        $query = "SELECT r.*, u.username, u.full_name
                  FROM reviews r
                  LEFT JOIN users u ON r.user_id = u.id
                  WHERE r.product_id = :product_id
                  AND r.is_approved = TRUE
                  AND r.parent_id IS NULL
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Для каждого отзыва получаем ответы
        foreach ($reviews as &$review) {
            $query_replies = "SELECT r.*, u.username, u.full_name
                             FROM reviews r
                             LEFT JOIN users u ON r.user_id = u.id
                             WHERE r.parent_id = :parent_id
                             AND r.is_approved = TRUE
                             ORDER BY r.created_at ASC";
            $stmt_replies = $this->conn->prepare($query_replies);
            $stmt_replies->bindParam(':parent_id', $review['id'], PDO::PARAM_INT);
            $stmt_replies->execute();
            $review['replies'] = $stmt_replies->fetchAll(PDO::FETCH_ASSOC);
        }

        return $reviews;
    }

    public function getImages($product_id) {
        $query = "SELECT * FROM product_images WHERE product_id = :product_id ORDER BY sort_order";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWithFilters($filters = [], $limit = 100, $offset = 0) {
        $query = "SELECT DISTINCT p.*, pi.image_path as main_image, c.name as category_name
                  FROM " . $this->table . " p
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = TRUE
                  LEFT JOIN categories c ON p.category_id = c.id";

        $params = [];
        $joinIndex = 0;

        if (!empty($filters['specifications']) && is_array($filters['specifications'])) {
            foreach ($filters['specifications'] as $spec_name => $spec_values) {
                if (!empty($spec_values) && is_array($spec_values)) {
                    $alias = "ps" . $joinIndex;
                    $placeholders = [];

                    foreach ($spec_values as $idx => $value) {
                        $placeholder = ":{$alias}_val_{$idx}";
                        $placeholders[] = $placeholder;
                        $params[$placeholder] = $value;
                    }

                    $in_clause = implode(', ', $placeholders);
                    $query .= " INNER JOIN product_specifications {$alias}
                               ON p.id = {$alias}.product_id
                               AND {$alias}.spec_name = :{$alias}_name
                               AND {$alias}.spec_value IN ({$in_clause})";

                    $params[":{$alias}_name"] = $spec_name;
                    $joinIndex++;
                }
            }
        }

        $where_clauses = ["p.is_active = TRUE"];

        $search = is_string($filters['search'] ?? null) ? trim($filters['search']) : '';
        if ($search !== '') {
            $terms = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY);
            if ($terms === false || strlen($search) > 480) {
                return [];
            }

            foreach ($terms as $index => $term) {
                // Treat SQL wildcard characters as literal search text.
                $pattern = '%' . strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
                $matches = [];
                foreach (['name', 'description', 'brand', 'sku'] as $field) {
                    $placeholder = ":search_{$index}_{$field}";
                    $matches[] = "p.{$field} LIKE {$placeholder} ESCAPE '!'";
                    $params[$placeholder] = $pattern;
                }

                $matches[] = "c.name LIKE :search_{$index}_category ESCAPE '!'";
                $params[":search_{$index}_category"] = $pattern;
                $matches[] = "EXISTS (
                    SELECT 1 FROM product_specifications search_spec
                    WHERE search_spec.product_id = p.id
                    AND CONCAT(search_spec.spec_name, ' ', search_spec.spec_value)
                        LIKE :search_{$index}_specification ESCAPE '!'
                )";
                $params[":search_{$index}_specification"] = $pattern;
                $where_clauses[] = '(' . implode(' OR ', $matches) . ')';
            }
        }

        if (!empty($filters['category_id'])) {
            $where_clauses[] = "p.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        if (!empty($filters['brand'])) {
            $where_clauses[] = "p.brand = :brand";
            $params[':brand'] = $filters['brand'];
        }

        if (!empty($filters['price_min']) && is_numeric($filters['price_min'])) {
            $where_clauses[] = "p.price >= :price_min";
            $params[':price_min'] = $filters['price_min'];
        }

        if (!empty($filters['price_max']) && is_numeric($filters['price_max'])) {
            $where_clauses[] = "p.price <= :price_max";
            $params[':price_max'] = $filters['price_max'];
        }

        if (!empty($where_clauses)) {
            $query .= " WHERE " . implode(' AND ', $where_clauses);
        }

        if ($search !== '') {
            $params[':search_exact'] = $search;
            $params[':search_prefix'] = strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $query .= " ORDER BY (p.name = :search_exact) DESC,
                       (p.name LIKE :search_prefix ESCAPE '!') DESC, p.created_at DESC, p.id DESC";
        } else {
            $query .= " ORDER BY p.created_at DESC";
        }
        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableSpecifications($category_id) {
        $query = "SELECT DISTINCT ps.spec_name, ps.spec_value
                  FROM product_specifications ps
                  INNER JOIN products p ON ps.product_id = p.id
                  WHERE p.category_id = :category_id
                  AND p.is_active = TRUE
                  ORDER BY ps.spec_name, ps.spec_value";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $specifications = [];
        foreach ($results as $row) {
            $spec_name = $row['spec_name'];
            $spec_value = $row['spec_value'];

            if (!isset($specifications[$spec_name])) {
                $specifications[$spec_name] = [];
            }

            if (!in_array($spec_value, $specifications[$spec_name])) {
                $specifications[$spec_name][] = $spec_value;
            }
        }

        return $specifications;
    }

    public function getAvailableBrands($category_id = null) {
        $query = "SELECT DISTINCT brand
                  FROM " . $this->table . "
                  WHERE is_active = TRUE
                  AND brand IS NOT NULL
                  AND brand != ''";

        $params = [];

        if ($category_id !== null) {
            $query .= " AND category_id = :category_id";
            $params[':category_id'] = $category_id;
        }

        $query .= " ORDER BY brand";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }

        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($results, 'brand');
    }
}
