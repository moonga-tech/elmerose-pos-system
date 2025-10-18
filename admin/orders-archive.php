<?php
require "../config/function.php";

$paraResultId = checkParamId('id');

if (is_numeric($paraResultId)) {
    $orderId = validated($paraResultId);

    // mark archived via helper
    $res = setArchived('order', $orderId, true);
    if ($res) {
        audit_log('archive', 'order', $orderId, json_encode(['by' => $_SESSION['loggedInUser']['name'] ?? 'system']));
        redirect('orders.php', 'Order moved to archive successfully', 'success');
    } else {
        redirect('orders.php', 'Failed to move order to archive', 'error');
    }
} else {
    redirect('orders.php', 'Invalid order id', 'error');
}
