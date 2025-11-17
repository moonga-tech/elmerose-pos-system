<?php 

require "../config/function.php";
require "authentication.php";

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Elmerose POS - Admin Dashboard</title>

        <!-- styles -->
        <link href="assets/css/styles.css" rel="stylesheet" />
        <link rel="stylesheet" href="assets/css/buttons.css">

        <!-- centralized enhanced table styles for all admin pages -->
        <link href="assets/css/enhanced-table.css" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    </head>
<body class="sb-nav-fixed">

    <?php include 'includes/navbar.php'; ?>

    <div id="layoutSidenav">
        <?php include 'includes/sidebar.php'; ?>
            <div id="layoutSidenav_content">
                <main class="py-4">
                    
