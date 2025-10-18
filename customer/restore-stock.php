<?php
include('../config/function.php');

header('Content-Type: application/json');

if(!isset($_SESSION['customerLoggedIn'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$productId = isset($_POST['product_id']) ? validated($_POST['product_id']) : null;
$quantity = isset($_POST['quantity']) ? validated($_POST['quantity']) : 0;

if(!$productId || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    exit();
}

// Ensure product exists
$stmt = $conn->prepare("SELECT id, quantity, name FROM products WHERE id = ?");
$stmt->bind_param('i', $productId);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

$product = $res->fetch_assoc();

// Increase the stock back
$newStock = $product['quantity'] + $quantity;
$update = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");
$update->bind_param('ii', $newStock, $productId);
if($update->execute()) {
    echo json_encode(['success' => true, 'message' => 'Stock restored', 'newStock' => $newStock]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to restore stock']);
}

?>
