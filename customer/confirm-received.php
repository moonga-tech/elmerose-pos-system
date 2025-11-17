<?php
include('../config/function.php');

header('Content-Type: application/json');

if(!isset($_SESSION['customerLoggedIn'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit();
}

$orderId = validated($data['order_id']);
$customerId = $_SESSION['customerUser']['id'];

// verify ownership
$q = $conn->prepare("SELECT id FROM orders WHERE id = ? AND customer_id = ?");
$q->bind_param('ii', $orderId, $customerId);
$q->execute();
$res = $q->get_result();
if (!$res || $res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found or permission denied']);
    exit();
}

// ensure column exists
try {
    if (!columnExists('orders', 'is_received')) {
        mysqli_query($conn, "ALTER TABLE orders ADD COLUMN is_received TINYINT(1) DEFAULT 0");
    }
} catch (Exception $e) {
    // ignore
}

$u = $conn->prepare("UPDATE orders SET is_received = 1 WHERE id = ?");
$u->bind_param('i', $orderId);
if ($u->execute()) {
    // optional: log audit
    if (function_exists('audit_log')) {
        // audit_log(action, entity, entityId, details)
        audit_log('order_received', 'order', $orderId, "Customer {$customerId} confirmed receipt");
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update order']);
}

?>
