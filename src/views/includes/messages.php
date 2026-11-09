<?php $flash_messages = \App\Helpers\Flash::consume(); ?>
<?php if ($flash_messages): ?>
    <div class="container flash-messages" aria-live="polite">
        <?php foreach ($flash_messages as $flash_message): ?>
            <div data-flash="<?php echo htmlspecialchars($flash_message['type']); ?>" class="alert alert-<?php echo htmlspecialchars($flash_message['type']); ?>" role="<?php echo $flash_message['type'] === 'error' ? 'alert' : 'status'; ?>">
                <?php echo htmlspecialchars($flash_message['message'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
