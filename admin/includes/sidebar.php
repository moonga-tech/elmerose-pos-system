<style>
.enhanced-sidebar {
    background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%) !important;
    box-shadow: 2px 0 15px rgba(0,0,0,0.1);
}
.sb-sidenav-menu-heading {
    background: rgba(255,255,255,0.1);
    color: #ecf0f1 !important;
    font-weight: 600;
    padding: 12px 20px;
    margin: 10px 0;
    border-radius: 8px;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.nav-link {
    color: #bdc3c7 !important;
    padding: 12px 20px;
    border-radius: 8px;
    margin: 2px 10px;
    transition: all 0.3s ease;
}
.nav-link:hover {
    background: rgba(255,255,255,0.1) !important;
    color: white !important;
    transform: translateX(5px);
}
.nav-link.active {
    background: linear-gradient(45deg, #667eea, #764ba2) !important;
    color: white !important;
}
.sb-nav-link-icon {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}
.sb-sidenav-menu-nested .nav-link {
    padding-left: 50px;
    font-size: 0.9rem;
}
.sb-sidenav-footer {
    background: rgba(0,0,0,0.2);
    border-top: 1px solid rgba(255,255,255,0.1);
    padding: 15px;
    color: #bdc3c7;
}
</style>
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion enhanced-sidebar" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading"><i class="fas fa-home me-2"></i>Core</div>
                <a class="nav-link" href="index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>

                <div class="sb-sidenav-menu-heading"><i class="fas fa-cogs me-2"></i>Management</div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCategory" aria-expanded="false" aria-controls="collapseCategory">
                    <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                    Categories
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCategory" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="category-create.php"><i class="fas fa-plus me-2"></i>Create Category</a>
                        <a class="nav-link" href="categories.php"><i class="fas fa-list me-2"></i>View Categories</a>
                    </nav>
                </div>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProduct" aria-expanded="false" aria-controls="collapseProduct">
                    <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                    Products
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseProduct" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="products-create.php"><i class="fas fa-plus me-2"></i>Create Product</a>
                        <a class="nav-link" href="products.php"><i class="fas fa-list me-2"></i>View Products</a>
                        <a class="nav-link" href="stock-alerts.php"><i class="fas fa-exclamation-triangle me-2"></i>Stock Alerts</a>
                        <a class="nav-link" href="expiry-alerts.php"><i class="fas fa-calendar-times me-2"></i>Expiry Alerts</a>
                    </nav>
                </div>
                
                <div class="sb-sidenav-menu-heading"><i class="fas fa-shopping-cart me-2"></i>Sales</div>
                <a class="nav-link" href="orders.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                    Orders
                </a>

                <div class="sb-sidenav-menu-heading"><i class="fas fa-users me-2"></i>User Management</div>
                <a class="nav-link" href="customers.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                    Customers
                </a>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseAdmins" aria-expanded="false" aria-controls="collapseAdmins">
                    <div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div>
                    Admins/Staff
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseAdmins" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="admin-create.php"><i class="fas fa-user-plus me-2"></i>Add Admin</a>
                        <a class="nav-link" href="admins.php"><i class="fas fa-users me-2"></i>View Admins</a>
                    </nav>
                </div>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <strong><?= $_SESSION['loggedInUser']['name'] ?? 'Admin' ?></strong>
        </div>
    </nav>
</div>