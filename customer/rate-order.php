<?php
include('../config/function.php');

header('Content-Type: application/json');

if (!isset($_SESSION['customerLoggedIn'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['order_id']) || !isset($data['rating'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$order_id = validated($data['order_id']);
$rating = (int)$data['rating'];
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit();
}

$customerId = $_SESSION['customerUser']['id'];

// verify the order belongs to the customer
$res = mysqli_query($conn, "SELECT id, customer_id FROM orders WHERE id='$order_id' LIMIT 1");
if (!$res || mysqli_num_rows($res) == 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit();
}
$row = mysqli_fetch_assoc($res);
if ((int)$row['customer_id'] !== (int)$customerId) {
    echo json_encode(['success' => false, 'message' => 'You are not authorized to rate this order']);
    exit();
}

// update rating column (create column if missing)
if (!columnExists('orders', 'rating')) {
    @mysqli_query($conn, "ALTER TABLE orders ADD COLUMN rating TINYINT NULL");
}

$upd = mysqli_query($conn, "UPDATE orders SET rating='$rating' WHERE id='$order_id'");
if ($upd) {
    audit_log('rate', 'order', $order_id, json_encode(['rating' => $rating]));
    echo json_encode(['success' => true, 'message' => 'Rating saved']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save rating']);
}

?>
