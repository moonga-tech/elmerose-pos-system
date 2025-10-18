<?php
include('../config/function.php');

if(!isset($_SESSION['customerLoggedIn'])) {
    redirect("../customer-login.php", "Please login first!", "error");
    exit();
}

if(isset($_POST['updateProfile'])) {
    $customerId = $_SESSION['customerUser']['id'];
    $name = validated($_POST['name']);
    $email = validated($_POST['email']);
    $phone = validated($_POST['phone']);
    $address = validated($_POST['address']);
    $password = validated($_POST['password']);
    $confirm_password = validated($_POST['confirm_password']);

    if($name == '' || $email == '') {
        redirect('profile.php', 'Name and Email are required!', 'error');
        exit();
    }

    $query = "UPDATE customers SET name='$name', email='$email', phone='$phone', address='$address'";

    if($password != '') {
        if($password != $confirm_password) {
            redirect('profile.php', 'Passwords do not match!', 'error');
            exit();
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query .= ", password='$hashedPassword'";
    }

    $query .= " WHERE id='$customerId'";

    $result = mysqli_query($conn, $query);

    if($result) {
        // Update session data
        $_SESSION['customerUser']['name'] = $name;
        $_SESSION['customerUser']['email'] = $email;
        $_SESSION['customerUser']['phone'] = $phone;
        $_SESSION['customerUser']['address'] = $address;

        redirect('profile.php', 'Profile updated successfully!', 'success');
    } else {
        redirect('profile.php', 'Something went wrong!', 'error');
    }
}
?>