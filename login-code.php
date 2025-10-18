<?php
require './config/function.php';

if (isset($_POST['loginBtn'])) {

    // Check if client is currently locked out
    if (isset($_SESSION['login_lockout_until']) && time() < $_SESSION['login_lockout_until']) {
        $remaining = $_SESSION['login_lockout_until'] - time();
        redirect('login.php', "Too many failed attempts. Please wait {$remaining} seconds.", 'error');
    }


    $email = validated($_POST['email']);
    $password = validated($_POST['password']);

    if ($email != '' && $password != '') {

        $query = "SELECT * FROM admins WHERE email ='$email' LIMIT 1";

        $result = mysqli_query($conn, $query);

        if ($result) {
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                $hassPassword = $row['password'];
                $id = $row['id'];
                if (!password_verify($password, $hassPassword)) {
                    // increment attempt counter
                    if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
                    $_SESSION['login_attempts']++;
                    // lockout after 3 failed attempts
                    if ($_SESSION['login_attempts'] >= 3) {
                        $_SESSION['login_lockout_until'] = time() + 10; // 10 seconds
                        $_SESSION['login_attempts'] = 0; // reset counter after lock
                        redirect('login.php', 'Too many failed attempts. Please wait 10 seconds.', 'error');
                    }
                    redirect('login.php', 'Invalid Password');
                }

                if($row['is_ban'] == 1) {
                    redirect("login.php", "Your account has been banned. Please contact the administrator.");
                }

                $_SESSION['loggedIn'] = true;
                $_SESSION['loggedInUser'] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                ];

                // clear any failed login tracking
                unset($_SESSION['login_attempts']);
                unset($_SESSION['login_lockout_until']);

                redirect("admin/index.php", "Login Successful");

            } else {
                if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
                $_SESSION['login_attempts']++;
                if ($_SESSION['login_attempts'] >= 3) {
                    $_SESSION['login_lockout_until'] = time() + 10;
                    $_SESSION['login_attempts'] = 0;
                    redirect('login.php', 'Too many failed attempts. Please wait 10 seconds.', 'error');
                }
                redirect("login.php", "Invalid Password");
            }   
        } else {
            redirect("login.php", "Email not found");
        }
    } else {
        redirect("login.php", "All fields are required");
    }

}

?>
