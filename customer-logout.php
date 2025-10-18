<?php
include('config/function.php');

unset($_SESSION['customerLoggedIn']);
unset($_SESSION['customerUser']);

redirect("customer-login.php", "Logged out successfully!");
?>