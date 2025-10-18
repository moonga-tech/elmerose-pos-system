<?php
require "../config/function.php";

$paraResultId = checkParamId('id');

if (is_numeric($paraResultId)) {
    $id = validated($paraResultId);
    $res = setArchived('customer', $id, false);
    if ($res) {
        audit_log('unarchive', 'customer', $id, json_encode(['by' => $_SESSION['loggedInUser']['name'] ?? 'system']));
        redirect('customers.php', 'Customer restored from archive successfully', 'success');
    } else {
        redirect('customers.php', 'Failed to restore customer', 'error');
    }
} else {
    redirect('customers.php', 'Invalid customer id', 'error');
}
