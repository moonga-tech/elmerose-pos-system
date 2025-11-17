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
                    <h6>Subtotal: ₱ <span id="cartSubtotal">0.00</span></h6>
                    <h6>Delivery fee: ₱ <span id="deliveryFee">0.00</span></h6>
                    <h4>Total: ₱ <span id="cartTotal">0.00</span></h4>

                    <div class="mt-3 text-start mb-2">
                        <label class="form-label fw-semibold">Delivery option</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="delivery_option" id="do_pickup" value="pickup" onchange="renderCart()">
                                <label class="form-check-label" for="do_pickup">Pick Up (no fee)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="delivery_option" id="do_cod" value="cod" checked onchange="renderCart()">
                                <label class="form-check-label" for="do_cod">Cash on Delivery (delivery fee applies)</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 text-start mb-2">
                        <label class="form-label fw-semibold">Payment method</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="payment_method" id="pm_cod" value="cod" checked>
                                <label class="form-check-label" for="pm_cod">Cash on Delivery</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="payment_method" id="pm_online" value="online">
                                <label class="form-check-label text-danger" for="pm_online">Online Payment Coming Soon!!</label>
                            </div>
                        </div>
                    </div>

                    <div id="deliveryAddressBlock" class="mt-3 text-start mb-2" style="display:none;">
                        <label class="form-label fw-semibold">Delivery address</label>
                        <div>
                            <textarea id="delivery_address" class="form-control" rows="2" placeholder="Enter delivery address (required for Cash on Delivery)"></textarea>
                            <div class="form-text">Provide the full address where you'd like your order delivered.</div>
                        </div>
                    </div>

                    <button class="btn btn-primary" onclick="placeOrder(this)"><i class="fas fa-check-circle me-2"></i>Place Order</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    // Delivery fee (COD) from server-side config
    const COD_FEE = <?= json_encode((float)get_delivery_fee()); ?>;

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
            // update subtotal and total display
            document.getElementById('cartSubtotal').textContent = total.toFixed(2);
            // compute delivery fee based on selected option
            const deliveryOption = document.querySelector('input[name="delivery_option"]:checked') ? document.querySelector('input[name="delivery_option"]:checked').value : 'cod';
                const deliveryFee = (deliveryOption === 'cod') ? COD_FEE : 0.00;
            document.getElementById('deliveryFee').textContent = deliveryFee.toFixed(2);
            document.getElementById('cartTotal').textContent = (total + deliveryFee).toFixed(2);
            // show/hide delivery address block depending on selected delivery option
            const addrBlock = document.getElementById('deliveryAddressBlock');
            if (addrBlock) {
                if (deliveryOption === 'cod') {
                    addrBlock.style.display = 'block';
                } else {
                    addrBlock.style.display = 'none';
                }
            }
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

        function placeOrder(btn) {
            if(cart.length === 0) {
                alert("Your cart is empty!");
                return;
            }
            const deliveryOption = document.querySelector('input[name="delivery_option"]:checked') ? document.querySelector('input[name="delivery_option"]:checked').value : 'cod';
            const pmInput = document.querySelector('input[name="payment_method"]:checked');
            const paymentMethod = pmInput ? pmInput.value : 'cod';

            // show a simple loading indicator
            if (btn) { btn.disabled = true; }

            // If Cash on Delivery is selected, ensure address is provided
            if (deliveryOption === 'cod') {
                const addrEl = document.getElementById('delivery_address');
                if (!addrEl || addrEl.value.trim() === '') {
                    if (btn) { btn.disabled = false; }
                    alert('Please enter a delivery address for Cash on Delivery.');
                    return;
                }
            }

            fetch('place-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                    body: JSON.stringify({
                        cart: cart,
                        payment_method: paymentMethod,
                        delivery_option: deliveryOption,
                        delivery_address: (document.getElementById('delivery_address') ? document.getElementById('delivery_address').value.trim() : '')
                    })
            })
            .then(response => response.json())
            .then(data => {
                if(btn) { btn.disabled = false; }
                if(data && data.success) {
                    localStorage.removeItem('cart');
                    window.location.href = 'order-success.php?order_id=' + data.order_id;
                } else {
                    alert((data && data.message) ? data.message : 'Something went wrong placing your order.');
                }
            })
            .catch(error => {
                if(btn) { btn.disabled = false; }
                console.error('Error:', error);
                alert('An error occurred while placing the order.');
            });
        }

        renderCart();
    </script>
</body>
</html>
