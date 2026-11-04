<?php
// Run with PDO SQLite enabled. Uses only an in-memory database, never the store DB.
require_once dirname(__DIR__) . '/src/helpers/Auth.php';
require_once dirname(__DIR__) . '/src/helpers/CSRF.php';
require_once dirname(__DIR__) . '/src/controllers/ReviewController.php';
require_once dirname(__DIR__) . '/src/models/Product.php';

$pdo = method_exists(PDO::class, 'connect') ? PDO::connect('sqlite::memory:') : new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// PHP 8.4+ exposes driver methods through Pdo\Sqlite; older PHP uses PDO's prefixed methods.
$createFunction = method_exists($pdo, 'createFunction') ? 'createFunction' : 'sqliteCreateFunction';
$pdo->$createFunction('NOW', function () { return date('Y-m-d H:i:s'); });
$pdo->exec('PRAGMA foreign_keys = ON');
$pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, full_name TEXT)');
$pdo->exec('CREATE TABLE reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    user_id INTEGER,
    parent_id INTEGER REFERENCES reviews(id) ON DELETE CASCADE,
    rating INTEGER CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    is_approved INTEGER DEFAULT 1,
    created_at TEXT,
    updated_at TEXT
)');
$pdo->exec("INSERT INTO users VALUES (1, 'owner', 'Owner'), (2, 'other', 'Other')");
$pdo->exec("INSERT INTO reviews (id, product_id, user_id, parent_id, rating, comment, is_approved, created_at) VALUES
    (1, 100, 1, NULL, 4, 'Parent review', 1, '2026-01-01'),
    (2, 200, 1, NULL, 3, 'Another product', 1, '2026-01-01'),
    (3, 100, 1, 1, 5, 'Existing reply', 1, '2026-01-02'),
    (4, 100, 1, NULL, 5, 'Not approved', 0, '2026-01-01')");

// Inject an isolated connection without invoking the production DB constructor.
$reflection = new ReflectionClass(\App\Controllers\ReviewController::class);
$controller = $reflection->newInstanceWithoutConstructor();
$connection = $reflection->getProperty('pdo');
$connection->setAccessible(true);
$connection->setValue($controller, $pdo);

function requestReview($controller, $action, $data, $userId = 1, $validCsrf = true) {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SESSION = ['csrf_token' => 'isolated-review-test', 'role' => 'client'];
    if ($userId !== null) {
        $_SESSION['user_id'] = $userId;
    }
    $_POST = $data + ['csrf_token' => $validCsrf ? 'isolated-review-test' : 'invalid'];
    http_response_code(200);
    ob_start();
    try {
        $controller->$action();
        $body = ob_get_contents();
    } finally {
        ob_end_clean();
    }
    return ['status' => http_response_code(), 'body' => json_decode($body, true, 512, JSON_THROW_ON_ERROR)];
}

function expectReview($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$tests = [
    'add a reply without a rating, even when the author already reviewed this product' => function () use ($controller, $pdo) {
        $response = requestReview($controller, 'addReview', ['product_id' => 100, 'parent_id' => 1, 'comment' => 'New reply']);
        expectReview($response['status'] === 200 && $response['body']['success'], 'Reply should be accepted without rating');
        $saved = $pdo->query('SELECT * FROM reviews ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        expectReview($saved['parent_id'] == 1 && $saved['comment'] === 'New reply', 'Reply should be stored under its parent');
    },
    'edit a reply without a rating and preserve its stored rating' => function () use ($controller, $pdo) {
        $response = requestReview($controller, 'updateReview', ['review_id' => 3, 'comment' => 'Edited reply']);
        expectReview($response['status'] === 200 && $response['body']['success'], 'Reply edit must not require a rating');
        $saved = $pdo->query('SELECT * FROM reviews WHERE id = 3')->fetch(PDO::FETCH_ASSOC);
        expectReview($saved['comment'] === 'Edited reply' && $saved['rating'] == 5 && $saved['updated_at'] !== null, 'Only reply text and update date should change');
    },
    'reply edits ignore a client-supplied rating' => function () use ($controller, $pdo) {
        $response = requestReview($controller, 'updateReview', ['review_id' => 3, 'rating' => 1, 'comment' => 'Text only']);
        expectReview($response['status'] === 200 && $response['body']['success'], 'Reply edit should succeed');
        expectReview($pdo->query('SELECT rating FROM reviews WHERE id = 3')->fetchColumn() == 5, 'Reply rating must remain unchanged');
    },
    'top-level review creation and editing still require a rating' => function () use ($controller) {
        $response = requestReview($controller, 'addReview', ['product_id' => 100, 'comment' => 'Review'], 2);
        expectReview($response['status'] === 400 && !$response['body']['success'], 'New review still requires a rating');
        $response = requestReview($controller, 'updateReview', ['review_id' => 1, 'parent_id' => 3, 'comment' => 'Review']);
        expectReview($response['status'] === 400 && !$response['body']['success'], 'Client parent_id must not bypass the root review rating');
        $response = requestReview($controller, 'updateReview', ['review_id' => 1, 'rating' => 2, 'comment' => 'Rated review']);
        expectReview($response['status'] === 200 && $response['body']['success'], 'Valid review edit should still work');
    },
    'answers require an approved top-level review on the same product' => function () use ($controller, $pdo) {
        $count = $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
        foreach ([2, 3, 4, 999] as $parentId) {
            $response = requestReview($controller, 'addReview', ['product_id' => 100, 'parent_id' => $parentId, 'comment' => 'Invalid parent']);
            expectReview($response['status'] === 404 && !$response['body']['success'], 'Invalid, hidden, nested or cross-product parent must be rejected');
        }
        expectReview($pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn() === $count, 'Invalid answers must not create rows');
    },
    'reply ownership, authentication and CSRF checks remain in place' => function () use ($controller, $pdo) {
        $comment = $pdo->query('SELECT comment FROM reviews WHERE id = 3')->fetchColumn();
        foreach ([['user' => null, 'csrf' => true, 'status' => 401], ['user' => 2, 'csrf' => true, 'status' => 403], ['user' => 1, 'csrf' => false, 'status' => 403]] as $case) {
            $response = requestReview($controller, 'updateReview', ['review_id' => 3, 'comment' => 'Unauthorized edit'], $case['user'], $case['csrf']);
            expectReview($response['status'] === $case['status'] && !$response['body']['success'], 'Permission checks must reject unauthorized edits');
        }
        expectReview($pdo->query('SELECT comment FROM reviews WHERE id = 3')->fetchColumn() === $comment, 'Rejected edits must not change stored text');
    },
    'empty answers remain invalid' => function () use ($controller) {
        $response = requestReview($controller, 'addReview', ['product_id' => 100, 'parent_id' => 1, 'comment' => '   ']);
        expectReview($response['status'] === 400 && !$response['body']['success'], 'Reply text is required');
    },
    'answers are displayed under their parent and excluded from the product rating' => function () use ($pdo) {
        $reviews = (new \App\Models\Product($pdo))->getReviews(100);
        expectReview(count($reviews) === 1 && $reviews[0]['id'] == 1, 'Only top-level approved reviews count');
        expectReview(count($reviews[0]['replies']) === 1 && $reviews[0]['replies'][0]['id'] == 3, 'Stored reply should be nested under the parent');
        expectReview(array_sum(array_column($reviews, 'rating')) / count($reviews) === 4, 'Replies must not affect average rating');
    }
];

$failures = 0;
$report = [];
foreach ($tests as $name => $run) {
    $pdo->beginTransaction();
    try {
        $run();
        $report[] = 'PASS ' . $name;
    } catch (Throwable $error) {
        $failures++;
        $report[] = 'FAIL ' . $name . ': ' . $error->getMessage();
    } finally {
        $pdo->rollBack();
    }
}
echo implode(PHP_EOL, $report) . PHP_EOL;
echo (count($tests) - $failures) . '/' . count($tests) . ' checks passed' . PHP_EOL;
exit($failures > 0 ? 1 : 0);