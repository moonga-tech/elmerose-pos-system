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
.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(45deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    font-weight: 600;
    margin: 0 auto 20px;
}
</style>

<div class="container-fluid px-4">
    <div class="card enhanced-card">
        <div class="card-header enhanced-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-user-edit me-2"></i>
                    <h4 class="mb-0 d-inline">My Profile</h4>
                </div>
                <a href="index.php" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            <?php alertMessage(); ?>
            
            <?php
            $adminId = $_SESSION['loggedInUser']['id'];
            $adminData = getById("admins", $adminId);
            
            if($adminData && $adminData['status'] == 200) {
                $admin = $adminData['data'];
                $initials = strtoupper(substr($admin['name'], 0, 1) . substr(strstr($admin['name'], ' '), 1, 1));
            ?>
            
            <div class="text-center mb-4">
                <div class="profile-avatar"><?= $initials; ?></div>
                <h5><?= htmlspecialchars($admin['name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                <p class="text-muted">Administrator</p>
            </div>
            
            <form action="code.php" method="POST">
                <input type="hidden" name="profileId" value="<?= $admin['id']; ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="name" class="form-label">
                            <i class="fas fa-user me-2 text-primary"></i>Full Name
                        </label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($admin['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2 text-primary"></i>Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($admin['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone me-2 text-primary"></i>Phone Number
                        </label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($admin['phone'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="current_password" class="form-label">
                            <i class="fas fa-lock me-2 text-primary"></i>Current Password
                        </label>
                        <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter current password to update">
                    </div>
                </div>

                <div class="mb-3 text-center text-warning">
                    <small>Leave blank to keep current</small>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="new_password" class="form-label">
                            <i class="fas fa-key me-2 text-primary"></i>New Password
                        </label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Leave blank to keep current">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-key me-2 text-primary"></i>Confirm New Password
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                    </div>
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-enhanced text-white" name="updateProfile">
                        <i class="fas fa-save me-2"></i>Update Profile
                    </button>
                </div>
            </form>
            
            <?php
            } else {
                echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Profile data not found</div>';
            }
            ?>
        </div>
    </div>
</div>

<?php include ('includes/footer.php'); ?>