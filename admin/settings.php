<?php
include('includes/header.php');

// Simple admin settings page for editable configuration values
// Header includes authentication checks

$codFee = get_setting('cod_delivery_fee', COD_DELIVERY_FEE);
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Settings</h1>
    <?php alertMessage(); ?>
    <div class="card mt-3">
        <div class="card-body">
            <form action="settings-save.php" method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cash on Delivery Fee</label>
                    <input type="text" name="cod_delivery_fee" class="form-control" value="<?= htmlspecialchars($codFee, ENT_QUOTES, 'UTF-8'); ?>" required>
                    <div class="form-text">Enter a numeric value (e.g., 50.00)</div>
                </div>

                <div class="col-12 mt-3">
                    <button class="btn btn-primary" type="submit">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php');
