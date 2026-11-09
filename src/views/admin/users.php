<?php
$users = $user_model->getAll($admin_page_size + 1, $admin_offset);
$has_more = count($users) > $admin_page_size;
$users = array_slice($users, 0, $admin_page_size);
$role_labels = ['admin' => 'Администратор', 'manager' => 'Менеджер', 'client' => 'Клиент'];
$user_status_labels = ['active' => 'Активен', 'blocked' => 'Заблокирован', 'deleted' => 'Удалён'];
?>
<div class="admin-header"><h2>Управление пользователями</h2></div>
<div class="data-table-wrapper">
    <table class="data-table">
        <thead><tr><th>ID</th><th>Email</th><th>Имя пользователя</th><th>Роль</th><th>Статус</th><th>Дата регистрации</th></tr></thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo (int)$user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><span class="badge <?php echo $user['role'] === 'admin' ? 'badge-destructive' : ($user['role'] === 'manager' ? 'badge-secondary' : ''); ?>"><?php echo htmlspecialchars($role_labels[$user['role']] ?? $user['role']); ?></span></td>
                    <td><?php echo htmlspecialchars($user_status_labels[$user['status']] ?? $user['status']); ?></td>
                    <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$users): ?><tr><td colspan="6">Пользователи не найдены</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/pagination.php'; ?>
