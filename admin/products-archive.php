<?php

require "../config/function.php";

$paraResultId = checkParamId('id');

if (is_numeric($paraResultId)) {
    $productId = validated($paraResultId);

    $product = getById('products', $productId);
    if ($product['status'] == 200) {
        // Update status to 2 = archived
        $updateData = [
            'status' => 2
        ];

        $res = update('products', $productId, $updateData);

        if ($res) {
            audit_log('archive', 'product', $productId, json_encode(['by' => $_SESSION['loggedInUser']['name'] ?? 'system']));
            redirect('products.php', 'Product moved to archive successfully', 'success');
        } else {
            redirect('products.php', 'Failed to move product to archive', 'error');
        }
    } else {
        redirect('products.php', $product['message'], 'error');
    }
} else {
    redirect('products.php', 'Invalid product id', 'error');
}

