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

$payment_method = isset($data['payment_method']) ? validated($data['payment_method']) : 'cod';
// delivery_option: 'cod' or 'pickup'
$delivery_option = isset($data['delivery_option']) ? validated($data['delivery_option']) : 'cod';
// delivery_address: required when delivery_option is 'cod'
$delivery_address = isset($data['delivery_address']) ? validated($data['delivery_address']) : '';

// determine delivery fee: COD has flat fee, pickup is free
$delivery_fee = 0.00;
// flat fee read from config
$COD_FEE = (float)get_delivery_fee();
if ($delivery_option === 'cod') {
    $delivery_fee = $COD_FEE;
} else {
    $delivery_fee = 0.00;
}

$customerId = $_SESSION['customerUser']['id'];
$cart = $data['cart'];
$totalAmount = 0;

foreach($cart as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}

// include delivery fee in the total amount charged
$totalAmountWithFee = $totalAmount + $delivery_fee;

$ordersTable = 'orders';
// ensure orders table has payment_method and delivery_fee columns
try {
    if (!columnExists($ordersTable, 'payment_method')) {
        mysqli_query($conn, "ALTER TABLE $ordersTable ADD COLUMN payment_method VARCHAR(50) NULL");
    }
    if (!columnExists($ordersTable, 'delivery_fee')) {
        mysqli_query($conn, "ALTER TABLE $ordersTable ADD COLUMN delivery_fee DECIMAL(10,2) DEFAULT 0");
    }
    if (!columnExists($ordersTable, 'delivery_address')) {
        mysqli_query($conn, "ALTER TABLE $ordersTable ADD COLUMN delivery_address TEXT NULL");
    }
} catch (Exception $e) {
    // ignore errors here; fallback to inserting without payment_method
}

// Insert into orders table (including payment method if column exists)
// Use the delivery-aware total when inserting
// Build insert with optional columns
$cols = ['customer_id', 'total_amount', 'status'];
$vals = ["'$customerId'", "'$totalAmountWithFee'", "'pending'"];
if (columnExists($ordersTable, 'payment_method')) {
    $cols[] = 'payment_method';
    $vals[] = "'$payment_method'";
}
if (columnExists($ordersTable, 'delivery_fee')) {
    $cols[] = 'delivery_fee';
    $vals[] = "'$delivery_fee'";
}
if (columnExists($ordersTable, 'delivery_address')) {
    $cols[] = 'delivery_address';
    $vals[] = "'" . mysqli_real_escape_string($conn, $delivery_address) . "'";
}

$query = "INSERT INTO $ordersTable (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
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