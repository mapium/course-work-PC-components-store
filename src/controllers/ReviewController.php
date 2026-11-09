<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Request;
use App\Helpers\Response;

class ReviewController {
    private $pdo;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: (new Database())->connect();
    }

    private function validateRequest($redirect) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::send(['success' => false, 'message' => 'Недопустимый метод запроса'], 405, $redirect);
            return false;
        }
        if (!Auth::isLoggedIn()) {
            Response::send(['success' => false, 'message' => 'Необходимо авторизоваться'], 401, $redirect);
            return false;
        }
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            Response::send(['success' => false, 'message' => 'Ошибка безопасности. Обновите страницу'], 403, $redirect);
            return false;
        }
        return true;
    }

    public function addReview($redirect = null) {
        if (!$this->validateRequest($redirect)) {
            return;
        }
        $product_id = Request::integer($_POST, 'product_id');
        $rating = Request::integer($_POST, 'rating');
        $comment = Request::text($_POST, 'comment');
        $parent_id = Request::integer($_POST, 'parent_id') ?: null;
        if ($product_id <= 0) {
            Response::send(['success' => false, 'message' => 'Неверный ID товара'], 400, $redirect);
            return;
        }
        if ($parent_id === null && ($rating < 1 || $rating > 5)) {
            Response::send(['success' => false, 'message' => 'Рейтинг должен быть от 1 до 5', 'errors' => ['rating' => 'Выберите оценку от 1 до 5']], 400, $redirect);
            return;
        }
        if ($comment === '' || Request::length($comment) > 10000) {
            Response::send(['success' => false, 'message' => 'Комментарий должен содержать от 1 до 10000 символов', 'errors' => ['comment' => 'Введите комментарий от 1 до 10000 символов']], 400, $redirect);
            return;
        }

        try {
            if ($parent_id !== null) {
                $stmt = $this->pdo->prepare("SELECT id FROM reviews WHERE id = :parent_id
                    AND product_id = :product_id AND parent_id IS NULL AND is_approved = 1");
                $stmt->execute([':parent_id' => $parent_id, ':product_id' => $product_id]);
                if (!$stmt->fetch()) {
                    Response::send(['success' => false, 'message' => 'Отзыв для ответа не найден'], 404, $redirect);
                    return;
                }
                // Оценка ответа не участвует в рейтинге товара.
                $rating = 5;
            } else {
                $stmt = $this->pdo->prepare("SELECT id FROM reviews WHERE product_id = :product_id
                    AND user_id = :user_id AND parent_id IS NULL");
                $stmt->execute([':product_id' => $product_id, ':user_id' => Auth::getUserId()]);
                if ($stmt->fetch()) {
                    Response::send(['success' => false, 'message' => 'Вы уже оставили отзыв на этот товар'], 400, $redirect);
                    return;
                }
            }
            $stmt = $this->pdo->prepare("INSERT INTO reviews
                (product_id, user_id, parent_id, rating, comment, is_approved, created_at)
                VALUES (:product_id, :user_id, :parent_id, :rating, :comment, 1, NOW())");
            $stmt->execute([
                ':product_id' => $product_id, ':user_id' => Auth::getUserId(), ':parent_id' => $parent_id,
                ':rating' => $rating, ':comment' => $comment
            ]);
            Response::send(['success' => true, 'message' => $parent_id === null ? 'Отзыв успешно добавлен' : 'Ответ успешно добавлен'], 200, $redirect);
        } catch (\PDOException $exception) {
            Response::send(['success' => false, 'message' => 'Ошибка при добавлении отзыва'], 500, $redirect);
        }
    }

    public function updateReview($redirect = null) {
        if (!$this->validateRequest($redirect)) {
            return;
        }
        $review_id = Request::integer($_POST, 'review_id');
        $rating = Request::integer($_POST, 'rating');
        $comment = Request::text($_POST, 'comment');
        if ($review_id <= 0) {
            Response::send(['success' => false, 'message' => 'Неверный ID отзыва'], 400, $redirect);
            return;
        }
        if ($comment === '' || Request::length($comment) > 10000) {
            Response::send(['success' => false, 'message' => 'Комментарий должен содержать от 1 до 10000 символов', 'errors' => ['comment' => 'Введите комментарий от 1 до 10000 символов']], 400, $redirect);
            return;
        }
        try {
            $stmt = $this->pdo->prepare("SELECT user_id, product_id, parent_id, rating FROM reviews WHERE id = :id");
            $stmt->execute([':id' => $review_id]);
            $review = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$review || (Request::integer($_POST, 'product_id') > 0 && (int)$review['product_id'] !== Request::integer($_POST, 'product_id'))) {
                Response::send(['success' => false, 'message' => 'Отзыв не найден'], 404, $redirect);
                return;
            }
            if ($review['user_id'] != Auth::getUserId()) {
                Response::send(['success' => false, 'message' => 'Недостаточно прав'], 403, $redirect);
                return;
            }
            if ($review['parent_id'] !== null) {
                // Тип записи определяет PHP по данным БД, а не клиентское поле is_reply.
                $rating = (int)$review['rating'];
            } elseif ($rating < 1 || $rating > 5) {
                Response::send(['success' => false, 'message' => 'Рейтинг должен быть от 1 до 5', 'errors' => ['rating' => 'Выберите оценку от 1 до 5']], 400, $redirect);
                return;
            }
            $stmt = $this->pdo->prepare("UPDATE reviews SET rating = :rating, comment = :comment,
                updated_at = NOW() WHERE id = :id");
            $stmt->execute([':rating' => $rating, ':comment' => $comment, ':id' => $review_id]);
            Response::send(['success' => true, 'message' => 'Отзыв обновлён'], 200, $redirect);
        } catch (\PDOException $exception) {
            Response::send(['success' => false, 'message' => 'Ошибка при обновлении отзыва'], 500, $redirect);
        }
    }

    public function deleteReview($redirect = null) {
        if (!$this->validateRequest($redirect)) {
            return;
        }
        $review_id = Request::integer($_POST, 'review_id');
        try {
            $stmt = $this->pdo->prepare("SELECT user_id, product_id FROM reviews WHERE id = :id");
            $stmt->execute([':id' => $review_id]);
            $review = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$review || (Request::integer($_POST, 'product_id') > 0 && (int)$review['product_id'] !== Request::integer($_POST, 'product_id'))) {
                Response::send(['success' => false, 'message' => 'Отзыв не найден'], 404, $redirect);
                return;
            }
            if ($review['user_id'] != Auth::getUserId() && !Auth::canManageStore()) {
                Response::send(['success' => false, 'message' => 'Недостаточно прав'], 403, $redirect);
                return;
            }
            $stmt = $this->pdo->prepare("DELETE FROM reviews WHERE id = :id");
            $stmt->execute([':id' => $review_id]);
            Response::send(['success' => true, 'message' => 'Отзыв удалён'], 200, $redirect);
        } catch (\PDOException $exception) {
            Response::send(['success' => false, 'message' => 'Ошибка при удалении отзыва'], 500, $redirect);
        }
    }
}
