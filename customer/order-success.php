<?php 
include('../config/function.php');

if(!isset($_SESSION['customerLoggedIn'])) {
    redirect("../customer-login.php", "Please login first!", "error");
    exit();
}

$customer = $_SESSION['customerUser'];
$order_id = isset($_GET['order_id']) ? validated($_GET['order_id']) : 'N/A';
$orderData = null;
if ($order_id !== 'N/A') {
    $g = getById('orders', $order_id);
    if ($g['status'] === 200) {
        $orderData = $g['data'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - Elmerose POS</title>
    <link href="../assets/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .success-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: white;
            text-align: center;
            padding: 40px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-store me-2"></i>Elmerose Store
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2"></i>My Orders</a></li>
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../customer-logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
                <a class="nav-link position-relative" href="cart.php">
                    <i class="fas fa-shopping-cart"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="success-card">
                    <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                    <h2 class="mb-3">Order Placed Successfully!</h2>
                    <p class="text-muted">Your Order ID is: <strong>#<?= htmlspecialchars($order_id, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    <?php if ($orderData && isset($orderData['payment_method'])): ?>
                        <p class="text-muted">Payment method: <strong><?= htmlspecialchars(strtoupper($orderData['payment_method']), ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    <?php endif; ?>
                    <?php if ($order_id !== 'N/A'): ?>
                        <div class="mt-3">
                            <label class="form-label">Rate your order:</label>
                            <div id="starRating" class="mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button class="btn btn-outline-secondary btn-sm rating-star" data-value="<?= $i ?>">☆</button>
                                <?php endfor; ?>
                            </div>
                            <div id="ratingMessage" class="text-success" style="display:none;">Thanks for your feedback!</div>
                        </div>
                    <?php endif; ?>
                    <p>Thank you for your purchase. You can view your order details in the "My Orders" section.</p>
                    <a href="dashboard.php" class="btn btn-primary mt-3"><i class="fas fa-shopping-bag me-2"></i>Continue Shopping</a>
                    <a href="orders.php" class="btn btn-outline-primary mt-3"><i class="fas fa-receipt me-2"></i>View My Orders</a>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function(){
            const orderId = <?= json_encode($order_id); ?>;
            const stars = document.querySelectorAll('.rating-star');
            const msg = document.getElementById('ratingMessage');
            function setStars(n){
                stars.forEach(s => {
                    s.textContent = (parseInt(s.dataset.value) <= n) ? '★' : '☆';
                    if (parseInt(s.dataset.value) <= n) {
                        s.classList.remove('btn-outline-secondary');
                        s.classList.add('btn-warning');
                    } else {
                        s.classList.remove('btn-warning');
                        s.classList.add('btn-outline-secondary');
                    }
                });
            }
            stars.forEach(s => s.addEventListener('click', function(e){
                const val = parseInt(this.dataset.value);
                // POST rating
                fetch('rate-order.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({order_id: orderId, rating: val})
                }).then(r=>r.json()).then(data=>{
                    if (data.success) {
                        setStars(val);
                        if (msg) { msg.style.display = 'block'; }
                    } else {
                        alert(data.message || 'Failed to save rating');
                    }
                }).catch(err => { console.error(err); alert('Error saving rating'); });
            }));
        })();
    </script>
</body>
</html>
