<?php

require_once __DIR__ . "/../config/function.php";

// Validate GET id parameter
if (!isset($_GET['id']) || trim($_GET['id']) === '') {
    redirect('customers.php', 'No customer ID provided', 'error');
    exit;
}

$rawId = validated($_GET['id']);
if (!is_numeric($rawId)) {
    redirect('customers.php', 'Invalid customer ID', 'error');
    exit;
}

$customerId = (int)$rawId;

$customer = getById('customers', $customerId);
if (!isset($customer['status']) || $customer['status'] !== 200) {
    $msg = $customer['message'] ?? 'Customer not found';
    redirect('customers.php', $msg, 'error');
    exit;
}

// Check for related orders that reference this customer via foreign key
global $conn;
$orderCountRes = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM orders WHERE customer_id='" . $customerId . "'");
$ordersCount = 0;
if ($orderCountRes) {
    $row = mysqli_fetch_assoc($orderCountRes);
    $ordersCount = isset($row['cnt']) ? (int)$row['cnt'] : 0;
}

// If there are related orders, require an explicit force parameter to remove them
if ($ordersCount > 0 && (!isset($_GET['force']) || $_GET['force'] != '1')) {
    $msg = "Cannot delete customer: there are {$ordersCount} order(s) linked to this customer. ";
    $msg .= "To remove the customer and their orders (this will permanently delete order records), use the force option: ";
    $msg .= "<a href=\"customer-delete.php?id={$customerId}&force=1\">Force delete</a>";
    redirect('customers.php', $msg, 'error');
    exit;
}

// If force delete requested, delete related order_items and orders in a transaction, then delete customer
if ($ordersCount > 0 && isset($_GET['force']) && $_GET['force'] == '1') {
    try {
        mysqli_begin_transaction($conn);

        // Get order ids
        $orderIds = [];
        $res = mysqli_query($conn, "SELECT id FROM orders WHERE customer_id='" . $customerId . "'");
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                $orderIds[] = (int)$r['id'];
            }
        }

        if (!empty($orderIds)) {
            $idsStr = implode(',', $orderIds);
            // Delete order_items for these orders
            $delItems = mysqli_query($conn, "DELETE FROM order_items WHERE order_id IN ($idsStr)");
            if ($delItems === false) {
                throw new Exception('Failed to delete order items: ' . mysqli_error($conn));
            }

            // Delete the orders
            $delOrders = mysqli_query($conn, "DELETE FROM orders WHERE id IN ($idsStr)");
            if ($delOrders === false) {
                throw new Exception('Failed to delete orders: ' . mysqli_error($conn));
            }
        }

        // Now delete the customer
        $deleted = delete('customers', $customerId);
        if (!$deleted) {
            throw new Exception('Failed to delete customer');
        }

        mysqli_commit($conn);
        redirect('customers.php', 'Customer and related orders deleted successfully', 'success');
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect('customers.php', 'Error deleting customer: ' . $e->getMessage(), 'error');
        exit;
    }
}

// No related orders: safe to delete the customer
$deleted = delete('customers', $customerId);
if ($deleted) {
    redirect('customers.php', 'Customer deleted successfully', 'success');
} else {
    redirect('customers.php', 'Failed to delete customer', 'error');
}
