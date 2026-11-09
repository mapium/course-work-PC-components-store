<?php
$controller = new \App\Controllers\AdminCategoryController($pdo);
$id = $action === 'category_create' ? null : \App\Helpers\Request::integer($_POST, 'id');
$form_url = '/admin?tab=categories&action=' . ($id ? 'edit&id=' . $id : 'add');
try {
    if ($action === 'category_delete') {
        $controller->delete($id, \App\Helpers\Request::integer($_POST, 'transfer_id') ?: null);
        $message = 'Категория удалена';
    } else {
        $controller->save($_POST, $id, $_FILES);
        $message = $id ? 'Категория обновлена' : 'Категория создана';
    }
    unset($_SESSION['admin_category_draft']);
    \App\Helpers\Response::send(['success' => true, 'message' => $message], 200, '/admin?tab=categories');
} catch (\InvalidArgumentException $error) {
    if (!\App\Helpers\Request::isAjax()) $_SESSION['admin_category_draft'] = ['id' => $id, 'data' => $_POST];
    \App\Helpers\Response::send(['success' => false, 'message' => $error->getMessage(), 'errors' => $error instanceof \App\Helpers\ValidationException ? $error->errors : []], 400, $form_url);
} catch (\PDOException $error) {
    \App\Helpers\Response::send(['success' => false, 'message' => 'Не удалось сохранить категорию. Повторите действие'], 500, $form_url);
}
