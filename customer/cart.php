<?php 
include('../config/function.php');

if(!isset($_SESSION['customerLoggedIn'])) {
    redirect("../customer-login.php", "Please login first!", "error");
    exit();
}

$customer = $_SESSION['customerUser'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Elmerose POS</title>
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
        .cart-card {
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
        <div class="card cart-card">
            <div class="card-header navbar-custom text-white">
                <h4 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>My Cart</h4>
            </div>
            <div class="card-body p-4">
                <?php alertMessage(); ?>
                <div id="cartItems"></div>
                <div class="text-end mt-3">
                    <h4>Total: ₱<span id="cartTotal">0.00</span></h4>
                    <button class="btn btn-primary" onclick="placeOrder()"><i class="fas fa-check-circle me-2"></i>Place Order</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        function renderCart() {
            const cartItemsContainer = document.getElementById('cartItems');
            let total = 0;
            cartItemsContainer.innerHTML = '';

            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '<p class="text-muted text-center">Your cart is empty.</p>';
                document.getElementById('cartTotal').textContent = '0.00';
                return;
            }

            const table = document.createElement('table');
            table.className = 'table';
            table.innerHTML = `
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;

            const tbody = table.querySelector('tbody');
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.name}</td>
                    <td>₱${item.price.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="updateQuantity(${index}, -1)">-</button>
                        <span class="mx-2">${item.quantity}</span>
                        <button class="btn btn-sm btn-secondary" onclick="updateQuantity(${index}, 1)">+</button>
                    </td>
                    <td>₱${itemTotal.toFixed(2)}</td>
                    <td><button class="btn btn-sm btn-danger" onclick="removeFromCart(${index})"><i class="fas fa-trash"></i></button></td>
                `;
                tbody.appendChild(row);
            });

            cartItemsContainer.appendChild(table);
            document.getElementById('cartTotal').textContent = total.toFixed(2);
        }

        async function updateQuantity(index, change) {
            const item = cart[index];
            if (!item) return;

            // Increase quantity: try to reserve stock on server first
            if (change > 0) {
                try {
                    const resp = await fetch('add-to-cart.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ product_id: item.id, quantity: 1 })
                    });
                    const data = await resp.json();
                    if (data.success) {
                        item.quantity += 1;
                        localStorage.setItem('cart', JSON.stringify(cart));
                        renderCart();
                        if (data.alertMessage) {
                            const alert = document.createElement('div');
                            alert.className = 'alert alert-warning';
                            alert.textContent = data.alertMessage;
                            document.querySelector('.card-body').prepend(alert);
                            setTimeout(() => alert.remove(), 5000);
                        }
                    } else {
                        alert(data.message || 'Unable to add more of this item due to stock limits.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Error contacting server. Please try again.');
                }
                return;
            }

            // Decrease quantity: restore stock on server
            if (change < 0) {
                const newQty = item.quantity + change;
                // If still positive after decrement, restore only one unit
                if (newQty > 0) {
                    try {
                        const resp = await fetch('restore-stock.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({ product_id: item.id, quantity: 1 })
                        });
                        const data = await resp.json();
                        if (data.success) {
                            item.quantity = newQty;
                            localStorage.setItem('cart', JSON.stringify(cart));
                            renderCart();
                        } else {
                            alert(data.message || 'Unable to update quantity on server.');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Error contacting server. Please try again.');
                    }
                } else {
                    // Removing the item completely: restore all units to stock
                    try {
                        const resp = await fetch('restore-stock.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({ product_id: item.id, quantity: item.quantity })
                        });
                        const data = await resp.json();
                        if (data.success) {
                            cart.splice(index, 1);
                            localStorage.setItem('cart', JSON.stringify(cart));
                            renderCart();
                        } else {
                            alert(data.message || 'Unable to remove item from cart on server.');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Error contacting server. Please try again.');
                    }
                }
            }
        }

        async function removeFromCart(index) {
            const item = cart[index];
            if (!item) return;

            try {
                const resp = await fetch('restore-stock.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ product_id: item.id, quantity: item.quantity })
                });
                const data = await resp.json();
                if (data.success) {
                    cart.splice(index, 1);
                    localStorage.setItem('cart', JSON.stringify(cart));
                    renderCart();
                } else {
                    alert(data.message || 'Unable to remove item from cart on server.');
                }
            } catch (err) {
                console.error(err);
                alert('Error contacting server. Please try again.');
            }
        }

        function placeOrder() {
            if(cart.length === 0) {
                alert("Your cart is empty!");
                return;
            }

            fetch('place-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({cart: cart})
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    localStorage.removeItem('cart');
                    window.location.href = 'order-success.php?order_id=' + data.order_id;
                } else {
                    alert(data.message || 'Something went wrong!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while placing the order.');
            });
        }

        renderCart();
    </script>
</body>
</html>
