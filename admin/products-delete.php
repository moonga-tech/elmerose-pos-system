<?php

require "../config/function.php";

$paraResultId= checkParamId('id');

if(is_numeric($paraResultId)) {
    $categoryId = validated($paraResultId);

    $category = getById('products', $categoryId);
    if($category['status'] == 200) {
        $categoryDeleteRes = delete('products', $categoryId);

        if($categoryDeleteRes) {
            redirect('products.php', 'Product Deleted Successfully');
        } else {
            redirect('products.php', 'Something went wrong!', 'error');
        }
    } else {
        redirect('products.php', $category['message']);
    }
} else {
    redirect('products.php', 'Something went wrong!', 'error');
}
