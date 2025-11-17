<?php include 'includes/header.php'; ?>

<div class="container-fluid px-4">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            <h4 class="mb-0">Edit Product
                <a href="products.php" class="btn btn-primary float-end">Go Back</a>
            </h4>
        </div>
        <div class="card-body">

            <?php alertMessage(); ?>

            <form action="code.php" method="POST" enctype="multipart/form-data">

                <?php
                    $paramValue = checkParamId('id');
                    if(!is_numeric($paramValue)){
                        echo "<h4>Invalid Id</h4>";
                        return false;
                    }

                    $product = getByID('products', $paramValue);

                    if($product) {
                        if($product['status'] == 200) {
                            ?>
                            <input type="hidden" name="productId" value="<?= $product['data']['id']; ?>">

                            <div class="col-md-12 mb-3">
                                <label for="category_id" class="form-label">Select Category</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Choose...</option>
                                    <?php
                                    $categories = getAll('categories');
                                    
                                    if(mysqli_num_rows($categories) > 0){
                                        if(mysqli_num_rows($categories) > 0) {
                                            foreach($categories as $item){
                                                ?>
                                                <option value="<?= $item['id']; ?>" <?= $item['id'] == $product['data']['category_id'] ? 'selected' : ''; ?>><?= $item['name']; ?></option>
                                                <?php 
                                            }
                                        } else {
                                            echo "<option value=''>No category available</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No category available</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?= $product['data']['name']; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?= $product['data']['description']; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" class="form-control" id="price" name="price" required value="<?= $product['data']['price']; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" class="form-control" id="image" name="image">
                                <img src="../<?= $product['data']['image']; ?>" alt="img" style="width: 40px; height: 40px;">
                            </div>

                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" required value="<?= $product['data']['quantity']; ?>">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="status">Status (Unchecked=Visible, Checked=Hidden)</label>
                                <br />
                                <input type="checkbox" id="status" name="status" value="1" 
                                    style="width: 20px; height: 20px;" 
                                    <?= $product['data']['status'] == 0 ? 'checked' : ''; ?>>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="has_expiry">Has Expiry Date</label>
                                <input type="checkbox" id="has_expiry" name="has_expiry" value="1" 
                                    style="width: 20px; height: 20px;" 
                                    <?= isset($product['data']['has_expiry']) && $product['data']['has_expiry'] == 1 ? 'checked' : ''; ?>
                                    onchange="toggleExpiryFields()">
                            </div>
                            
                            <div class="col-md-4 mb-3" id="expiry_date_field" style="<?= !isset($product['data']['has_expiry']) || $product['data']['has_expiry'] != 1 ? 'display:none;' : ''; ?>">
                                <label for="expiry_date">Expiry Date</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date" 
                                    value="<?= htmlspecialchars($product['data']['expiry_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3" id="alert_days_field" style="<?= !isset($product['data']['has_expiry']) || $product['data']['has_expiry'] != 1 ? 'display:none;' : ''; ?>">
                                <label for="expiry_alert_days">Alert Days Before Expiry</label>
                                <input type="number" class="form-control" id="expiry_alert_days" name="expiry_alert_days" 
                                    value="<?= htmlspecialchars($product['data']['expiry_alert_days'] ?? '30', ENT_QUOTES, 'UTF-8'); ?>" min="1">
                            </div>

                            <div class="col-md-6 mb-3 text-center button-div">
                                <button type="submit" class="btn btn-primary button-style" name="updateProduct">Update</button>
                            </div>
                            <?php
                        } else {
                            echo "<h4>{$product['message']}</h4>";
                        }
                    } else {
                        echo "<h4>No Such Id Found</h4>";
                        return false;
                    }
                ?>
            </form>

        </div>

    </div>

</div>

<script>
function toggleExpiryFields() {
    const hasExpiry = document.getElementById('has_expiry').checked;
    const expiryDateField = document.getElementById('expiry_date_field');
    const alertDaysField = document.getElementById('alert_days_field');
    
    if (hasExpiry) {
        expiryDateField.style.display = 'block';
        alertDaysField.style.display = 'block';
    } else {
        expiryDateField.style.display = 'none';
        alertDaysField.style.display = 'none';
        document.getElementById('expiry_date').value = '';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
