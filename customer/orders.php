<?php 
include('../config/function.php');

if(!isset($_SESSION['customerLoggedIn'])) {
    redirect("../customer-login.php", "Please login first!", "error");
    exit();
}

$customer = $_SESSION['customerUser'];
$customerId = $customer['id'];

$orders = mysqli_query($conn, "SELECT * FROM orders WHERE customer_id = '$customerId' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Elmerose POS</title>
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
        .orders-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: white;
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

    <div class="container mt-4">
        <div class="card orders-card">
            <div class="card-header navbar-custom text-white">
                <h4 class="mb-0"><i class="fas fa-receipt me-2"></i>My Order History</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Received</th>
                                <th>Rating</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(mysqli_num_rows($orders) > 0) {
                                foreach($orders as $order) {
                                    ?>
                                    <tr>
                                        <td>#<?= htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <!-- <td> ?= $order['name'] ?></td> -->
                                        <td>₱<?= number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <?php
                                                $status = htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8');
                                                $badge_class = 'bg-secondary';
                                                if($status == 'pending') $badge_class = 'bg-warning';
                                                if($status == 'confirmed') $badge_class = 'bg-info';
                                                if($status == 'delivered') $badge_class = 'bg-success';
                                                if($status == 'cancelled') $badge_class = 'bg-danger';
                                                echo "<span class='badge {$badge_class}'>".ucfirst($status)."</span>";
                                            ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($order['is_received'])): ?>
                                                <span class="badge bg-success">Received</span>
                                            <?php else: ?>
                                                <?php if ($order['status'] === 'delivered'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="confirmReceived(<?= $order['id']; ?>, this)">Confirm Received</button>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($order['rating']) && is_numeric($order['rating'])): ?>
                                                <?php $r = (int)$order['rating'];
                                                for ($s=1;$s<=5;$s++) {
                                                    echo $s <= $r ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star text-muted"></i>';
                                                }
                                                ?>
                                            <?php else: ?>
                                                <?php if ($order['status'] === 'delivered'): ?>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="rateOrderPrompt(<?= $order['id']; ?>)">Rate</button>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d M, Y', strtotime($order['created_at'])); ?></td>
                                        <td><a href="order-details.php?id=<?= $order['id']; ?>" class="btn btn-sm btn-primary">View</a></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="5" class="text-center text-muted p-5">You have no orders yet.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    
        <!-- Rating modal -->
        <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ratingModalLabel">Rate your order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="text-muted">Please select a rating (1 = worst, 5 = best)</p>
                        <div id="ratingStars" class="d-flex justify-content-center gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary rating-star" data-value="1" onclick="setRatingStars(1)">☆</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rating-star" data-value="2" onclick="setRatingStars(2)">☆</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rating-star" data-value="3" onclick="setRatingStars(3)">☆</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rating-star" data-value="4" onclick="setRatingStars(4)">☆</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rating-star" data-value="5" onclick="setRatingStars(5)">☆</button>
                        </div>
                        <div class="text-center">
                            <button id="ratingSubmitBtn" type="button" class="btn btn-primary" onclick="submitRatingFromModal()">Submit Rating</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <script>
        async function confirmReceived(orderId, btn) {
            if (!confirm('Are you sure you have received this order?')) return;
            try {
                btn.disabled = true;
                const resp = await fetch('confirm-received.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({order_id: orderId})
                });
                const data = await resp.json();
                if (data.success) {
                    alert('Order marked as received. Thank you!');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to mark as received');
                    btn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                alert('Error contacting server');
                btn.disabled = false;
            }
        }

        // Rating modal implementation
        let ratingModalInstance = null;
        let currentRatingOrderId = null;
        let currentRatingValue = 0;

        function setRatingStars(n) {
            currentRatingValue = n;
            const stars = document.querySelectorAll('#ratingModal .rating-star');
            stars.forEach(s => {
                const val = parseInt(s.dataset.value, 10);
                s.classList.toggle('btn-warning', val <= n);
                s.classList.toggle('btn-outline-secondary', val > n);
                s.textContent = val <= n ? '★' : '☆';
            });
        }

        function rateOrderPrompt(orderId) {
            currentRatingOrderId = orderId;
            currentRatingValue = 0;
            setRatingStars(0);
            if (!ratingModalInstance) {
                const modalEl = document.getElementById('ratingModal');
                ratingModalInstance = new bootstrap.Modal(modalEl);
            }
            ratingModalInstance.show();
        }

        async function submitRatingFromModal() {
            if (!currentRatingOrderId || currentRatingValue < 1 || currentRatingValue > 5) {
                alert('Please select a rating between 1 and 5');
                return;
            }
            const btn = document.getElementById('ratingSubmitBtn');
            btn.disabled = true;
            try {
                const resp = await fetch('rate-order.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({order_id: currentRatingOrderId, rating: currentRatingValue})
                });
                const data = await resp.json();
                if (data.success) {
                    ratingModalInstance.hide();
                    // small success message then reload
                    alert(data.message || 'Thanks for rating!');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to save rating');
                    btn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                alert('Error contacting server');
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
