<?php 
include('../config/function.php');

if(!isset($_SESSION['customerLoggedIn'])) {
    redirect("../customer-login.php", "Please login first!", "error");
    exit();
}

$customer = $_SESSION['customerUser'];
$customerId = $customer['id'];

if(!isset($_GET['id'])) {
    redirect("orders.php", "No order ID provided!", "error");
    exit();
}

$orderId = validated($_GET['id']);

$orderQuery = "SELECT * FROM orders WHERE id = '$orderId' AND customer_id = '$customerId'";
$orderResult = mysqli_query($conn, $orderQuery);

if(mysqli_num_rows($orderResult) == 0) {
    redirect("orders.php", "Order not found!", "error");
    exit();
}

$order = mysqli_fetch_assoc($orderResult);

$orderItemsQuery = "SELECT oi.*, p.name as productName FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = '$orderId'";
$orderItems = mysqli_query($conn, $orderItemsQuery);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Elmerose POS</title>
    <link href="../assets/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .details-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-store me-2"></i>Elmerose Store
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2"></i>My Orders</a></li>
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../customer-logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
                <a class="nav-link position-relative" href="cart.php">
                    <i class="fas fa-shopping-cart"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card details-card">
            <div class="card-header navbar-custom text-white">
                <h4 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Details for #<?= htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?></h4>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Order Date:</strong> <?= date('d M, Y', strtotime($order['created_at'])); ?></p>
                        <p><strong>Status:</strong> 
                            <?php
                                $status = htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8');
                                $badge_class = 'bg-secondary';
                                if($status == 'pending') $badge_class = 'bg-warning';
                                if($status == 'confirmed') $badge_class = 'bg-info';
                                if($status == 'delivered') $badge_class = 'bg-success';
                                if($status == 'cancelled') $badge_class = 'bg-danger';
                                echo "<span class='badge {$badge_class}'>".ucfirst($status)."</span>";
                            ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h4 class="mb-0">Total: ₱<?= number_format($order['total_amount'], 2); ?></h4>
                    </div>
                </div>

                <h5>Items in this order:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(mysqli_num_rows($orderItems) > 0) {
                                foreach($orderItems as $item) {
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['productName'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>₱<?= number_format($item['price'], 2); ?></td>
                                        <td><?= $item['quantity']; ?></td>
                                        <td>₱<?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="4" class="text-center text-muted">No items found for this order.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <a href="orders.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Orders</a>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
