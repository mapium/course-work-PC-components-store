<?php
$return_to = '/admin?tab=orders';
$order_id = \App\Helpers\Request::integer($_POST, 'order_id');
if (\App\Helpers\Request::text($_POST, 'return_view') === 'detail' && $order_id > 0) {
    $return_to .= '&action=view&id=' . $order_id;
}
(new \App\Controllers\OrderController($pdo))->updateStatus($return_to);
