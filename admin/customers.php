<?php include ('includes/header.php'); ?>

<div class="container-fluid px-4">
    <div class="card table-card">
        <div class="card-header table-header">
                <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-users me-2"></i>
                    <h4 class="mb-0 d-inline">Customers Management</h4>
                </div>
                <div class="d-flex align-items-center">
                    <?php $showArchived = isset($_GET['archived']) && $_GET['archived'] === '1'; ?>
                    <form class="me-3" method="GET" action="customers.php">
                        <div class="input-group">
                            <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control form-control-sm" placeholder="Search customer by name or email">
                            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                    <?php if($showArchived): ?>
                        <a href="customers.php" class="btn btn-outline-secondary btn-sm me-2">Show Active</a>
                    <?php else: ?>
                        <a href="customers.php?archived=1" class="btn btn-outline-secondary btn-sm me-2">Show Archived</a>
                    <?php endif; ?>
                    <span class="badge bg-light text-dark">Total Customers: <?= mysqli_num_rows(mysqli_query($conn, "SELECT * FROM customers")); ?></span>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <?php alertMessage(); ?>

            <div class="table-responsive">
                <table class="table enhanced-table table-striped mb-0">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user me-2"></i>Customer Info</th>
                            <th><i class="fas fa-envelope me-2"></i>Contact</th>
                            <th><i class="fas fa-map-marker-alt me-2"></i>Address</th>
                            <th><i class="fas fa-calendar me-2"></i>Joined</th>
                            <th><i class="fas fa-toggle-on me-2"></i>Status</th>
                            <th><i class="fas fa-cogs me-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Apply optional search filter and archived filter
                        $searchTerm = '';
                        $showArchived = isset($_GET['archived']) && $_GET['archived'] === '1';
                        if (isset($_GET['q']) && trim($_GET['q']) !== '') {
                            $searchTerm = validated($_GET['q']);
                            // Escape wildcard characters to prevent unintended LIKE behavior
                            $searchTermEscaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $searchTerm);
                            $sql = "SELECT * FROM customers WHERE (name LIKE '%". $searchTermEscaped ."%' OR email LIKE '%". $searchTermEscaped ."%')";
                        } else {
                            $sql = "SELECT * FROM customers";
                        }
                        // archived filter: prefer is_archived column, otherwise archived_items table
                        $table = getTableForEntity('customer');
                        if (columnExists($table, 'is_archived')) {
                            if ($showArchived) {
                                $sql .= (stripos($sql, 'WHERE') !== false) ? " AND is_archived = 1" : " WHERE is_archived = 1";
                            } else {
                                $sql .= (stripos($sql, 'WHERE') !== false) ? " AND is_archived != 1" : " WHERE is_archived != 1";
                            }
                        } else {
                            // ensure archived_items exists before using it
                            ensureArchivedTableExists();
                            $archSub = "id " . ($showArchived ? "IN" : "NOT IN") . " (SELECT entity_id FROM archived_items WHERE entity='customer')";
                            $sql .= (stripos($sql, 'WHERE') !== false) ? " AND $archSub" : " WHERE $archSub";
                        }

                        $sql .= " ORDER BY created_at DESC";
                        $customers = mysqli_query($conn, $sql);
                        
                        if(mysqli_num_rows($customers) > 0) {
                            while($customer = mysqli_fetch_assoc($customers)) {
                                $initials = strtoupper(substr($customer['name'], 0, 1) . substr(strstr($customer['name'], ' '), 1, 1));
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="customer-avatar"><?= $initials; ?></div>
                                            <div>
                                                <strong><?= htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <br><small class="text-muted">Customer</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($customer['email'], ENT_QUOTES, 'UTF-8'); ?>
                                            <br><i class="fas fa-phone me-1"></i><?= htmlspecialchars($customer['phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars(substr($customer['address'], 0, 50), ENT_QUOTES, 'UTF-8'); ?>...</td>
                                    <td><?= date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                    <td>
                                        <?php if (isArchived('customer', $customer['id'])): ?>
                                            <span class="badge bg-dark status-badge"><i class="fas fa-archive me-1"></i>Archived</span>
                                        <?php else: ?>
                                            <span class="status-badge <?= $customer['status'] == 1 ? 'status-active' : 'status-inactive'; ?>">
                                                <?= $customer['status'] == 1 ? '<h6 class="text-success text-center">Active</h6>' : '<h6 class="text-danger text-center">Inactive</h6>' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="customer-edit.php?id=<?= $customer['id']; ?>" class="btn btn-success btn-sm">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <?php if(isset($customer['status']) && $customer['status'] == 2): ?>
                                            <a href="customer-unarchive.php?id=<?= $customer['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Restore this customer from archive?')">
                                                <i class="fas fa-undo me-1"></i>Restore
                                            </a>
                                        <?php else: ?>
                                            <a href="customer-archive.php?id=<?= $customer['id']; ?>" class="btn btn-warning btn-sm" onclick="return confirm('Move this customer to archive?')">
                                                <i class="fas fa-archive me-1"></i>Move to Archive
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center p-5"><i class="fas fa-users fa-3x mb-3 d-block text-muted"></i><h5>No customers found</h5><p class="text-muted">Customers will appear here when they register</p></td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<?php include ('includes/footer.php'); ?>