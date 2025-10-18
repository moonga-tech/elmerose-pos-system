<?php

require "../config/function.php";

$paraResultId = checkParamId('id');

if (is_numeric($paraResultId)) {
    $productId = validated($paraResultId);

    $product = getById('products', $productId);
    if ($product['status'] == 200) {
        // Update status to 1 = active
        $updateData = [
            'status' => 1
        ];

        $res = update('products', $productId, $updateData);

        if ($res) {
            audit_log('unarchive', 'product', $productId, json_encode(['by' => $_SESSION['loggedInUser']['name'] ?? 'system']));
            redirect('products.php', 'Product restored from archive successfully', 'success');
        } else {
            redirect('products.php', 'Failed to restore product', 'error');
        }
    } else {
        redirect('products.php', $product['message'], 'error');
    }
} else {
    redirect('products.php', 'Invalid product id', 'error');
}
