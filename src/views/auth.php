<?php
/** @var \PDO $pdo */
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/helpers/Auth.php';
require_once dirname(__DIR__) . '/helpers/CSRF.php';

$user_model = new \App\Models\User($pdo);

$error_message = '';
$success_message = '';
$current_tab = \App\Helpers\Request::text($_GET, 'tab') === 'register' ? 'register' : 'login';
$auth_values = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF защита
    if (!\App\Helpers\CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Ошибка безопасности. Обновите страницу и попробуйте снова';
    } else {
        $action = \App\Helpers\Request::text($_POST, 'action');
        $current_tab = $action === 'register' ? 'register' : 'login';
        foreach (['email', 'username', 'full_name', 'phone'] as $field) {
            $auth_values[$field] = \App\Helpers\Request::text($_POST, $field);
        }

    if ($action === 'login') {
        $email = \App\Helpers\Request::text($_POST, 'email');
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        if (empty($email) || empty($password)) {
            $error_message = 'Email и пароль обязательны';
        } else {
            $user = $user_model->getByEmail($email);

            if ($user && ($user['status'] ?? 'active') === 'active' && \App\Helpers\Auth::verifyPassword($password, $user['password'])) {
                // Успешная авторизация
                $old_session_id = session_id();
                // Гостевые позиции переходят в аккаунт и объединяются с сохранённой корзиной.
                (new \App\Models\Cart($pdo))->mergeGuestCart($user['id'], $old_session_id);
                \App\Helpers\BrowsingHistory::mergeGuest($pdo, $user['id']);
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();

                \App\Helpers\Response::redirect('/account');
                exit;
            } else {
                $error_message = 'Неверный email или пароль';
            }
        }
    } elseif ($action === 'register') {
        $email = $auth_values['email'];
        $username = $auth_values['username'];
        $full_name = $auth_values['full_name'];
        $phone = $auth_values['phone'];
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $password_confirm = is_string($_POST['password_confirm'] ?? null) ? $_POST['password_confirm'] : '';

        if (empty($email) || empty($username) || empty($password) || empty($password_confirm)) {
            $error_message = 'Email, имя пользователя и пароль обязательны';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            $error_message = 'Укажите корректный email';
        } elseif (\App\Helpers\Request::length($username) > 50 || \App\Helpers\Request::length($full_name) > 100) {
            $error_message = 'Имя пользователя — до 50 символов, полное имя — до 100';
        } elseif ($phone !== '' && !\App\Helpers\Request::validPhone($phone)) {
            $error_message = 'Укажите корректный телефон (не менее 10 цифр)';
        } elseif ($password !== $password_confirm) {
            $error_message = 'Пароли не совпадают';
        } elseif (strlen($password) < 6) {
            $error_message = 'Пароль должен быть не менее 6 символов';
        } else {
            // Проверяем, не существует ли уже пользователь с таким email
            $existing_user = $user_model->getByEmail($email);
            if ($existing_user || $user_model->getByUsername($username)) {
                $error_message = 'Пользователь с таким email или именем уже зарегистрирован';
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
                    \App\Helpers\Flash::add('Регистрация успешна. Теперь вы можете войти');
                    \App\Helpers\Response::redirect('/auth');
                    exit;
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
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>
    <?php include dirname(__FILE__) . '/includes/header.php'; ?>

    <main>
        <div class="container">
            <div class="auth-container" data-tabs>
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
                    <a href="<?php echo APP_URL; ?>/auth?tab=login" class="tab-btn <?php echo $current_tab === 'login' ? 'active' : ''; ?>" data-tab="login" data-tab-target="login-form">Вход</a>
                    <a href="<?php echo APP_URL; ?>/auth?tab=register" class="tab-btn <?php echo $current_tab === 'register' ? 'active' : ''; ?>" data-tab="register" data-tab-target="register-form">Регистрация</a>
                </div>

                <form id="login-form" class="auth-form <?php echo $current_tab === 'login' ? 'active' : ''; ?>" method="POST" action="<?php echo APP_URL; ?>/auth?tab=login">
                    <h2>Вход в систему</h2>
                    <input type="hidden" name="action" value="login">
                    <?php echo \App\Helpers\CSRF::getTokenField(); ?>

                    <label for="login_email">Email</label>
                    <input type="email" id="login_email" name="email" autocomplete="email" maxlength="100" placeholder="example@mail.ru" value="<?php echo htmlspecialchars($auth_values['email'] ?? ''); ?>" required>

                    <label for="login_password">Пароль</label>
                    <input type="password" id="login_password" name="password" autocomplete="current-password" placeholder="Введите пароль" required>

                    <button type="submit" class="btn">Войти</button>
                </form>

                <form id="register-form" class="auth-form <?php echo $current_tab === 'register' ? 'active' : ''; ?>" method="POST" action="<?php echo APP_URL; ?>/auth?tab=register">
                    <h2>Регистрация</h2>
                    <input type="hidden" name="action" value="register">
                    <?php echo \App\Helpers\CSRF::getTokenField(); ?>

                    <label for="register_email">Email</label>
                    <input type="email" id="register_email" name="email" autocomplete="email" maxlength="100" placeholder="example@mail.ru" value="<?php echo htmlspecialchars($auth_values['email'] ?? ''); ?>" required>

                    <label for="register_username">Имя пользователя</label>
                    <input type="text" id="register_username" name="username" autocomplete="username" maxlength="50" placeholder="Придумайте имя пользователя" value="<?php echo htmlspecialchars($auth_values['username'] ?? ''); ?>" required>

                    <label for="register_full_name">Полное имя</label>
                    <input type="text" id="register_full_name" name="full_name" autocomplete="name" maxlength="100" placeholder="Иван Иванов" value="<?php echo htmlspecialchars($auth_values['full_name'] ?? ''); ?>">

                    <label for="register_phone">Телефон</label>
                    <input type="tel" id="register_phone" name="phone" autocomplete="tel" maxlength="20" placeholder="+7 (999) 123-45-67" value="<?php echo htmlspecialchars($auth_values['phone'] ?? ''); ?>">

                    <label for="register_password">Пароль</label>
                    <input type="password" id="register_password" name="password" autocomplete="new-password" placeholder="Не менее 6 символов" minlength="6" required>

                    <label for="register_password_confirm">Подтвердить пароль</label>
                    <input type="password" id="register_password_confirm" name="password_confirm" autocomplete="new-password" placeholder="Повторите пароль" minlength="6" required>

                    <button type="submit" class="btn">Зарегистрироваться</button>
                </form>
            </div>
        </div>
    </main>

    <?php include dirname(__FILE__) . '/includes/footer.php'; ?>

</body>
</html>
