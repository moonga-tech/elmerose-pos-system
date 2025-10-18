<?php
include('config/function.php');

if(isset($_POST['forgotPassword'])) {
    $email = validated($_POST['email']);
    
    if($email != '') {
        // Check admins first, then customers
        $found_table = null;
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result && mysqli_num_rows($result) > 0) {
            $found_table = 'admins';
        } else {
            $stmt2 = $conn->prepare("SELECT * FROM customers WHERE email = ?");
            $stmt2->bind_param("s", $email);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            if($result2 && mysqli_num_rows($result2) > 0) {
                $found_table = 'customers';
            }
        }

        if($found_table) {
            // redirect to secret question verification page
            $roleParam = $found_table === 'customers' ? 'customer' : 'admin';
            header('Location: verify-secret.php?email=' . urlencode($email) . '&role=' . $roleParam);
            exit();
        } else {
            redirect("forgot-password.php", "Email not found!");
        }
    } else {
        redirect("forgot-password.php", "Email is required!");
    }
}

// Verification of secret answer (from verify-secret.php)
if(isset($_POST['action']) && $_POST['action'] === 'verifySecret') {
    $email = validated($_POST['email'] ?? '');
    $role = isset($_POST['role']) && $_POST['role'] === 'customer' ? 'customer' : 'admin';
    $secret_answer = $_POST['secret_answer'] ?? '';

    if($email === '' || $secret_answer === '') {
        redirect('verify-secret.php?email=' . urlencode($email) . '&role=' . $role, 'Please provide the answer');
        exit();
    }

    $table = $role === 'customer' ? 'customers' : 'admins';
    $stmt = $conn->prepare("SELECT secret_answer_hash FROM $table WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hash = $row['secret_answer_hash'] ?? '';
        if($hash && password_verify($secret_answer, $hash)) {
            // success: allow reset via session
            $_SESSION['password_reset_email'] = $email;
            $_SESSION['password_reset_role'] = $role;
            redirect('reset-password.php', 'Secret verified. You may now reset your password.');
            exit();
        } else {
            redirect('verify-secret.php?email=' . urlencode($email) . '&role=' . $role, 'Incorrect answer');
            exit();
        }
    } else {
        redirect('forgot-password.php', 'Email not found');
        exit();
    }
}

if(isset($_POST['resetPassword'])) {
    // Support two flows: token-based reset (legacy) OR session-authenticated reset via secret QA
    $role = isset($_POST['role']) && $_POST['role'] === 'customer' ? 'customer' : 'admin';
    $email = '';
    $token = '';
    if (isset($_SESSION['password_reset_email'])) {
        $email = $_SESSION['password_reset_email'];
        $role = $_SESSION['password_reset_role'] ?? $role;
    } else {
        $token = validated($_POST['token'] ?? '');
    }
    $password = validated($_POST['password']);
    $confirmPassword = validated($_POST['confirm_password']);
    
    if($password != $confirmPassword) {
        redirect("reset-password.php?token=" . urlencode($token) . "&role=" . $role, "Passwords do not match!");
        exit();
    }
    
    // If token present, verify legacy token path
    if (isset($token) && $token !== '') {
        $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if(mysqli_num_rows($result) > 0) {
            $resetData = mysqli_fetch_assoc($result);
            $email = $resetData['email'];
            // proceed to update below
        } else {
            redirect("forgot-password.php?role=" . ($role === 'customer' ? 'customer' : 'admin'), "Invalid or expired token!");
            exit();
        }
    }

    if ($email === '') {
        redirect("forgot-password.php", "No email specified for password reset");
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $updateTable = $role === 'customer' ? 'customers' : 'admins';
    $updateStmt = $conn->prepare("UPDATE $updateTable SET password = ? WHERE email = ?");
    $updateStmt->bind_param("ss", $hashedPassword, $email);

    if($updateStmt->execute()) {
        // If we used a token, mark it used
        if (isset($token) && $token !== '') {
            $markUsedStmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $markUsedStmt->bind_param("s", $token);
            $markUsedStmt->execute();
        }
        // clear session-based reset
        unset($_SESSION['password_reset_email']);
        unset($_SESSION['password_reset_role']);

        if($role === 'customer') {
            redirect("customer-login.php", "Password reset successfully!");
        } else {
            redirect("login.php", "Password reset successfully!");
        }
    } else {
        redirect("reset-password.php?role=" . $role, "Failed to reset password!");
    }
}
?>