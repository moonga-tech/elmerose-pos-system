<?php
include('../config/function.php');

if(!isset($_GET['order_id'])){
    redirect('orders.php', 'Order ID is required', 'error');
}

$order_id = validated($_GET['order_id']);

$order = getById('orders', $order_id);
if($order['status'] != 200){
    redirect('orders.php', 'Order not found', 'error');
}

$order_items = mysqli_query($conn, "SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = '$order_id'");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Order #<?= $order['data']['id'] ?></title>
    <link href="../assets/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .receipt-container { max-width: 800px; margin: 20px auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        @media print {
            body, html { margin: 0; padding: 0; }
            .no-print { display: none; }
            .receipt-container { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    <div class="receipt-container" id="receipt">
        <h2 class="text-center mb-4">Receipt</h2>
        <p><strong>Order ID:</strong> #<?= $order['data']['id'] ?></p>
        <p><strong>Date:</strong> <?= date('d/m/Y', strtotime($order['data']['created_at'])) ?></p>
        <?php if (!empty($order['data']['payment_method'])): ?>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars(strtoupper($order['data']['payment_method']), ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($order_items as $item): ?>
                <tr>
                    <td><?= $item['name'] ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>₱<?= number_format($item['price'], 2) ?></td>
                    <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total:</th>
                    <th>₱<?= number_format($order['data']['total_amount'], 2) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="text-center mt-3 no-print">
        <button class="btn btn-primary" onclick="window.print()">Print</button>
        <button class="btn btn-success" id="save-pdf">Save as PDF</button>
    </div>

    <script>
        window.jsPDF = window.jspdf.jsPDF;
        document.getElementById('save-pdf').addEventListener('click', function () {
            html2canvas(document.getElementById('receipt')).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF();
                const imgProps= pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                pdf.save('receipt-order-<?= $order['data']['id'] ?>.pdf');
            });
        });
    </script>
</body>
</html>
