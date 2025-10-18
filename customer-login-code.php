<?php
include('config/function.php');

if(isset($_POST['customerLogin'])) {
    $email = validated($_POST['email']);
    $password = validated($_POST['password']);
    
    if($email != '' && $password != '') {
        $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ? AND status = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if(mysqli_num_rows($result) == 1) {
            $customer = mysqli_fetch_assoc($result);
            
            if(password_verify($password, $customer['password'])) {
                $_SESSION['customerLoggedIn'] = true;
                $_SESSION['customerUser'] = [
                    'id' => $customer['id'],
                    'name' => $customer['name'],
                    'email' => $customer['email'],
                    'phone' => $customer['phone'],
                    'address' => $customer['address']
                ];
                redirect("customer/dashboard.php", "Welcome back, " . $customer['name'] . "!");
            } else {
                redirect("customer-login.php", "Invalid password!", "error");
            }
        } else {
            redirect("customer-login.php", "Invalid email or account disabled!", "error");
        }
    } else {
        redirect("customer-login.php", "All fields are required!", "error");
    }
}
?>