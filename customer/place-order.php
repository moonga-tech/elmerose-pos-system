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

$payment_method = isset($data['payment_method']) ? validated($data['payment_method']) : 'pickup';
$delivery_fee = isset($data['delivery_fee']) ? (float)$data['delivery_fee'] : 0;
$totalAmount = isset($data['total_amount']) ? (float)$data['total_amount'] : 0;

$customerId = $_SESSION['customerUser']['id'];
$cart = $data['cart'];

// Calculate subtotal from cart items
$subtotal = 0;
foreach($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Verify total matches subtotal + delivery fee
if($totalAmount != ($subtotal + $delivery_fee)) {
    $totalAmount = $subtotal + $delivery_fee;
}

$ordersTable = 'orders';
// if the orders table doesn't have payment_method column, try to add it (safe on most setups)
try {
    if (!columnExists($ordersTable, 'payment_method')) {
        mysqli_query($conn, "ALTER TABLE $ordersTable ADD COLUMN payment_method VARCHAR(50) NULL");
    }
} catch (Exception $e) {
    // ignore errors here; fallback to inserting without payment_method
}

// Insert into orders table
$query = "INSERT INTO orders (customer_id, total_amount, status, payment_method, delivery_fee) VALUES ('$customerId', '$totalAmount', 'pending', '$payment_method', '$delivery_fee')";
$result = mysqli_query($conn, $query);

if($result) {
    $orderId = mysqli_insert_id($conn);

    // Insert into order_items table
    foreach($cart as $item) {
        $productId = validated($item['id']);
        $quantity = validated($item['quantity']);
        $price = validated($item['price']);
        $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ('$orderId', '$productId', '$quantity', '$price')";
        mysqli_query($conn, $itemQuery);
    }

    echo json_encode(['success' => true, 'order_id' => $orderId]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}
?>