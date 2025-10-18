<?php
require "../config/function.php";

$paraResultId = checkParamId('id');

if (is_numeric($paraResultId)) {
    $orderId = validated($paraResultId);

    // unarchive via helper
    $res = setArchived('order', $orderId, false);
    if ($res) {
        audit_log('unarchive', 'order', $orderId, json_encode(['by' => $_SESSION['loggedInUser']['name'] ?? 'system']));
        redirect('orders.php', 'Order restored from archive successfully', 'success');
    } else {
        redirect('orders.php', 'Failed to restore order', 'error');
    }
} else {
    redirect('orders.php', 'Invalid order id', 'error');
}
