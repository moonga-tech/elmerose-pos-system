<?php include('config/function.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - Elmerose POS</title>
    <link href="assets/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/customer-login.css">
    
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card login-card shadow-lg mt-5">
                    <div class="card-header bg-transparent border-0">
                        <div class="text-center mt-3">
                            <i class="fas fa-shopping-cart fa-3x text-primary mb-1"></i>
                            <h3 class="font-weight-light">Customer Login</h3>
                            <p class="text-muted">Access your account</p>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php alertMessage(); ?>
                        <form action="customer-login-code.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2 text-primary"></i>Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2 text-primary"></i>Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-login text-white" name="customerLogin">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>
                            <div class="text-center mt-3">
                                <p class="mb-0">Don't have an account? <a href="register.php" class="text-primary text-decoration-none">Register here</a></p>
                            </div>
                            <div class="text-center mt-3">
                                <a href="forgot-password.php?role=customer" class="text-primary text-decoration-none">Forgot Password?</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>