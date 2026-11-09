<?php
$admin_product_controller = new \App\Controllers\AdminProductController($pdo);
$product_id = $action === 'update' ? \App\Helpers\Request::integer($_POST, 'id') : null;
$form_url = '/admin?tab=products&action=' . ($action === 'update' ? 'edit&id=' . $product_id : 'add');
$form_command = \App\Helpers\Request::text($_POST, 'form_command');
if ($action !== 'delete' && $form_command !== '') {
    // Без JS добавление/удаление строки формы тоже работает; данные пока не сохраняются в БД.
    $draft = $_POST;
    $specifications = is_array($draft['specifications'] ?? null) ? array_values($draft['specifications']) : [];
    if ($form_command === 'add_specification' && count($specifications) < 100) {
        $specifications[] = ['name' => '', 'value' => ''];
    } elseif (preg_match('/^remove_specification:(\d+)$/', $form_command, $matches)) {
        unset($specifications[(int)$matches[1]]);
    }
    $draft['specifications'] = array_values($specifications);
    $_SESSION['admin_product_draft'] = ['id' => $product_id, 'data' => $draft];
    \App\Helpers\Response::redirect($form_url);
    return;
}
try {
    if ($action === 'delete') {
        $admin_product_controller->delete(\App\Helpers\Request::integer($_POST, 'id'));
        $message = 'Товар снят с продажи. Данные оформленных заказов сохранены';
    } else {
        $admin_product_controller->save($_POST, $product_id, $_FILES);
        $message = $action === 'create' ? 'Товар создан' : 'Товар обновлён';
    }
    unset($_SESSION['admin_product_draft']);
    \App\Helpers\Response::send(['success' => true, 'message' => $message], 200, '/admin?tab=products');
} catch (\InvalidArgumentException $exception) {
    if ($action !== 'delete' && !\App\Helpers\Request::isAjax()) {
        $_SESSION['admin_product_draft'] = ['id' => $product_id, 'data' => $_POST];
    }
    \App\Helpers\Response::send(['success' => false, 'message' => $exception->getMessage(), 'errors' => $exception instanceof \App\Helpers\ValidationException ? $exception->errors : []], 400, $action === 'delete' ? '/admin?tab=products' : $form_url);
} catch (\PDOException $exception) {
    if ($action !== 'delete' && !\App\Helpers\Request::isAjax()) {
        $_SESSION['admin_product_draft'] = ['id' => $product_id, 'data' => $_POST];
    }
    \App\Helpers\Response::send(['success' => false, 'message' => 'Не удалось сохранить товар. Проверьте уникальность SKU и повторите действие'], 500, $action === 'delete' ? '/admin?tab=products' : $form_url);
}
