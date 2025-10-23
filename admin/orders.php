<?php include ('includes/header.php'); ?>

<link rel="stylesheet" href="assets/css/orders.css">

<div class="container-fluid px-4">
    <div class="card enhanced-table-card">
        <div class="card-header enhanced-table-header">
                <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-shopping-cart me-2"></i>
                    <h4 class="mb-0 d-inline">Orders Management</h4>
                </div>
                <div class="d-flex align-items-center">
                    <?php $showArchived = isset($_GET['archived']) && $_GET['archived'] === '1'; ?>
                    <form class="me-3" method="GET" action="orders.php">
                        <div class="input-group">
                            <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control form-control-sm" placeholder="Search by order id, customer name or email">
                            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                    <?php if($showArchived): ?>
                        <a href="orders.php" class="btn btn-outline-secondary btn-sm me-2">Show Active</a>
                    <?php else: ?>
                        <a href="orders.php?archived=1" class="btn btn-outline-secondary btn-sm me-2">Show Archived</a>
                    <?php endif; ?>
                    <span class="badge bg-light text-dark">Total Orders: <?= mysqli_num_rows(mysqli_query($conn, "SELECT * FROM orders")); ?></span>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <?php alertMessage(); ?>

            <div class="table-responsive">
                <table class="table enhanced-table mb-0">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-2"></i>Order ID</th>
                            <th><i class="fas fa-user me-2"></i>Customer</th>
                            <th><i class="fas fa-peso-sign me-2"></i>Amount</th>
                            <th><i class="fas fa-info-circle me-2"></i>Status</th>
                            <th><i class="fas fa-credit-card me-2"></i>Payment</th>
                            <th><i class="fas fa-calendar me-2"></i>Date</th>
                            <th><i class="fas fa-cogs me-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Pagination
                        $perPage = 15;
                        $page = isset($_GET['p']) && is_numeric($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
                        $offset = ($page - 1) * $perPage;

                        // Prepare base queries with optional search
                        $searchTerm = '';
                        $params = [];
                        $types = '';

                        $baseCountSql = "SELECT COUNT(*) AS cnt FROM orders o LEFT JOIN customers c ON o.customer_id = c.id";
                        $baseSql = "SELECT o.*, c.name as customer_name, c.email as customer_email FROM orders o LEFT JOIN customers c ON o.customer_id = c.id";

                        // archived filter: if orders table has is_archived column use it, otherwise use archived_items table
                        $ordersHaveIsArchived = false;
                        try {
                            $ordersHaveIsArchived = columnExists('orders', 'is_archived');
                        } catch (Exception $e) {
                            $ordersHaveIsArchived = false;
                        }

                        if (isset($_GET['q']) && trim($_GET['q']) !== '') {
                            $searchTerm = trim($_GET['q']);
                            // If user typed a number and matches id exactly
                            if (ctype_digit($searchTerm)) {
                                $where = " WHERE o.id = ?";
                                $baseCountSql .= $where;
                                $baseSql .= $where;
                                $params[] = (int)$searchTerm;
                                $types .= 'i';
                            } else {
                                $like = '%' . $searchTerm . '%';
                                $where = " WHERE c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR o.total_amount LIKE ?";
                                $baseCountSql .= $where;
                                $baseSql .= $where;
                                $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
                                $types .= 'ssss';
                            }
                        }

                        // Apply archived condition to count and base SQL
                        if ($ordersHaveIsArchived) {
                            if ($showArchived) {
                                $baseCountSql .= (stripos($baseCountSql, 'WHERE') !== false) ? " AND o.is_archived = 1" : " WHERE o.is_archived = 1";
                                $baseSql .= (stripos($baseSql, 'WHERE') !== false) ? " AND o.is_archived = 1" : " WHERE o.is_archived = 1";
                            } else {
                                $baseCountSql .= (stripos($baseCountSql, 'WHERE') !== false) ? " AND o.is_archived != 1" : " WHERE o.is_archived != 1";
                                $baseSql .= (stripos($baseSql, 'WHERE') !== false) ? " AND o.is_archived != 1" : " WHERE o.is_archived != 1";
                            }
                        } else {
                            // We'll filter later with archived_items join for listing queries
                            if ($showArchived) {
                                // we'll join archived_items when building fetch query below
                            } else {
                                // same as above
                            }
                        }

                        // Count total
                        $countStmt = $conn->prepare($baseCountSql);
                        if ($types !== '') {
                            $countStmt->bind_param($types, ...$params);
                        }
                        $countStmt->execute();
                        $countRes = $countStmt->get_result();
                        $total = 0;
                        if ($countRes && $countRes->num_rows > 0) {
                            $total = $countRes->fetch_assoc()['cnt'];
                        }

                        // Fetch paginated rows
                        // If archived_items strategy is used, modify baseSql to include join for filtering
                        if (!$ordersHaveIsArchived) {
                            // make sure archived_items exists
                            ensureArchivedTableExists();
                            if ($showArchived) {
                                // select only orders that have an archived_items record
                                $baseSql = "SELECT o.*, c.name as customer_name, c.email as customer_email FROM orders o LEFT JOIN customers c ON o.customer_id = c.id INNER JOIN archived_items a ON a.entity='order' AND a.entity_id = o.id";
                            } else {
                                // exclude orders that have an archived_items record
                                $baseSql = "SELECT o.*, c.name as customer_name, c.email as customer_email FROM orders o LEFT JOIN customers c ON o.customer_id = c.id LEFT JOIN archived_items a ON a.entity='order' AND a.entity_id = o.id WHERE a.id IS NULL";
                            }
                        }

                        $baseSql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
                        $stmt = $conn->prepare($baseSql);
                        // bind dynamic params + pagination params
                        if ($types === '') {
                            $stmt->bind_param('ii', $perPage, $offset);
                        } else {
                            $typesWithLimit = $types . 'ii';
                            $stmt->bind_param($typesWithLimit, ...array_merge($params, [$perPage, $offset]));
                        }
                        $stmt->execute();
                        $ordersRes = $stmt->get_result();
                        $orders = $ordersRes;
                        
                        if(mysqli_num_rows($orders) > 0) {
                            while($order = mysqli_fetch_assoc($orders)) {
                                $statusClass = $order['status'] == 'pending' ? 'bg-warning' : ($order['status'] == 'confirmed' ? 'bg-info' : ($order['status'] == 'delivered' ? 'bg-success' : 'bg-danger'));
                                ?>
                                <tr>

                                    <td>
                                        <strong>#<?= htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </td>

                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($order['customer_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($order['customer_email'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></small>
                                        </div>
                                    </td>
                                    <td><strong>₱<?= number_format($order['total_amount'], 2); ?></strong></td>
                                    <td>
                                        <span class="badge <?= $statusClass; ?> status-badge">
                                            <?= ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= !empty($order['payment_method']) ? htmlspecialchars(strtoupper($order['payment_method']), ENT_QUOTES, 'UTF-8') : 'N/A'; ?>
                                    </td>
                                    <td><?= date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        
                                        <a href="order-edit.php?id=<?= $order['id']; ?>" class="btn btn-success btn-sm me-1">
                                            <i class="fas fa-edit me-1"></i>Update
                                        </a>
                                        <a href="receipt.php?order_id=<?= $order['id']; ?>" target="_blank" class="btn btn-secondary btn-sm me-1">
                                            <i class="fas fa-print me-1"></i>Print
                                        </a>
                                        <form action="code.php" method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                                            <input type="hidden" name="order_status" value="confirmed">
                                            <button type="submit" class="btn btn-primary btn-sm me-1"><i class="fas fa-check me-1"></i>Approve</button>
                                        </form>
                                        <?php if(isArchived('order', $order['id'])): ?>
                                            <a href="orders-unarchive.php?id=<?= $order['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Restore this order from archive?')"><i class="fas fa-undo me-1"></i>Restore</a>
                                        <?php else: ?>
                                            <a href="orders-archive.php?id=<?= $order['id']; ?>" class="btn btn-warning btn-sm" onclick="return confirm('Move this order to archive?')"><i class="fas fa-archive me-1"></i>Move to Archive</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center p-5"><i class="fas fa-shopping-cart fa-3x mb-3 d-block text-muted"></i><h5>No orders found</h5><p class="text-muted">Orders will appear here when customers place them</p></td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Pagination controls
$totalPages = ($perPage > 0) ? ceil($total / $perPage) : 1;
if ($totalPages > 1) {
    echo '<nav aria-label="Orders pagination" class="mt-3"><ul class="pagination justify-content-center">';
    $queryBase = $_GET;
    for ($i = 1; $i <= $totalPages; $i++) {
        $queryBase['p'] = $i;
        $qs = http_build_query($queryBase);
        $active = $i === $page ? ' active' : '';
        echo "<li class='page-item$active'><a class='page-link' href='?{$qs}'>$i</a></li>";
    }
    echo '</ul></nav>';
}
?>

<?php include ('includes/footer.php'); ?>