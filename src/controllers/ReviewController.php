<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Auth;
use App\Helpers\CSRF;

class ReviewController
{
    private $pdo;

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->connect();
    }

    public function addReview()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        // Проверка авторизации
        if (!Auth::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Необходимо авторизоваться']);
            return;
        }

        // Проверка CSRF токена
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $user_id = Auth::getUserId();
        $product_id = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

        // Валидация
        if ($product_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
            return;
        }

        // Для ответов на отзывы рейтинг не обязателен
        if ($parent_id === null && ($rating < 1 || $rating > 5)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Рейтинг должен быть от 1 до 5']);
            return;
        }

        // Для ответов устанавливаем рейтинг по умолчанию
        if ($parent_id !== null && ($rating < 1 || $rating > 5)) {
            $rating = 5;
        }

        if (empty($comment)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Комментарий не может быть пустым']);
            return;
        }

        // Добавление отзыва
        try {
            if ($parent_id !== null) {
                // Ответ должен относиться к опубликованному отзыву этого товара.
                $stmt = $this->pdo->prepare("SELECT id FROM reviews
                    WHERE id = :parent_id AND product_id = :product_id
                    AND parent_id IS NULL AND is_approved = 1");
                $stmt->execute([':parent_id' => $parent_id, ':product_id' => $product_id]);
                if (!$stmt->fetch()) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Отзыв для ответа не найден']);
                    return;
                }
            } else {
                // Ограничение одного отзыва не распространяется на ответы.
                $stmt = $this->pdo->prepare("SELECT id FROM reviews WHERE product_id = :product_id AND user_id = :user_id AND parent_id IS NULL");
                $stmt->execute([':product_id' => $product_id, ':user_id' => $user_id]);
                if ($stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Вы уже оставили отзыв на этот товар']);
                    return;
                }
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO reviews (product_id, user_id, parent_id, rating, comment, is_approved, created_at)
                VALUES (:product_id, :user_id, :parent_id, :rating, :comment, 1, NOW())
            ");

            $stmt->execute([
                ':product_id' => $product_id,
                ':user_id' => $user_id,
                ':parent_id' => $parent_id,
                ':rating' => $rating,
                ':comment' => $comment
            ]);

            echo json_encode(['success' => true, 'message' => 'Отзыв успешно добавлен']);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении отзыва']);
        }
    }

    public function updateReview()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        // Проверка авторизации
        if (!Auth::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Необходимо авторизоваться']);
            return;
        }

        // Проверка CSRF токена
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $review_id = (int)($_POST['review_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        $user_id = Auth::getUserId();

        // Валидация
        if ($review_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Неверный ID отзыва']);
            return;
        }

        if (empty($comment)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Комментарий не может быть пустым']);
            return;
        }

        // Проверка, что отзыв принадлежит пользователю
        try {
            $stmt = $this->pdo->prepare("SELECT user_id, parent_id, rating FROM reviews WHERE id = :id");
            $stmt->execute([':id' => $review_id]);
            $review = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$review) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Отзыв не найден']);
                return;
            }

            if ($review['user_id'] != $user_id) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Недостаточно прав']);
                return;
            }

            if ($review['parent_id'] !== null) {
                // Тип записи определяем по БД, а не по полям, присланным клиентом.
                $rating = (int)$review['rating'];
            } elseif ($rating < 1 || $rating > 5) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Рейтинг должен быть от 1 до 5']);
                return;
            }

            // Обновление отзыва
            $stmt = $this->pdo->prepare("
                UPDATE reviews
                SET rating = :rating, comment = :comment, updated_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                ':rating' => $rating,
                ':comment' => $comment,
                ':id' => $review_id
            ]);

            echo json_encode(['success' => true, 'message' => 'Отзыв обновлен']);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении отзыва']);
        }
    }

    public function deleteReview()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        // Проверка авторизации
        if (!Auth::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Необходимо авторизоваться']);
            return;
        }

        // Проверка CSRF токена
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $review_id = (int)($_POST['review_id'] ?? 0);
        $user_id = Auth::getUserId();
        $user_role = Auth::getUserRole();

        // Проверка, что отзыв принадлежит пользователю или пользователь - админ/менеджер
        try {
            $stmt = $this->pdo->prepare("SELECT user_id FROM reviews WHERE id = :id");
            $stmt->execute([':id' => $review_id]);
            $review = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$review) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Отзыв не найден']);
                return;
            }

            $can_delete = ($review['user_id'] == $user_id) || in_array($user_role, ['admin', 'manager']);

            if (!$can_delete) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Недостаточно прав']);
                return;
            }

            // Удаление отзыва
            $stmt = $this->pdo->prepare("DELETE FROM reviews WHERE id = :id");
            $stmt->execute([':id' => $review_id]);

            echo json_encode(['success' => true, 'message' => 'Отзыв удален']);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Ошибка при удалении отзыва']);
        }
    }
}
