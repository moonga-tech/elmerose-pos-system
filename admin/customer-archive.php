<?php
require "../config/function.php";

$paraResultId = checkParamId('id');

if (is_numeric($paraResultId)) {
    $id = validated($paraResultId);
    $res = setArchived('customer', $id, true);
    if ($res) {
        audit_log('archive', 'customer', $id, json_encode(['by' => $_SESSION['loggedInUser']['name'] ?? 'system']));
        redirect('customers.php', 'Customer moved to archive successfully', 'success');
    } else {
        redirect('customers.php', 'Failed to move customer to archive', 'error');
    }
} else {
    redirect('customers.php', 'Invalid customer id', 'error');
}
