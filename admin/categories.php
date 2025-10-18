<?php include ('includes/header.php'); ?>

<div class="container-fluid px-4">
    <div class="card enhanced-table-card">
        <div class="card-header enhanced-table-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-tags me-2"></i>
                    <h4 class="mb-0 d-inline">Categories Management</h4>
                </div>
                <div class="d-flex align-items-center">
                    <?php $showArchived = isset($_GET['archived']) && $_GET['archived'] === '1'; ?>
                    <form class="me-3" method="GET" action="categories.php">
                        <div class="input-group">
                            <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control form-control-sm" placeholder="Search by id, name or description">
                            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                    <?php if($showArchived): ?>
                        <a href="categories.php" class="btn btn-outline-secondary btn-sm me-2">Show Active</a>
                    <?php else: ?>
                        <a href="categories.php?archived=1" class="btn btn-outline-secondary btn-sm me-2">Show Archived</a>
                    <?php endif; ?>
                    <a href="category-create.php" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Add Category
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <?php alertMessage(); ?>

            <div class="table-responsive">
                <table class="table enhanced-table mb-0">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-2"></i>ID</th>
                            <th><i class="fas fa-tag me-2"></i>Name</th>
                            <th><i class="fas fa-info-circle me-2"></i>Description</th>
                            <th><i class="fas fa-eye me-2"></i>Status</th>
                            <th><i class="fas fa-cogs me-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            // Build prepared statement for categories with optional search
                            $sql = "SELECT * FROM categories";
                            $params = [];
                            $types = '';
                            if (isset($_GET['q']) && trim($_GET['q']) !== '') {
                                $q = trim($_GET['q']);
                                if (ctype_digit($q)) {
                                    $sql .= " WHERE id = ?";
                                    $params[] = (int)$q;
                                    $types .= 'i';
                                } else {
                                    $sql .= " WHERE name LIKE ? OR description LIKE ?";
                                    $like = '%' . $q . '%';
                                    $params[] = $like; $params[] = $like;
                                    $types .= 'ss';
                                }
                            }

                            // archived filter: prefer is_archived column, otherwise archived_items table
                            $table = getTableForEntity('category');
                            if (columnExists($table, 'is_archived')) {
                                if ($showArchived) {
                                    $sql .= (stripos($sql, 'WHERE') !== false) ? " AND is_archived = 1" : " WHERE is_archived = 1";
                                } else {
                                    $sql .= (stripos($sql, 'WHERE') !== false) ? " AND is_archived != 1" : " WHERE is_archived != 1";
                                }
                            } else {
                                // ensure archived_items exists before using it
                                ensureArchivedTableExists();
                                $archSub = "id " . ($showArchived ? "IN" : "NOT IN") . " (SELECT entity_id FROM archived_items WHERE entity='category')";
                                $sql .= (stripos($sql, 'WHERE') !== false) ? " AND $archSub" : " WHERE $archSub";
                            }

                            $sql .= " ORDER BY id DESC";
                            $stmt = $conn->prepare($sql);
                            if ($types !== '') {
                                $stmt->bind_param($types, ...$params);
                            }
                            $stmt->execute();
                            $categoriesRes = $stmt->get_result();

                            if($categoriesRes && mysqli_num_rows($categoriesRes) > 0){
                                foreach($categoriesRes as $item){
                                    ?>
                                        <tr>
                                            <td><strong>#<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                            <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars(substr($item['description'], 0, 50), ENT_QUOTES, 'UTF-8'); ?>...</td>
                                            <td>
                                                <?php
                                                    if (isArchived('category', $item['id'])) {
                                                        echo "<span class='badge bg-dark status-badge'><i class='fas fa-archive me-1'></i>Archived</span>";
                                                    } else if($item['status'] == 0){
                                                        echo "<span class='badge bg-success status-badge'><i class='fas fa-eye me-1'></i>Visible</span>";
                                                    } else {
                                                        echo "<span class='badge bg-secondary status-badge'><i class='fas fa-eye-slash me-1'></i>Hidden</span>";
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="category-edit.php?id=<?= $item['id']; ?>" class="btn btn-primary action-btn btn-sm">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                                <a href="category-delete.php?id=<?= $item['id']; ?>" class="btn btn-danger action-btn btn-sm" onclick="return confirm('Are you sure you want to delete this category?')">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5' class='no-records'><i class='fas fa-inbox fa-3x mb-3 d-block'></i><h5>No categories found</h5><p class='text-muted'>Start by creating your first category</p></td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include ('includes/footer.php'); ?>