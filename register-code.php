<?php
include('config/function.php');

if(isset($_POST['registerCustomer'])) {
    $name = validated($_POST['name']);
    $email = validated($_POST['email']);
    $password = validated($_POST['password']);
    $phone = validated($_POST['phone']);
    $address = validated($_POST['address']);
    
    if($name != '' && $email != '' && $password != '' && $phone != '' && $address != '') {
        $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if(mysqli_num_rows($result) > 0) {
            redirect("register.php", "Email already exists!", "error");
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            // ensure secret columns exist
            ensureSecretColumns('customers');

            $secret_question = validated($_POST['secret_question'] ?? '');
            $secret_answer = validated($_POST['secret_answer'] ?? '');
            $secret_answer_hash = password_hash($secret_answer, PASSWORD_BCRYPT);

            $insertStmt = $conn->prepare("INSERT INTO customers (name, email, password, phone, address, secret_question, secret_answer_hash) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->bind_param("sssssss", $name, $email, $hashedPassword, $phone, $address, $secret_question, $secret_answer_hash);
            
            if($insertStmt->execute()) {
                redirect("customer-login.php", "Account created successfully! Please login.");
            } else {
                redirect("register.php", "Failed to create account!", "error");
            }
        }
    } else {
        redirect("register.php", "All fields are required!", "error");
    }
}
?>