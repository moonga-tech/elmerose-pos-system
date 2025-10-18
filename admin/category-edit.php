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
.form-control {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 15px;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.8);
}
.form-control:focus {
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
.status-container {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
    background: rgba(102, 126, 234, 0.1);
    border-radius: 10px;
    border: 2px solid rgba(102, 126, 234, 0.2);
}
</style>

<div class="container-fluid px-4">
    <div class="card enhanced-card">
        <div class="card-header enhanced-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-edit me-2"></i>
                    <h4 class="mb-0 d-inline">Edit Category</h4>
                </div>
                <a href="categories.php" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Go Back
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            <?php alertMessage(); ?>
            
            <form action="code.php" method="POST">
                <?php 
                $paramValue = checkParamId('id');
                if(!is_numeric($paramValue)) {
                    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>' .htmlspecialchars($paramValue, ENT_QUOTES, 'UTF-8'). '</div>';
                    return false;
                }   
                $category = getById('categories', $paramValue); 
                if($category['status'] == 200) {
                ?>
                <input type="hidden" name="categoryId" value="<?= htmlspecialchars($category['data']['id'], ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="mb-4">
                    <label for="name" class="form-label">
                        <i class="fas fa-tag me-2 text-primary"></i>Category Name
                    </label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($category['data']['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label for="description" class="form-label">
                        <i class="fas fa-align-left me-2 text-primary"></i>Description
                    </label>
                    <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($category['data']['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-eye me-2 text-primary"></i>Visibility Status
                    </label>
                    <div class="status-container">
                        <input type="checkbox" id="status" name="status" value="1" <?= $category['data']['status'] == 1 ? 'checked' : ''; ?> style="width: 20px; height: 20px; accent-color: #667eea;">
                        <label for="status" class="mb-0">
                            <strong>Hide Category</strong>
                            <small class="d-block text-muted">Check to hide this category from public view</small>
                        </label>
                    </div>
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-enhanced text-white" name="updateCategory">
                        <i class="fas fa-save me-2"></i>Update Category
                    </button>
                </div>
                
                <?php
                } else {
                    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>' .htmlspecialchars($category['message'], ENT_QUOTES, 'UTF-8'). '</div>';
                }
                ?>
            </form>
        </div>
    </div>
</div>

<?php include ('includes/footer.php'); ?>