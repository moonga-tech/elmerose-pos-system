<?php include ('includes/header.php'); ?>

<div class="container-fluid px-4">
    <div class="card enhanced-table-card">
        <div class="card-header enhanced-table-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-users me-2"></i>
                    <h4 class="mb-0 d-inline">Admin & Staff Management</h4>
                </div>
                <a href="admin-create.php" class="btn btn-light btn-sm">
                    <i class="fas fa-user-plus me-1"></i>Add Admin
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <?php alertMessage(); ?>

            <div class="table-responsive">
                <table class="table enhanced-table mb-0">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user me-2"></i>Admin Info</th>
                            <th><i class="fas fa-envelope me-2"></i>Email</th>
                            <th><i class="fas fa-phone me-2"></i>Phone</th>
                            <th><i class="fas fa-shield-alt me-2"></i>Status</th>
                            <th><i class="fas fa-cogs me-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $admins = getAll("admins");

                            if(mysqli_num_rows($admins) > 0){
                                foreach($admins as $item){
                                    $initials = strtoupper(substr($item['name'], 0, 1) . substr(strstr($item['name'], ' '), 1, 1));
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="admin-info">
                                                    <div class="admin-avatar"><?= $initials; ?></div>
                                                    <div class="admin-details">
                                                        <div class="admin-name"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="admin-role">Administrator</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($item['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($item['phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php
                                                    if($item['is_ban'] == 0){
                                                        echo "<span class='badge bg-success'><i class='fas fa-check me-1'></i>Active</span>";
                                                    } else {
                                                        echo "<span class='badge bg-danger'><i class='fas fa-ban me-1'></i>Banned</span>";
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="admin-edit.php?id=<?= $item['id']; ?>" class="btn btn-primary action-btn btn-sm">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                                <a href="admin-delete.php?id=<?= $item['id']; ?>" class="btn btn-danger action-btn btn-sm" onclick="return confirm('Are you sure you want to delete this admin?')">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php 
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center p-5'><i class='fas fa-users fa-3x mb-3 d-block text-muted'></i><h5>No admins found</h5><p class='text-muted'>Start by adding your first admin</p></td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include ('includes/footer.php'); ?>