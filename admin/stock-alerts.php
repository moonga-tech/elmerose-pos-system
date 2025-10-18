<?php include ('includes/header.php'); ?>
<style>
.table-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.table-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem;
}
.table thead th {
    background: #495057;
    color: white;
    border: none;
    padding: 12px;
    font-weight: 500;
}
.table tbody tr:hover {
    background: #f8f9fa;
}
.table tbody td {
    padding: 12px;
    vertical-align: middle;
}
.alert-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}
</style>

<div class="container-fluid px-4">
    <div class="card table-card">
        <div class="card-header table-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <h4 class="mb-0 d-inline">Stock Alerts</h4>
                </div>
                <div class="d-flex align-items-center">
                    <form class="me-3" method="GET" action="stock-alerts.php">
                        <div class="input-group">
                            <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control form-control-sm" placeholder="Search product id, name or category">
                            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                    <span class="badge bg-light text-dark">Low Stock & Out of Stock Items</span>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <?php alertMessage(); ?>

            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-2"></i>ID</th>
                            <th><i class="fas fa-cube me-2"></i>Product Name</th>
                            <th><i class="fas fa-tags me-2"></i>Category</th>
                            <th><i class="fas fa-boxes me-2"></i>Current Stock</th>
                            <th><i class="fas fa-exclamation-circle me-2"></i>Alert Level</th>
                            <th><i class="fas fa-cogs me-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Build prepared statement with optional search
                        $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.quantity <= 5";
                        $params = [];
                        $types = '';

                        if (isset($_GET['q']) && trim($_GET['q']) !== '') {
                            $q = trim($_GET['q']);
                            if (ctype_digit($q)) {
                                $sql .= " AND p.id = ?";
                                $params[] = (int)$q;
                                $types .= 'i';
                            } else {
                                $sql .= " AND (p.name LIKE ? OR c.name LIKE ? OR p.description LIKE ? )";
                                $like = '%' . $q . '%';
                                $params[] = $like; $params[] = $like; $params[] = $like;
                                $types .= 'sss';
                            }
                        }

                        $sql .= " ORDER BY p.quantity ASC";
                        $stmt = $conn->prepare($sql);
                        if ($types !== '') {
                            $stmt->bind_param($types, ...$params);
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if($result && mysqli_num_rows($result) > 0) {
                            while($product = mysqli_fetch_assoc($result)) {
                                $alertLevel = $product['quantity'] == 0 ? 'Out of Stock' : 'Low Stock';
                                $badgeClass = $product['quantity'] == 0 ? 'bg-danger' : 'bg-warning';
                                ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                    <td><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($product['category_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="badge <?= $badgeClass; ?> alert-badge">
                                            <?= $product['quantity']; ?> units
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badgeClass; ?> alert-badge">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?= $alertLevel; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="products-edit.php?id=<?= $product['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit me-1"></i>Restock
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center p-5"><i class="fas fa-check-circle fa-3x mb-3 d-block text-success"></i><h5>All products are well stocked!</h5><p class="text-muted">No low stock alerts at this time</p></td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include ('includes/footer.php'); ?>