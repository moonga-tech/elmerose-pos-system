<?php
include('../config/function.php');

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = validated($_POST['customer_id']);
    $status = validated($_POST['status']);
    
    if($customerId && ($status == 0 || $status == 1)) {
        $stmt = $conn->prepare("UPDATE customers SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $customerId);
        
        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Customer status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update customer status']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>