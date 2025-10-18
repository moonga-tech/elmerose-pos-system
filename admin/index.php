<?php include ('includes/header.php'); ?>
<style>
.dashboard-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}
.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}
.stat-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}
.chart-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
}
.alert-item {
    border-left: 4px solid #dc3545;
    background: rgba(220, 53, 69, 0.1);
    border-radius: 0 10px 10px 0;
}
a {
    text-decoration: none;
}
</style>

<div class="container-fluid px-4">
    <?php alertMessage(); ?>
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
        <span class="text-muted">Welcome back, <?= $_SESSION['loggedInUser']['name'] ?? 'Admin'; ?>!</span>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php
        // Get statistics
        $totalProducts = mysqli_num_rows(getAll('products'));
        $totalCategories = mysqli_num_rows(getAll('categories'));
        $totalAdmins = mysqli_num_rows(getAll('admins'));
        $lowStockCount = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM products WHERE quantity <= 5"));
        $totalCustomers = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM customers"));
        $totalRevenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as revenue FROM orders WHERE status != 'cancelled'"))['revenue'] ?? 0;
        $expiringProducts = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM products WHERE has_expiry = 1 AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL expiry_alert_days DAY)"));
        ?>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card bg-primary text-white">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Products</div>
                            <div class="h5 mb-0 font-weight-bold"><?= $totalProducts; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box stat-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="products.php">View Products</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card bg-success text-white">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Categories</div>
                            <div class="h5 mb-0 font-weight-bold"><?= $totalCategories; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags stat-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="categories.php">View Categories</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card bg-warning text-white">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Low Stock Items</div>
                            <div class="h5 mb-0 font-weight-bold"><?= $lowStockCount; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle stat-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="stock-alerts.php">View Alerts</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card bg-info text-white">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold">₱<?= number_format($totalRevenue, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas" style="font-size: 1.8rem;">₱</i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-decoration-none text-white stretched-link" href="orders.php">View Orders</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-6 mb-4">
            <div class="card chart-card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-shopping-cart me-2"></i>Recent Orders</h6>
                    <a href="orders.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <?php
                    $recentOrders = mysqli_query($conn, "SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id ORDER BY o.created_at DESC LIMIT 5");
                    if(mysqli_num_rows($recentOrders) > 0) {
                        while($order = mysqli_fetch_assoc($recentOrders)) {
                            $statusClass = $order['status'] == 'pending' ? 'warning' : ($order['status'] == 'confirmed' ? 'info' : ($order['status'] == 'delivered' ? 'success' : 'danger'));
                            ?>
                            <div class="d-flex align-items-center py-2 border-bottom">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?= htmlspecialchars($order['customer_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></h6>
                                    <small class="text-muted">Order #<?= $order['id']; ?> - <?= date('M d, Y', strtotime($order['created_at'])); ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">₱ <?= number_format($order['total_amount'], 2); ?></div>
                                    <span class="badge bg-<?= $statusClass; ?>"><?= ucfirst($order['status']); ?></span>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="text-muted text-center">No orders yet</p>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Stock Alerts -->
        <div class="col-lg-6 mb-4">
            <div class="card chart-card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Stock Alerts</h6>
                    <a href="stock-alerts.php" class="btn btn-danger btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <?php
                    $stockAlerts = mysqli_query($conn, "SELECT * FROM products WHERE quantity <= 5 ORDER BY quantity ASC LIMIT 5");
                    if(mysqli_num_rows($stockAlerts) > 0) {
                        while($product = mysqli_fetch_assoc($stockAlerts)) {
                            $alertClass = $product['quantity'] == 0 ? 'danger' : 'warning';
                            ?>
                            <div class="alert-item p-3 mb-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                        <small class="text-muted">Product ID: #<?= $product['id']; ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $alertClass; ?>"><?= $product['quantity']; ?> left</span>
                                        <div><a href="products-edit.php?id=<?= $product['id']; ?>" class="btn btn-sm btn-outline-primary mt-1">Restock</a></div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="text-center text-success"><i class="fas fa-check-circle fa-3x mb-2"></i><p>All products are well stocked!</p></div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card chart-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="products-create.php" class="btn btn-primary w-100 py-3">
                                <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                                Add Product
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="category-create.php" class="btn btn-success w-100 py-3">
                                <i class="fas fa-tags fa-2x d-block mb-2"></i>
                                Add Category
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="admin-create.php" class="btn btn-info w-100 py-3 text-white">
                                <i class="fas fa-user-plus fa-2x d-block mb-2"></i>
                                Add Admin
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="stock-alerts.php" class="btn btn-warning w-100 py-3 text-white">
                                <i class="fas fa-exclamation-triangle fa-2x d-block mb-2"></i>
                                Stock Alerts
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="expiry-alerts.php" class="btn btn-danger w-100 py-3 text-white">
                                <i class="fas fa-calendar-times fa-2x d-block mb-2"></i>
                                Expiry Alerts
                                <?php if($expiringProducts > 0): ?>
                                    <span class="badge bg-light text-dark ms-2"><?= $expiringProducts; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ('includes/footer.php'); ?>