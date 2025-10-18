<?php include ('includes/header.php'); ?>
<style>
.enhanced-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
}
.enhanced-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px 15px 0 0;
    padding: 20px;
    border: none;
}
.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 15px;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.8);
}
.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    transform: translateY(-2px);
}
.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}
.btn-enhanced {
    background: linear-gradient(45deg, #667eea, #764ba2);
    border: none;
    border-radius: 10px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
}
.btn-enhanced:hover {
    background: linear-gradient(45deg, #764ba2, #667eea);
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}
.file-upload {
    position: relative;
    overflow: hidden;
    display: inline-block;
    width: 100%;
}
.file-upload input[type=file] {
    position: absolute;
    left: -9999px;
}
.file-upload-label {
    display: block;
    padding: 12px 15px;
    border: 2px dashed #667eea;
    border-radius: 10px;
    background: rgba(102, 126, 234, 0.1);
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}
.file-upload-label:hover {
    background: rgba(102, 126, 234, 0.2);
    border-color: #764ba2;
}
</style>

<div class="container-fluid px-4">
    <div class="card enhanced-card">
        <div class="card-header enhanced-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-box me-2"></i>
                    <h4 class="mb-0 d-inline">Create Product</h4>
                </div>
                <a href="products.php" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Go Back
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            <?php alertMessage(); ?>
            
            <form action="code.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="category_id" class="form-label">
                            <i class="fas fa-tags me-2 text-primary"></i>Category
                        </label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Choose Category...</option>
                            <?php
                            $categories = getAll('categories');
                            if(mysqli_num_rows($categories) > 0){
                                foreach($categories as $item){
                                    ?>
                                        <option value="<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php 
                                }
                            } else {
                                echo "<option value=''>No category available</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="name" class="form-label">
                            <i class="fas fa-cube me-2 text-primary"></i>Product Name
                        </label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter product name" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="description" class="form-label">
                        <i class="fas fa-align-left me-2 text-primary"></i>Description
                    </label>
                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Enter product description" required></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <label for="price" class="form-label">
                            <!-- <i class="fas fa-dollar-sign me-2 text-primary"></i> --> ₱ Price
                        </label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" placeholder="0.00" required>
                    </div>
                    <div class="col-md-4 mb-4">
                        <label for="quantity" class="form-label">
                            <i class="fas fa-boxes me-2 text-primary"></i>Quantity
                        </label>
                        <input type="number" class="form-control" id="quantity" name="quantity" placeholder="0" required>
                    </div>
                    <div class="col-md-4 mb-4">
                        <label class="form-label">
                            <i class="fas fa-eye me-2 text-primary"></i>Status
                        </label>
                        <div class="d-flex align-items-center p-3" style="background: rgba(102, 126, 234, 0.1); border-radius: 10px;">
                            <input type="checkbox" id="status" name="status" value="1" class="me-2" style="width: 20px; height: 20px; accent-color: #667eea;">
                            <label for="status" class="mb-0">Hide Product</label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <label class="form-label">
                            <i class="fas fa-calendar-times me-2 text-primary"></i>Expiry Settings
                        </label>
                        <div class="d-flex align-items-center p-3" style="background: rgba(102, 126, 234, 0.1); border-radius: 10px;">
                            <input type="checkbox" id="has_expiry" name="has_expiry" value="1" class="me-2" style="width: 20px; height: 20px; accent-color: #667eea;" onchange="toggleExpiryFields()">
                            <label for="has_expiry" class="mb-0">Product Has Expiry Date</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4" id="expiry_date_field" style="display: none;">
                        <label for="expiry_date" class="form-label">
                            <i class="fas fa-calendar me-2 text-primary"></i>Expiry Date
                        </label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                    </div>
                    <div class="col-md-4 mb-4" id="alert_days_field" style="display: none;">
                        <label for="expiry_alert_days" class="form-label">
                            <i class="fas fa-bell me-2 text-primary"></i>Alert Days Before Expiry
                        </label>
                        <input type="number" class="form-control" id="expiry_alert_days" name="expiry_alert_days" value="30" min="1">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-image me-2 text-primary"></i>Product Image
                    </label>
                    <div class="file-upload">
                        <input type="file" id="image" name="image" accept="image/*">
                        <label for="image" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt fa-2x mb-2 d-block"></i>
                            <strong>Click to upload image</strong>
                            <small class="d-block text-muted">PNG, JPG, GIF up to 10MB</small>
                        </label>
                    </div>
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-enhanced text-white" name="saveProduct">
                        <i class="fas fa-save me-2"></i>Create Product
                    </button>
                </div>
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
        document.getElementById('expiry_date').required = true;
    } else {
        expiryDateField.style.display = 'none';
        alertDaysField.style.display = 'none';
        document.getElementById('expiry_date').required = false;
        document.getElementById('expiry_date').value = '';
    }
}
</script>

<?php include ('includes/footer.php'); ?>