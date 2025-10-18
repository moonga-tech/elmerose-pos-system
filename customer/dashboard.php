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
    <title>Customer Dashboard - Elmerose POS</title>
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
        .product-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            background: white;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .btn-order {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-order:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transform: translateY(-2px);
        }
        .cart-badge {
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
            position: absolute;
            top: -8px;
            right: -8px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
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
                    <span class="cart-badge" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php alertMessage(); ?>
        
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-shopping-bag me-2 text-primary"></i>Available Products</h2>
                <p class="text-muted">Browse and order from our collection</p>
            </div>
        </div>

        <!-- Search bar -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input id="product-search-input" type="search" class="form-control" placeholder="Search products by name or description...">
                    <button id="product-search-btn" class="btn btn-primary" type="button"><i class="fas fa-search me-1"></i>Search</button>
                </div>
            </div>
        </div>

    <!-- product card -->
    <div class="row" id="productsContainer">
            <?php 
            $products = getAll("products", "0");
            if(mysqli_num_rows($products) > 0) {
                foreach($products as $product) {
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card" data-product-id="<?= $product['id']; ?>">
                            <?php if($product['image']): ?>
                                <img src="../<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top product-image" alt="Product">
                            <?php else: ?>
                                <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars(substr($product['description'], 0, 80), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="text-primary mb-0">₱ <?= number_format($product['price'], 2); ?></h4>
                                    <span class="badge <?= $product['quantity'] == 0 ? 'bg-danger' : ($product['quantity'] <= 5 ? 'bg-warning' : 'bg-success'); ?>">Stock: <?= $product['quantity']; ?></span>
                                </div>
                                <div class="mt-3">
                                    <div class="input-group mb-2">
                                        <input type="number" class="form-control" id="qty-<?= $product['id']; ?>" value="1" min="1" max="<?= $product['quantity']; ?>">
                                        <button class="btn btn-order text-white" onclick="addToCart(<?= $product['id']; ?>, '<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>', <?= $product['price']; ?>, <?= $product['quantity']; ?>)" <?= $product['quantity'] == 0 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-cart-plus me-2"></i><?= $product['quantity'] == 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                                        </button>
                                    </div>
                                    <?php if($product['quantity'] <= 5 && $product['quantity'] > 0): ?>
                                        <small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Only <?= $product['quantity']; ?> left!</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12 text-center"><h4 class="text-muted">No products available</h4></div>';
            }
            ?>
        </div>
    </div>

    <!-- javascript -->

    <script src="../assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Product search: debounce helper
        function debounce(fn, delay) {
            let t;
            return function(...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), delay);
            }
        }

        const searchInput = document.getElementById('product-search-input');
        const searchBtn = document.getElementById('product-search-btn');
        const productsContainer = document.getElementById('productsContainer');

        function fetchProducts(q = '') {
            fetch('ajax-products.php?q=' + encodeURIComponent(q))
                .then(r => r.text())
                .then(html => {
                    productsContainer.innerHTML = html;
                })
                .catch(() => {
                    productsContainer.innerHTML = '<div class="col-12 text-center"><h4 class="text-muted">Failed to load products</h4></div>';
                });
        }

        const debouncedFetch = debounce(() => fetchProducts(searchInput.value.trim()), 300);

        searchInput.addEventListener('keyup', debouncedFetch);
        searchBtn.addEventListener('click', () => fetchProducts(searchInput.value.trim()));

        // Delegated handler for add-to-cart buttons (works for AJAX-loaded items)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest && e.target.closest('.add-to-cart-btn');
            if(!btn) return;
            const id = parseInt(btn.getAttribute('data-id'));
            const name = btn.getAttribute('data-name');
            const price = parseFloat(btn.getAttribute('data-price'));
            const available = parseInt(btn.getAttribute('data-qty'));
            // Before calling addToCart, update the qty input value if present
            const qtyInput = document.getElementById(`qty-${id}`);
            if(qtyInput) {
                const qtyVal = parseInt(qtyInput.value) || 1;
                // set the quantity input's value in case addToCart reads it
                qtyInput.value = qtyVal;
            }
            addToCart(id, name, price, available);
        });

        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        updateCartCount();

        function addToCart(id, name, price, currentStock) {
            const quantity = parseInt(document.getElementById(`qty-${id}`).value);
            
            if(quantity > currentStock) {
                showAlert('error', 'Not enough stock available!');
                return;
            }
            
            // Send AJAX request to update stock
            fetch('add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${id}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Update local cart
                    const existingItem = cart.find(item => item.id === id);
                    if(existingItem) {
                        existingItem.quantity += quantity;
                    } else {
                        cart.push({id, name, price, quantity});
                    }
                    localStorage.setItem('cart', JSON.stringify(cart));
                    updateCartCount();
                    
                    // Update UI
                    updateProductStock(id, data.newStock);
                    showAlert('success', data.message);
                    
                    // Show stock alert if any
                    if(data.alertMessage) {
                        setTimeout(() => showAlert('warning', data.alertMessage), 1000);
                    }
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                showAlert('error', 'Something went wrong!');
            });
        }

        function updateProductStock(productId, newStock) {
            const stockBadge = document.querySelector(`[data-product-id="${productId}"] .badge`);
            const button = document.querySelector(`[data-product-id="${productId}"] button`);
            const qtyInput = document.getElementById(`qty-${productId}`);
            const warningText = document.querySelector(`[data-product-id="${productId}"] small`);
            
            if(stockBadge) stockBadge.textContent = `Stock: ${newStock}`;
            if(qtyInput) qtyInput.max = newStock;
            
            if(newStock === 0) {
                if(button) {
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-times me-2"></i>Out of Stock';
                    button.className = 'btn btn-secondary text-white';
                }
                if(stockBadge) stockBadge.className = 'badge bg-danger';
            } else if(newStock <= 5) {
                if(stockBadge) stockBadge.className = 'badge bg-warning';
                if(warningText) {
                    warningText.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i>Only ${newStock} left!`;
                    warningText.className = 'text-warning';
                }
            }
        }

        function showAlert(type, message) {
            const colors = {
                success: '#28a745',
                error: '#dc3545',
                warning: '#ffc107'
            };
            const icons = {
                success: 'check-circle',
                error: 'exclamation-triangle',
                warning: 'exclamation-triangle'
            };
            
            const alert = document.createElement('div');
            alert.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                background: ${colors[type]};
                color: white;
                padding: 15px 20px;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                transform: translateX(400px);
                transition: all 0.5s ease;
                max-width: 400px;
                font-weight: 500;
            `;
            alert.innerHTML = `
                <i class="fas fa-${icons[type]} me-2"></i>
                ${message}
                <button onclick="this.parentElement.remove()" style="
                    background: none;
                    border: none;
                    color: white;
                    float: right;
                    font-size: 18px;
                    cursor: pointer;
                    margin-left: 10px;
                ">&times;</button>
            `;
            
            document.body.appendChild(alert);
            setTimeout(() => alert.style.transform = 'translateX(0)', 100);
            setTimeout(() => alert.remove(), 5000);
        }

        function updateCartCount() {
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cartCount').textContent = count;
        }
    </script>
</body>
</html>