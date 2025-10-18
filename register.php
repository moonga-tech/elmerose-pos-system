<?php include('config/function.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Elmerose POS</title>
    <link href="assets/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .register-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            transform: translateY(-2px);
        }
        .btn-register {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card register-card shadow-lg mt-5">
                    <div class="card-header bg-transparent border-0">
                        <div class="text-center">
                            <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                            <h3 class="font-weight-light">Create Account</h3>
                            <p class="text-muted">Join us to start ordering</p>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php alertMessage(); ?>
                        <form action="register-code.php" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user me-2 text-primary"></i>Full Name
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2 text-primary"></i>Email
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2 text-primary"></i>Password
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Create password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-2 text-primary"></i>Phone
                                    </label>
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter phone number" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="secret_question" class="form-label">
                                        <i class="fas fa-question-circle me-2 text-primary"></i>Secret Question
                                    </label>
                                    <input type="text" class="form-control" id="secret_question" name="secret_question" placeholder="e.g. What is my favourite dog?" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="secret_answer" class="form-label">
                                        <i class="fas fa-lock me-2 text-primary"></i>Secret Answer
                                    </label>
                                    <input type="text" class="form-control" id="secret_answer" name="secret_answer" placeholder="Your answer" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>Address
                                </label>
                                <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter your address" required></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-register text-white" name="registerCustomer">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </div>
                            <div class="text-center mt-3">
                                <p class="mb-0">Already have an account? <a href="customer-login.php" class="text-primary">Login here</a></p>
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