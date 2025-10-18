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
.checkbox-container {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: rgba(102, 126, 234, 0.1);
    border-radius: 10px;
    border: 2px solid rgba(102, 126, 234, 0.2);
}
.custom-checkbox {
    width: 20px;
    height: 20px;
    accent-color: #667eea;
}
</style>

<div class="container-fluid px-4">
    <div class="card enhanced-card">
        <div class="card-header enhanced-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-user-plus me-2"></i>
                    <h4 class="mb-0 d-inline">Create Admin/Staff</h4>
                </div>
                <a href="admins.php" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Go Back
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            <form action="code.php" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="name" class="form-label">
                            <i class="fas fa-user me-2 text-primary"></i>Full Name
                        </label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter full name" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2 text-primary"></i>Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2 text-primary"></i>Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone me-2 text-primary"></i>Phone Number
                        </label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter phone number" required>
                    </div>
                </div>
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <label for="secret_question" class="form-label">
                                <i class="fas fa-question-circle me-2 text-primary"></i>Secret Question
                            </label>
                            <input type="text" class="form-control" id="secret_question" name="secret_question" placeholder="e.g. What is my favourite dog?" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <label for="secret_answer" class="form-label">
                                <i class="fas fa-lock me-2 text-primary"></i>Secret Answer
                            </label>
                            <input type="text" class="form-control" id="secret_answer" name="secret_answer" placeholder="Your answer" required>
                        </div>
                    </div>
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-ban me-2 text-primary"></i>Account Status
                    </label>
                    <div class="checkbox-container">
                        <input type="checkbox" id="is_ban" name="is_ban" value="1" class="custom-checkbox">
                        <label for="is_ban" class="mb-0">Ban this user</label>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-enhanced text-white" name="saveAdmin">
                        <i class="fas fa-save me-2"></i>Create Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include ('includes/footer.php'); ?>