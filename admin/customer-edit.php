<?php include ('includes/header.php'); ?>

<div class="container-fluid px-4">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Edit Customer</h4>
        </div>
        <div class="card-body">
            <?php alertMessage(); ?>

            <?php
            if(isset($_GET['id'])) {
                $customerId = validated($_GET['id']);
                $customer = mysqli_query($conn, "SELECT * FROM customers WHERE id='$customerId'");
                
                if(mysqli_num_rows($customer) == 1) {
                    $customerData = mysqli_fetch_assoc($customer);
                    ?>
                    <form action="code.php" method="POST">
                        <input type="hidden" name="customerId" value="<?= $customerData['id']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Name *</label>
                                <input type="text" name="name" required class="form-control" value="<?= htmlspecialchars($customerData['name'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email *</label>
                                <input type="email" name="email" required class="form-control" value="<?= htmlspecialchars($customerData['email'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($customerData['phone'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Status</label>
                                <select name="status" class="form-select">
                                    <option value="1" <?= $customerData['status'] == 1 ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?= $customerData['status'] == 0 ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Address</label>
                                <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($customerData['address'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <button type="submit" name="updateCustomer" class="btn btn-primary">Update Customer</button>
                                <a href="customers.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                    <?php
                } else {
                    echo '<h5>Customer not found</h5>';
                }
            } else {
                echo '<h5>No customer ID provided</h5>';
            }
            ?>
        </div>
    </div>
</div>

<?php include ('includes/footer.php'); ?>