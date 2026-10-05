<?php
require_once __DIR__ . '/../includes/admin_config.php';
require_admin();

$admin = current_admin();

$stats = [
    'Products' => (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'Reservations' => (int) db()->query('SELECT COUNT(*) FROM reservations')->fetchColumn(),
    'Messages' => (int) db()->query('SELECT COUNT(*) FROM contacts')->fetchColumn(),
    'Orders' => (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'Offers' => (int) db()->query('SELECT COUNT(*) FROM offers')->fetchColumn(),
];

$recentReservations = fetch_all('SELECT * FROM reservations ORDER BY id DESC LIMIT 5');
$recentMessages = fetch_all('SELECT * FROM contacts ORDER BY id DESC LIMIT 5');
$recentOrders = fetch_all('SELECT * FROM orders ORDER BY id DESC LIMIT 5');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?= h(APP_NAME) ?></title>
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
                <a class="active" href="<?= h(site_url('admin/index.php')) ?>"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                <a href="<?= h(site_url('admin/manage.php?entity=products')) ?>"><i class="fa-solid fa-mug-hot"></i> Products</a>
                <a href="<?= h(site_url('admin/manage.php?entity=categories')) ?>"><i class="fa-solid fa-book-open"></i> Categories</a>
                <a href="<?= h(site_url('admin/manage.php?entity=offers')) ?>"><i class="fa-solid fa-gift"></i> Offers</a>
                <a href="<?= h(site_url('admin/manage.php?entity=reservations')) ?>"><i class="fa-solid fa-calendar-check"></i> Reservations</a>
                <a href="<?= h(site_url('admin/manage.php?entity=orders')) ?>"><i class="fa-solid fa-cart-shopping"></i> Orders</a>
                <a href="<?= h(site_url('admin/manage.php?entity=testimonials')) ?>"><i class="fa-solid fa-star"></i> Testimonials</a>
                <a href="<?= h(site_url('admin/manage.php?entity=gallery_items')) ?>"><i class="fa-solid fa-camera"></i> Gallery</a>
                <a href="<?= h(site_url('admin/manage.php?entity=blog_posts')) ?>"><i class="fa-solid fa-newspaper"></i> Blog</a>
                <a href="<?= h(site_url('admin/manage.php?entity=careers')) ?>"><i class="fa-solid fa-briefcase"></i> Careers</a>
                <a href="<?= h(site_url('admin/manage.php?entity=applications')) ?>"><i class="fa-solid fa-file-lines"></i> Applications</a>
                <a href="<?= h(site_url('admin/manage.php?entity=contacts')) ?>"><i class="fa-solid fa-envelope"></i> Messages</a>
                <a href="<?= h(site_url('admin/manage.php?entity=settings')) ?>"><i class="fa-solid fa-gear"></i> Settings</a>
                <a href="<?= h(site_url('admin/logout.php')) ?>"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </nav>
        </aside>
        <main class="content">
            <header class="topbar-admin">
                <div>
                    <p class="eyebrow">Welcome back</p>
                    <h1><?= h($admin['name'] ?? 'Admin') ?></h1>
                </div>
                <a class="btn btn-light" href="<?= h(site_url('index.php')) ?>" target="_blank">View Public Site</a>
            </header>

            <?php if ($message = get_flash('success')): ?>
                <div class="alert alert-success"><?= h($message) ?></div>
            <?php endif; ?>

            <section class="stats-grid">
                <?php foreach ($stats as $label => $value): ?>
                    <article class="stat-card">
                        <span><?= h($label) ?></span>
                        <strong><?= number_format($value) ?></strong>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="panel-grid">
                <article class="panel">
                    <h2>Recent Reservations</h2>
                    <?php foreach ($recentReservations as $row): ?>
                        <div class="mini-row">
                            <strong><?= h($row['name']) ?></strong>
                            <span><?= h($row['reservation_date']) ?> <?= h($row['reservation_time']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </article>

                <article class="panel">
                    <h2>Recent Messages</h2>
                    <?php foreach ($recentMessages as $row): ?>
                        <div class="mini-row">
                            <strong><?= h($row['subject']) ?></strong>
                            <span><?= h($row['name']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </article>

                <article class="panel">
                    <h2>Recent Orders</h2>
                    <?php foreach ($recentOrders as $row): ?>
                        <div class="mini-row">
                            <strong><?= h($row['customer_name']) ?></strong>
                            <span><?= money((float) $row['total_amount']) ?> | <?= h($row['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </article>
            </section>
        </main>
    </div>
</body>
</html>
