<?php
include('../config/function.php');

header('Content-Type: application/json');

if(!isset($_SESSION['customerLoggedIn'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = validated($_POST['product_id']);
    $quantity = validated($_POST['quantity']);
    
    if($productId && $quantity > 0) {
        // Get current product stock
        $stmt = $conn->prepare("SELECT quantity, name FROM products WHERE id = ? AND status = 0");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $currentStock = $product['quantity'];
            $productName = $product['name'];
            
            // Check if enough stock available
            if($currentStock >= $quantity) {
                // Update product quantity
                $newStock = $currentStock - $quantity;
                $updateStmt = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");
                $updateStmt->bind_param("ii", $newStock, $productId);
                
                if($updateStmt->execute()) {
                    // Check for low stock alert
                    $alertMessage = '';
                    if($newStock <= 5 && $newStock > 0) {
                        $alertMessage = "Low stock alert: Only $newStock units left for $productName";
                    } elseif($newStock == 0) {
                        $alertMessage = "Out of stock: $productName is no longer available";
                    }
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Product added to cart successfully',
                        'newStock' => $newStock,
                        'alertMessage' => $alertMessage
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update stock']);
                }
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => "Not enough stock! Only $currentStock units available"
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found or unavailable']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>