<?php include ('includes/header.php'); ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Suppliers</h1>
    <?php alertMessage(); ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-truck me-2"></i>Supplier List</span>
            <a href="supplier-create.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Supplier</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $suppliers = getAll('suppliers');
                        if($suppliers && mysqli_num_rows($suppliers) > 0){
                            foreach($suppliers as $row){
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($row['contact_person'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($row['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($row['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= (isset($row['status']) && $row['status']==1) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
                                    <td>
                                        <a href="supplier-edit.php?id=<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <form action="code.php" method="POST" style="display:inline-block; margin:0 4px;">
                                            <input type="hidden" name="supplier_id" value="<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" name="deleteSupplier" class="btn btn-sm btn-danger" onclick="return confirm('Delete this supplier?');"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="8" class="text-center">No suppliers found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include ('includes/footer.php'); ?>
