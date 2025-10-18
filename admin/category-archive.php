<?php
require "../config/function.php";

$paraResultId = checkParamId('id');

if (is_numeric($paraResultId)) {
    $id = validated($paraResultId);
    $res = setArchived('category', $id, true);
    if ($res) {
        audit_log('archive', 'category', $id, json_encode(['by' => $_SESSION['loggedInUser']['name'] ?? 'system']));
        redirect('categories.php', 'Category moved to archive successfully', 'success');
    } else {
        redirect('categories.php', 'Failed to move category to archive', 'error');
    }
} else {
    redirect('categories.php', 'Invalid category id', 'error');
}
