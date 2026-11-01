<?php
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/helpers/Auth.php';
require_once dirname(__DIR__) . '/helpers/CSRF.php';

$db = new \App\Config\Database();
$pdo = $db->connect();
$user_model = new \App\Models\User($pdo);

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF защита
    if (!\App\Helpers\CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Ошибка безопасности. Обновите страницу и попробуйте снова';
    } else {
        $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error_message = 'Email и пароль обязательны';
        } else {
            $user = $user_model->getByEmail($email);

            if ($user && \App\Helpers\Auth::verifyPassword($password, $user['password'])) {
                // Успешная авторизация
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();

                header('Location: ' . APP_URL . '/account');
                exit;
            } else {
                $error_message = 'Неверный email или пароль';
            }
        }
    } elseif ($action === 'register') {
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $full_name = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if (empty($email) || empty($username) || empty($password) || empty($password_confirm)) {
            $error_message = 'Email, имя пользователя и пароль обязательны';
        } elseif ($password !== $password_confirm) {
            $error_message = 'Пароли не совпадают';
        } elseif (strlen($password) < 6) {
            $error_message = 'Пароль должен быть не менее 6 символов';
        } else {
            // Проверяем, не существует ли уже пользователь с таким email
            $existing_user = $user_model->getByEmail($email);
            if ($existing_user) {
                $error_message = 'Пользователь с таким email уже зарегистрирован';
            } else {
                // Создаём нового пользователя
                $hashed_password = \App\Helpers\Auth::hashPassword($password);

                $user_data = [
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashed_password,
                    'full_name' => $full_name,
                    'phone' => $phone
                ];

                if ($user_model->create($user_data)) {
                    $success_message = 'Регистрация успешна. Теперь вы можете войти';
                } else {
                    $error_message = 'Ошибка при регистрации. Попробуйте ещё раз';
                }
            }
        }
    }
}
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-url" content="<?php echo APP_URL; ?>">
    <title>Авторизация - PCComponents Store</title>
    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('style.css'); ?>">
</head>
<body>
    <?php include dirname(__FILE__) . '/includes/header.php'; ?>

    <main>
        <div class="container">
            <div class="auth-container">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <div class="auth-tabs">
                    <button class="tab-btn active" data-tab="login">Вход</button>
                    <button class="tab-btn" data-tab="register">Регистрация</button>
                </div>

                <form id="login-form" class="auth-form active" method="POST">
                    <h2>Вход в систему</h2>
                    <input type="hidden" name="action" value="login">
                    <?php echo \App\Helpers\CSRF::getTokenField(); ?>

                    <label for="login_email">Email</label>
                    <input type="email" id="login_email" name="email" autocomplete="email" required>

                    <label for="login_password">Пароль</label>
                    <input type="password" id="login_password" name="password" autocomplete="current-password" required>

                    <button type="submit" class="btn">Войти</button>
                </form>

                <form id="register-form" class="auth-form" method="POST">
                    <h2>Регистрация</h2>
                    <input type="hidden" name="action" value="register">
                    <?php echo \App\Helpers\CSRF::getTokenField(); ?>

                    <label for="register_email">Email</label>
                    <input type="email" id="register_email" name="email" autocomplete="email" required>

                    <label for="register_username">Имя пользователя</label>
                    <input type="text" id="register_username" name="username" autocomplete="username" required>

                    <label for="register_full_name">Полное имя</label>
                    <input type="text" id="register_full_name" name="full_name" autocomplete="name">

                    <label for="register_phone">Телефон</label>
                    <input type="tel" id="register_phone" name="phone" autocomplete="tel">

                    <label for="register_password">Пароль</label>
                    <input type="password" id="register_password" name="password" autocomplete="new-password" required>

                    <label for="register_password_confirm">Подтвердить пароль</label>
                    <input type="password" id="register_password_confirm" name="password_confirm" autocomplete="new-password" required>

                    <button type="submit" class="btn">Зарегистрироваться</button>
                </form>
            </div>
        </div>
    </main>

    <?php include dirname(__FILE__) . '/includes/footer.php'; ?>

    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                switchAuthTab(this.getAttribute('data-tab'));
            });

            btn.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    switchAuthTab(this.getAttribute('data-tab'));
                }
            });
        });

        function switchAuthTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
            document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
            document.getElementById(tab + '-form').classList.add('active');
        }
    </script>
</body>
</html>
