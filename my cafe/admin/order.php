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
    <title>Order #<?= h((string) $orderId) ?> | Admin</title>
    <link rel="stylesheet" href="<?= h(asset_url('css/admin.css')) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-mark"><i class="fa-solid fa-mug-hot"></i></div>
                <div>
                    <strong><?= h(APP_NAME) ?></strong>
                    <span>Admin Panel</span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="<?= h(site_url('admin/index.php')) ?>"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                <a href="<?= h(site_url('admin/manage.php?entity=orders')) ?>"><i class="fa-solid fa-cart-shopping"></i> Orders</a>
                <a href="<?= h(site_url('admin/invoice.php?id=' . (string) $orderId)) ?>" target="_blank"><i class="fa-solid fa-print"></i> Print Invoice</a>
            </nav>
        </aside>
        <main class="content">
            <header class="topbar-admin">
                <div>
                    <p class="eyebrow">Order Details</p>
                    <h1>Order #<?= h((string) $orderId) ?></h1>
                </div>
                <div class="action-row">
                    <a class="btn btn-light" href="<?= h(site_url('admin/manage.php?entity=orders')) ?>">Back</a>
                    <a class="btn btn-primary" href="<?= h(site_url('admin/invoice.php?id=' . (string) $orderId)) ?>" target="_blank">Print Invoice</a>
                </div>
            </header>

            <section class="panel">
                <div class="info-grid">
                    <article class="mini-card">
                        <h3>Customer</h3>
                        <p><?= h($order['customer_name']) ?></p>
                        <p><?= h($order['phone']) ?></p>
                        <p><?= h((string) ($order['email'] ?? '')) ?></p>
                    </article>
                    <article class="mini-card">
                        <h3>Delivery</h3>
                        <p><?= h((string) ($order['address'] ?? '')) ?></p>
                        <p><?= h((string) ($order['notes'] ?? '')) ?></p>
                    </article>
                    <article class="mini-card">
                        <h3>Summary</h3>
                        <p>Status: <?= h($order['status']) ?></p>
                        <p>Items: <?= h((string) $order['item_count']) ?></p>
                        <p>Total: <?= money((float) $order['total_amount']) ?></p>
                    </article>
                </div>
            </section>

            <section class="panel">
                <h2>Items</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
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
            </section>
        </main>
    </div>
</body>
</html>
