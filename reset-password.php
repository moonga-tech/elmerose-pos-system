<?php 

include('config/function.php');

// Two supported flows:
// 1) Legacy token-based: ?token=...
// 2) Secret-question verified: session contains password_reset_email

$token = '';
$tokenValid = false;
$sessionEmail = $_SESSION['password_reset_email'] ?? '';
$sessionRole = $_SESSION['password_reset_role'] ?? '';

if(isset($_GET['token']) && $_GET['token'] != '') {
    $token = validated($_GET['token']);
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if(mysqli_num_rows($result) > 0) {
        $tokenValid = true;
    } else {
        redirect("forgot-password.php", "Invalid or expired token!");
        exit();
    }
} else if($sessionEmail) {
    // allow session-based reset
    $tokenValid = true;
} else {
    redirect("forgot-password.php", "No valid reset session or token provided");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Elmerose POS</title>
    <link href="assets/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border: none;
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header bg-transparent border-0">
                                    <div class="text-center">
                                        <i class="fas fa-key fa-3x text-primary mb-3"></i>
                                        <h3 class="font-weight-light my-2">Reset Password</h3>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php alertMessage(); ?>
                                    <form action="forgot-password-code.php" method="POST">
                                        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputPassword" type="password" name="password" placeholder="Password" required />
                                            <label for="inputPassword">New Password</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputConfirmPassword" type="password" name="confirm_password" placeholder="Confirm Password" required />
                                            <label for="inputConfirmPassword">Confirm Password</label>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <a class="small" href="customer-login.php">Return to login</a>
                                            <button class="btn btn-primary" type="submit" name="resetPassword">Reset Password</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>