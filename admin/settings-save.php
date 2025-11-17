<?php
include('includes/header.php');
include('../config/function.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('settings.php', 'Invalid request', 'error');
    exit();
}

$fee = isset($_POST['cod_delivery_fee']) ? validated($_POST['cod_delivery_fee']) : '';
if ($fee === '' || !is_numeric($fee)) {
    redirect('settings.php', 'Please enter a valid numeric fee', 'error');
    exit();
}

$ok = set_setting('cod_delivery_fee', number_format((float)$fee, 2, '.', ''));
if ($ok) {
    redirect('settings.php', 'Settings saved', 'success');
} else {
    redirect('settings.php', 'Failed to save settings', 'error');
}

?>
