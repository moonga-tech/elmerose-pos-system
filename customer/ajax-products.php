<?php
require_once __DIR__ . '/../config/dbcon.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if($q !== '') {
    $q_esc = mysqli_real_escape_string($conn, $q);
    $sql = "SELECT * FROM products WHERE status = 0 AND (name LIKE '%$q_esc%' OR description LIKE '%$q_esc%') ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM products WHERE status = 0 ORDER BY id DESC";
}

$res = mysqli_query($conn, $sql);

if($res && mysqli_num_rows($res) > 0) {
    while($product = mysqli_fetch_assoc($res)) {
        $id = htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
        $desc = htmlspecialchars(substr($product['description'], 0, 80), ENT_QUOTES, 'UTF-8');
        $price = number_format($product['price'], 2);
        $rawPrice = (float)$product['price'];
        $qty = (int)$product['quantity'];
        $image = $product['image'] ? '../' . htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') : null;

        // Use json_encode to safely escape the product name for JS
        $jsName = json_encode($product['name']);

        echo "<div class=\"col-md-4 mb-4\">";
        echo "<div class=\"card product-card\" data-product-id=\"$id\">";
        if($image) {
            echo "<img src=\"$image\" class=\"card-img-top product-image\" alt=\"Product\">";
        } else {
            echo "<div class=\"product-image bg-light d-flex align-items-center justify-content-center\"><i class=\"fas fa-image fa-3x text-muted\"></i></div>";
        }
        echo "<div class=\"card-body\">";
        echo "<h5 class=\"card-title\">$name</h5>";
        echo "<p class=\"card-text text-muted\">$desc...</p>";
        echo "<div class=\"d-flex justify-content-between align-items-center\"><h4 class=\"text-primary mb-0\">₱$price</h4><span class=\"badge " . ($qty == 0 ? 'bg-danger' : ($qty <= 5 ? 'bg-warning' : 'bg-success')) . "\">Stock: $qty</span></div>";

        // Use data attributes and a class for the add-to-cart button so we can bind events from JS
        $safeNameAttr = htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
        echo "<div class=\"mt-3\"><div class=\"input-group mb-2\">";
            $disabledAttr = $qty == 0 ? 'disabled' : '';
            echo "<input type=\"number\" class=\"form-control\" id=\"qty-$id\" value=\"1\" min=\"1\" max=\"$qty\">";
            echo "<button class=\"btn btn-order text-white add-to-cart-btn\" data-id=\"$id\" data-name=\"$safeNameAttr\" data-price=\"$rawPrice\" data-qty=\"$qty\" $disabledAttr>";
        echo "<i class=\"fas fa-cart-plus me-2\"></i>" . ($qty == 0 ? 'Out of Stock' : 'Add to Cart') . "</button></div>";
        if($qty <= 5 && $qty > 0) {
            echo "<small class=\"text-warning\"><i class=\"fas fa-exclamation-triangle me-1\"></i>Only $qty left!</small>";
        }
        echo "</div></div></div></div>";
    }
} else {
    echo '<div class="col-12 text-center"><h4 class="text-muted">No products available</h4></div>';
}

exit;
