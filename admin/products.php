<?php include ('includes/header.php'); ?>

<div class="container-fluid px-4">
    <div class="card enhanced-table-card">
        <div class="card-header enhanced-table-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-box me-2"></i>
                    <h4 class="mb-0 d-inline">Products Management</h4>
                </div>
                <div class="d-flex align-items-center">
                    <?php $showArchived = isset($_GET['archived']) && $_GET['archived'] === '1'; ?>
                    <form class="me-3" method="GET" action="products.php">
                        <div class="input-group">
                            <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control form-control-sm" placeholder="Search id, name, category or SKU">
                            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                    <?php if($showArchived): ?>
                        <a href="products.php" class="btn btn-outline-secondary btn-sm me-2">Show Active</a>
                    <?php else: ?>
                        <a href="products.php?archived=1" class="btn btn-outline-secondary btn-sm me-2">Show Archived</a>
                    <?php endif; ?>
                    <a href="products-create.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-plus me-1"></i>Add Product
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <?php alertMessage(); ?>

            <div class="table-responsive">
                <table class="table enhanced-table mb-0">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-2"></i>ID</th>
                            <th><i class="fas fa-image me-2"></i>Image</th>
                            <th><i class="fas fa-cube me-2"></i>Product Info</th>
                            <th><i class="fas fa-boxes me-2"></i>Quantity</th>
                            <th><i class="fas fa-peso-sign me-2"></i>Price</th>
                            <th><i class="fas fa-eye me-2"></i>Status</th>
                            <th><i class="fas fa-cogs me-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            // Build prepared statement for products with optional search
                            $sql = "SELECT p.*, c.name as category_name, pc.name as color_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN paint_colors pc ON p.color_id = pc.id";
                            $params = [];
                            $types = '';

                            if (isset($_GET['q']) && trim($_GET['q']) !== '') {
                                $q = trim($_GET['q']);
                                if (ctype_digit($q)) {
                                    $sql .= " WHERE p.id = ?";
                                    $params[] = (int)$q;
                                    $types .= 'i';
                                } else {
                                    $sql .= " WHERE p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?";
                                    $like = '%' . $q . '%';
                                    $params[] = $like; $params[] = $like; $params[] = $like;
                                    $types .= 'sss';
                                }
                            }

                            // Filter archived vs active items
                            if ($showArchived) {
                                if (stripos($sql, 'WHERE') !== false) {
                                    $sql .= " AND p.status = 2";
                                } else {
                                    $sql .= " WHERE p.status = 2";
                                }
                            } else {
                                // By default hide archived items
                                if (stripos($sql, 'WHERE') !== false) {
                                    $sql .= " AND p.status != 2";
                                } else {
                                    $sql .= " WHERE p.status != 2";
                                }
                            }

                            $sql .= " ORDER BY p.id DESC";
                            $stmt = $conn->prepare($sql);
                            $productsRes = [];
                            if ($stmt) {
                                if ($types !== '') {
                                    // bind params dynamically
                                    $bind_names = [];
                                    $bind_names[] = $types;
                                    for ($i = 0; $i < count($params); $i++) {
                                        $bind_names[] = &$params[$i];
                                    }
                                    call_user_func_array([$stmt, 'bind_param'], $bind_names);
                                }
                                $stmt->execute();
                                // Try to get result (requires mysqlnd). If not available, fallback to bind_result
                                $res = false;
                                if (method_exists($stmt, 'get_result')) {
                                    $res = $stmt->get_result();
                                }

                                if ($res !== false && $res !== null) {
                                    while ($row = $res->fetch_assoc()) {
                                        $productsRes[] = $row;
                                    }
                                } else {
                                    // Fallback: use bind_result
                                    $meta = $stmt->result_metadata();
                                    if ($meta) {
                                        $fields = [];
                                        $row = [];
                                        while ($field = $meta->fetch_field()) {
                                            $fields[] = &$row[$field->name];
                                        }
                                        call_user_func_array([$stmt, 'bind_result'], $fields);
                                        while ($stmt->fetch()) {
                                            $copy = [];
                                            foreach($row as $k => $v) $copy[$k] = $v;
                                            $productsRes[] = $copy;
                                        }
                                    }
                                }
                            } else {
                                // Prepare failed (maybe unsupported column like sku). Fallback to safe mysqli query
                                if ($types === 'i' && isset($params[0])) {
                                    $fallbackSql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = " . (int)$params[0] . " ORDER BY p.id DESC";
                                } else {
                                    $like = '%' . validated($_GET['q'] ?? '') . '%';
                                    $fallbackSql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE (p.name LIKE '" . mysqli_real_escape_string($conn, $like) . "' OR p.description LIKE '" . mysqli_real_escape_string($conn, $like) . "' OR c.name LIKE '" . mysqli_real_escape_string($conn, $like) . "') ORDER BY p.id DESC";
                                }
                                $r = mysqli_query($conn, $fallbackSql);
                                if ($r) {
                                    while ($row = mysqli_fetch_assoc($r)) $productsRes[] = $row;
                                }
                            }

                            if(!empty($productsRes)){
                                foreach($productsRes as $item){
                                    ?>
                                        <tr>
                                            <td><strong>#<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                            
                                            <td>
                                                <?php if($item['image']): ?>
                                                    <img src="../<?= htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" class="product-image" alt="Product">
                                                <?php else: ?>
                                                    <div class="product-image d-flex align-items-center justify-content-center bg-light">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <div class="product-info">
                                                    <div class="product-name"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="product-details">
                                                        <?= htmlspecialchars(substr($item['description'], 0, 40), ENT_QUOTES, 'UTF-8'); ?>...
                                                        <?php if($item['color_name']): ?>
                                                            <br><small class="text-info">Color: <?= htmlspecialchars($item['color_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <span class="badge <?= $item['quantity'] > 10 ? 'bg-success' : ($item['quantity'] > 0 ? 'bg-warning' : 'bg-danger'); ?>">
                                                    <?= $item['quantity']; ?> units
                                                </span>
                                            </td>

                                            <td>
                                                <span class="price-tag">₱ <?= number_format($item['price'], 2); ?></span>
                                            </td>
                                            
                                            <td>
                                                <?php
                                                    if(isset($item['status']) && $item['status'] == 2){
                                                        echo "<span class='badge bg-dark status-badge'><i class='fas fa-archive me-1'></i>Archived</span>";
                                                    } else if(isset($item['status']) && $item['status'] == 0){
                                                        echo "<span class='badge bg-success status-badge'><i class='fas fa-eye me-1'></i>Visible</span>";
                                                    } else {
                                                        echo "<span class='badge bg-secondary status-badge'><i class='fas fa-eye-slash me-1'></i>Hidden</span>";
                                                    }
                                                ?>
                                            </td>

                                            <td>
                                                <a href="products-edit.php?id=<?= $item['id']; ?>" class="btn btn-primary action-btn btn-sm">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                                <?php if(isset($item['status']) && $item['status'] == 2): ?>
                                                    <a href="products-unarchive.php?id=<?= $item['id']; ?>" class="btn btn-success action-btn btn-sm" onclick="return confirm('Restore this product from archive?')">
                                                        <i class="fas fa-undo me-1"></i>Restore
                                                    </a>
                                                <?php else: ?>
                                                    <a href="products-archive.php?id=<?= $item['id']; ?>" class="btn btn-warning action-btn btn-sm" onclick="return confirm('Move this product to archive?')">
                                                        <i class="fas fa-archive me-1"></i>Move to Archive
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php 
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center p-5'><i class='fas fa-box-open fa-3x mb-3 d-block text-muted'></i><h5>No products found</h5><p class='text-muted'>Start by adding your first product</p></td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include ('includes/footer.php'); ?>