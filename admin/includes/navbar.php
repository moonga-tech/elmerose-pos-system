<style>
.enhanced-navbar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    border: none;
}
.navbar-brand {
    font-weight: 600;
    font-size: 1.4rem;
    color: white !important;
}

/* .navbar-brand:hover {
    color: #f8f9fa !important;
    transform: scale(1.05);
    transition: all 0.3s ease;
} */
    
#sidebarToggle {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 8px;
    color: white;
    transition: all 0.3s ease;
}
#sidebarToggle:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
}
.dropdown-menu {
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border-radius: 10px;
}
.logo {
    font-size: .8em;
    font-weight: 700;
}
</style>
<nav class="sb-topnav navbar navbar-expand navbar-dark enhanced-navbar">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3 logo" href="index.php">
        <i class="fas fa-store me-2"></i>Elmerose Management System
    </a>
    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
        <i class="fas fa-bars"></i>
    </button>
    <div class="d-flex align-items-center ms-auto">
        <!-- Navbar-->
        <ul class="mr-auto navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle fa-lg"></i>
                    <span class="ms-2"><?= $_SESSION['loggedInUser']['name'] ?? 'Admin' ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider" /></li>
                    <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<?php $_SESSION['loggedInUser']['name']; ?>