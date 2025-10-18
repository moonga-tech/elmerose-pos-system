<?php include ('includes/header.php'); ?>

<div class="container-fluid px-4">
	<div class="card">
		<div class="card-header">
			<h4 class="mb-0">Edit Order</h4>
		</div>
		<div class="card-body">
			<?php alertMessage(); ?>

			<?php
			if (!isset($_GET['id']) || trim($_GET['id']) === '') {
				echo '<h5>No order ID provided</h5>';
			} else {
				$orderId = validated($_GET['id']);
				$orderRes = mysqli_query($conn, "SELECT o.*, c.name as customer_name, c.email as customer_email FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.id='$orderId' LIMIT 1");
				if ($orderRes && mysqli_num_rows($orderRes) == 1) {
					$order = mysqli_fetch_assoc($orderRes);
					?>
					<form action="code.php" method="POST">
						<input type="hidden" name="orderId" value="<?= $order['id']; ?>">

						<div class="row">
							<div class="col-md-6 mb-3">
								<label>Order ID</label>
								<input type="text" class="form-control" value="#<?= htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
							</div>
							<div class="col-md-6 mb-3">
								<label>Customer</label>
								<input type="text" class="form-control" value="<?= htmlspecialchars($order['customer_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?> (<?= htmlspecialchars($order['customer_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>)" disabled>
							</div>
							<div class="col-md-6 mb-3">
								<label>Total Amount</label>
								<input type="text" name="total_amount" class="form-control" value="<?= htmlspecialchars($order['total_amount'], ENT_QUOTES, 'UTF-8'); ?>">
							</div>
							<div class="col-md-6 mb-3">
								<label>Status</label>
								<select name="status" class="form-select">
									<option value="pending" <?= $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
									<option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
									<option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
									<option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
								</select>
							</div>
							<div class="col-md-12 mb-3">
								<button type="submit" name="updateOrder" class="btn btn-primary">Update Order</button>
								<a href="orders.php" class="btn btn-secondary">Cancel</a>
							</div>
						</div>
					</form>
					<?php
				} else {
					echo '<h5>Order not found</h5>';
				}
			}
            
			// Show order items if available
			if (!empty($order) && isset($order['id'])) {
				$itemsRes = mysqli_query($conn, "SELECT oi.*, p.name as product_name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id='".validated($order['id'])."'");
				echo '<hr><h5>Order Items</h5>';
				if ($itemsRes && mysqli_num_rows($itemsRes) > 0) {
					echo '<ul class="list-group">';
					while ($it = mysqli_fetch_assoc($itemsRes)) {
						echo '<li class="list-group-item d-flex justify-content-between align-items-center">' . htmlspecialchars($it['product_name'] ?? 'Product', ENT_QUOTES, 'UTF-8') . '<span class="badge bg-secondary">Qty: ' . (int)$it['quantity'] . '</span><span class="ms-3">$' . number_format($it['price'],2) . '</span></li>';
					}
					echo '</ul>';
				} else {
					echo '<p class="text-muted">No items found for this order.</p>';
				}
			}
			?>
		</div>
	</div>
</div>

<?php include ('includes/footer.php'); ?>
