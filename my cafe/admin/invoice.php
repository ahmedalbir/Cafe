<?php
require_once __DIR__ . '/../includes/admin_config.php';
require_admin();

$orderId = (int) ($_GET['id'] ?? 0);
$order = fetch_one('SELECT o.*, COALESCE(i.item_count, 0) AS item_count FROM orders o LEFT JOIN (SELECT order_id, COUNT(*) AS item_count FROM order_items GROUP BY order_id) i ON i.order_id = o.id WHERE o.id = ?', [$orderId]);
if (!$order) {
    redirect_to(site_url('admin/manage.php?entity=orders'));
}

$items = fetch_all('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC', [$orderId]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= h((string) $orderId) ?></title>
    <link rel="stylesheet" href="<?= h(asset_url('css/admin.css')) ?>">
    <style>
        body { background: #fff; }
        .print-wrap { max-width: 900px; margin: 24px auto; padding: 24px; }
        @media print { .no-print { display: none !important; } body { background: #fff; } .print-wrap { margin: 0; } }
    </style>
</head>
<body>
    <div class="print-wrap panel">
        <div class="no-print action-row" style="justify-content:flex-end; margin-bottom:16px;">
            <button class="btn btn-primary" onclick="window.print()">Print</button>
        </div>
        <h1><?= h(setting('site_name', APP_NAME)) ?></h1>
        <p>Invoice #<?= h((string) $orderId) ?></p>
        <p>Status: <?= h($order['status']) ?></p>
        <p>Customer: <?= h($order['customer_name']) ?></p>
        <p>Phone: <?= h($order['phone']) ?></p>
        <p>Date: <?= h((string) $order['created_at']) ?></p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= h($item['product_name']) ?></td>
                            <td><?= h((string) $item['quantity']) ?></td>
                            <td><?= money((float) $item['unit_price']) ?></td>
                            <td><?= money((float) $item['unit_price'] * (int) $item['quantity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2>Total: <?= money((float) $order['total_amount']) ?></h2>
    </div>
</body>
</html>
