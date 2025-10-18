<?php
include('../config/function.php');

header('Content-Type: application/json');

if(!isset($_SESSION['customerLoggedIn'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first!']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if(!isset($data['cart']) || empty($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty!']);
    exit();
}

$customerId = $_SESSION['customerUser']['id'];
$cart = $data['cart'];
$totalAmount = 0;

foreach($cart as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}

// Insert into orders table
$query = "INSERT INTO orders (customer_id, total_amount, status) VALUES ('$customerId', '$totalAmount', 'pending')";
$result = mysqli_query($conn, $query);

if($result) {
    $orderId = mysqli_insert_id($conn);

    // Insert into order_items table
    foreach($cart as $item) {
        $productId = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ('$orderId', '$productId', '$quantity', '$price')";
        mysqli_query($conn, $itemQuery);

            // Note: stock was already decremented when the item was added to cart (reservation),
            // so do not decrement again here to avoid double-decrementing inventory.
    }

    echo json_encode(['success' => true, 'order_id' => $orderId]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to place order.']);
}
?>