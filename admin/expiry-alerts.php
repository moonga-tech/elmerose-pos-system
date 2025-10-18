<?php include ('includes/header.php'); ?>

<div class="container-fluid px-4">
    <div class="card enhanced-table-card">
        <div class="card-header enhanced-table-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-calendar-times me-2"></i>
                    <h4 class="mb-0 d-inline">Product Expiry Alerts</h4>
                </div>
                <span class="badge bg-light text-dark">Products with Expiry Dates</span>
            </div>
        </div>

        <div class="card-body p-0">
            <?php alertMessage(); ?>

            <div class="table-responsive">
                <table class="table enhanced-table mb-0">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-2"></i>ID</th>
                            <th><i class="fas fa-cube me-2"></i>Product Name</th>
                            <th><i class="fas fa-calendar me-2"></i>Expiry Date</th>
                            <th><i class="fas fa-clock me-2"></i>Days Remaining</th>
                            <th><i class="fas fa-exclamation-circle me-2"></i>Status</th>
                            <th><i class="fas fa-cogs me-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $today = date('Y-m-d');
                        $sql = "SELECT * FROM products WHERE has_expiry = 1 AND expiry_date IS NOT NULL ORDER BY expiry_date ASC";
                        $result = mysqli_query($conn, $sql);

                        if($result && mysqli_num_rows($result) > 0) {
                            while($product = mysqli_fetch_assoc($result)) {
                                $expiryDate = $product['expiry_date'];
                                $daysRemaining = (strtotime($expiryDate) - strtotime($today)) / (60 * 60 * 24);
                                $alertDays = $product['expiry_alert_days'];
                                
                                $statusClass = 'bg-success';
                                $statusText = 'Good';
                                
                                if($daysRemaining < 0) {
                                    $statusClass = 'bg-danger';
                                    $statusText = 'Expired';
                                } elseif($daysRemaining <= $alertDays) {
                                    $statusClass = 'bg-warning';
                                    $statusText = 'Expiring Soon';
                                }
                                ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                    <td><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= date('M d, Y', strtotime($expiryDate)); ?></td>
                                    <td>
                                        <?php if($daysRemaining < 0): ?>
                                            <span class="text-danger"><?= abs(floor($daysRemaining)); ?> days overdue</span>
                                        <?php else: ?>
                                            <?= floor($daysRemaining); ?> days
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statusClass; ?> status-badge">
                                            <?= $statusText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="products-edit.php?id=<?= $product['id']; ?>" class="btn btn-primary action-btn btn-sm">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center p-5'><i class='fas fa-calendar-check fa-3x mb-3 d-block text-muted'></i><h5>No products with expiry dates</h5><p class='text-muted'>Products with expiry tracking will appear here</p></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include ('includes/footer.php'); ?>