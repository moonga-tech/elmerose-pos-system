<?php
require "../config/function.php";

$paraResultId = checkParamId('id');

if (is_numeric($paraResultId)) {
    $id = validated($paraResultId);
    $res = setArchived('category', $id, false);
    if ($res) {
        audit_log('unarchive', 'category', $id, json_encode(['by' => $_SESSION['loggedInUser']['name'] ?? 'system']));
        redirect('categories.php', 'Category restored from archive successfully', 'success');
    } else {
        redirect('categories.php', 'Failed to restore category', 'error');
    }
} else {
    redirect('categories.php', 'Invalid category id', 'error');
}
