<?php
require_once __DIR__ . '/../includes/admin_config.php';
require_admin();

$entity = (string) ($_GET['entity'] ?? 'products');
$config = entity_config($entity);
if (!$config) {
    redirect_to(site_url('admin/index.php'));
}

$pk = $config['pk'];
$title = $config['title'];
$table = $config['table'];
$readonly = $config['readonly'] ?? false;
$fields = $config['fields'] ?? [];
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['form_action'] ?? 'save');

    if ($action === 'delete') {
        $id = $_POST[$pk] ?? null;
        if ($id !== null) {
            execute("DELETE FROM {$table} WHERE {$pk} = ?", [$id]);
            flash('success', "{$title} item deleted.");
        }
        redirect_to(site_url("admin/manage.php?entity={$entity}"));
    }

    if ($entity === 'settings') {
        $key = trim((string) ($_POST['key'] ?? ''));
        $value = trim((string) ($_POST['value'] ?? ''));
        if ($key !== '') {
            execute('UPDATE settings SET value = ? WHERE `key` = ?', [$value, $key]);
            flash('success', 'Setting updated.');
        }
        redirect_to(site_url("admin/manage.php?entity=settings"));
    }

if (!$readonly && !empty($fields)) {
        $id = trim((string) ($_POST[$pk] ?? ''));
        $data = [];
        foreach ($config['fields'] as $field) {
            $name = $field['name'];
            $type = $field['type'];
            $default = $field['default'] ?? '';

            if ($type === 'checkbox') {
                $data[$name] = isset($_POST[$name]) ? 1 : 0;
                continue;
            }

            if ($type === 'file') {
                $existing = trim((string) ($_POST['existing_' . $name] ?? ''));
                $data[$name] = upload_asset($name, $existing);
                continue;
            }

            $value = trim((string) ($_POST[$name] ?? $default));
            if ($name === 'slug' && $value === '') {
                $source = trim((string) ($_POST['title'] ?? ''));
                $value = slugify($source);
            }
            $data[$name] = $value;
        }

        if ($entity === 'products' && empty($data['image'])) {
            $data['image'] = asset_url('images/product-espresso.svg');
        }
        if ($entity === 'gallery_items' && empty($data['image'])) {
            $data['image'] = asset_url('images/gallery-interior.svg');
        }
        if ($entity === 'blog_posts' && empty($data['cover_image'])) {
            $data['cover_image'] = asset_url('images/blog-1.svg');
        }

        if ($id === '') {
            if (!isset($data['created_at']) && $entity !== 'settings') {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            $columns = array_keys($data);
            $placeholders = array_fill(0, count($columns), '?');
            execute(
                "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ')',
                array_values($data)
            );
            flash('success', "{$title} created.");
        } else {
            $assignments = [];
            foreach ($data as $column => $value) {
                $assignments[] = "{$column} = ?";
            }
            execute(
                "UPDATE {$table} SET " . implode(', ', $assignments) . " WHERE {$pk} = ?",
                array_merge(array_values($data), [$id])
            );
            flash('success', "{$title} updated.");
        }

        redirect_to(site_url("admin/manage.php?entity={$entity}"));
    }
}

if ($entity === 'reservations' && isset($_GET['status'], $_GET['id'])) {
    execute('UPDATE reservations SET status = ? WHERE id = ?', [$_GET['status'], $_GET['id']]);
    flash('success', 'Reservation status updated.');
    redirect_to(site_url("admin/manage.php?entity=reservations"));
}

if ($entity === 'contacts' && isset($_GET['status'], $_GET['id'])) {
    execute('UPDATE contacts SET status = ? WHERE id = ?', [$_GET['status'], $_GET['id']]);
    flash('success', 'Message status updated.');
    redirect_to(site_url("admin/manage.php?entity=contacts"));
}

if ($entity === 'orders' && isset($_GET['status'], $_GET['id'])) {
    execute('UPDATE orders SET status = ? WHERE id = ?', [$_GET['status'], $_GET['id']]);
    flash('success', 'Order status updated.');
    redirect_to(site_url("admin/manage.php?entity=orders"));
}

if ($entity === 'applications' && isset($_GET['status'], $_GET['id'])) {
    execute('UPDATE applications SET status = ? WHERE id = ?', [$_GET['status'], $_GET['id']]);
    flash('success', 'Application status updated.');
    redirect_to(site_url("admin/manage.php?entity=applications"));
}

$rows = entity_rows($entity);
$editRow = null;
if (!$readonly && !empty($fields) && isset($_GET['edit'])) {
    $editRow = fetch_one("SELECT * FROM {$table} WHERE {$pk} = ?", [$_GET['edit']]);
}

$listMessage = get_flash('success');
$categories = category_options();
$siteSettings = fetch_all('SELECT * FROM settings ORDER BY key ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($title) ?> | Admin</title>
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
                    <p class="eyebrow">Manage</p>
                    <h1><?= h($title) ?></h1>
                </div>
                <a class="btn btn-light" href="<?= h(site_url('admin/index.php')) ?>">Back to Dashboard</a>
            </header>

            <?php if ($listMessage): ?>
                <div class="alert alert-success"><?= h($listMessage) ?></div>
            <?php endif; ?>

            <?php if (!$readonly && $entity !== 'settings' && !empty($fields)): ?>
                <section class="panel">
                    <h2><?= $editRow ? 'Edit' : 'Add' ?> <?= h($title) ?></h2>
                    <form method="post" enctype="multipart/form-data" class="form-grid">
                        <?= csrf_field() ?>
                        <input type="hidden" name="form_action" value="save">
                        <?php if ($editRow): ?>
                            <input type="hidden" name="<?= h($pk) ?>" value="<?= h((string) $editRow[$pk]) ?>">
                        <?php endif; ?>
                        <?php foreach ($fields as $field): ?>
                            <?php
                            $name = $field['name'];
                            $type = $field['type'];
                            $label = $field['label'];
                            $value = $editRow[$name] ?? ($field['default'] ?? '');
                            ?>
                            <label>
                                <?= h($label) ?>
                                <?php if ($type === 'textarea'): ?>
                                    <textarea name="<?= h($name) ?>" rows="4" placeholder="<?= h($field['placeholder'] ?? '') ?>"><?= h((string) $value) ?></textarea>
                                <?php elseif ($type === 'select'): ?>
                                    <select name="<?= h($name) ?>">
                                        <?php
                                        $options = $field['options'] ?? [];
                                        if ($options === 'categories') {
                                            $options = $categories;
                                        }
                                        foreach ($options as $optValue => $optLabel):
                                        ?>
                                            <option value="<?= h((string) $optValue) ?>" <?= (string) $value === (string) $optValue ? 'selected' : '' ?>>
                                                <?= h((string) $optLabel) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($type === 'checkbox'): ?>
                                    <input type="checkbox" name="<?= h($name) ?>" value="1" <?= (int) $value ? 'checked' : '' ?>>
                                <?php elseif ($type === 'file'): ?>
                                    <div class="file-preview">
                                        <img
                                            class="image-preview"
                                            data-preview-image="<?= h($name) ?>"
                                            src="<?= h(media_url((string) ($value ?: asset_url('images/hero.svg')))) ?>"
                                            alt="Preview"
                                        >
                                        <input type="file" name="<?= h($name) ?>" accept="image/*" data-preview-input="<?= h($name) ?>">
                                    </div>
                                    <?php if (!empty($value)): ?>
                                        <small>Current: <?= h((string) $value) ?></small>
                                        <input type="hidden" name="existing_<?= h($name) ?>" value="<?= h((string) $value) ?>">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <input
                                        type="<?= h($type) ?>"
                                        name="<?= h($name) ?>"
                                        value="<?= h((string) $value) ?>"
                                        step="<?= h((string) ($field['step'] ?? '1')) ?>"
                                        placeholder="<?= h($field['placeholder'] ?? '') ?>"
                                    >
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                        <button class="btn btn-primary" type="submit"><?= $editRow ? 'Update' : 'Create' ?></button>
                    </form>
                </section>
            <?php endif; ?>

            <?php if ($entity === 'settings'): ?>
                <section class="panel">
                    <h2>Site Settings</h2>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr><th>Key</th><th>Value</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($siteSettings as $row): ?>
                                    <tr>
                                        <td><?= h($row['key']) ?></td>
                                        <td><?= h($row['value']) ?></td>
                                        <td>
                                            <form method="post" class="inline-form">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="key" value="<?= h($row['key']) ?>">
                                                <input type="text" name="value" value="<?= h($row['value']) ?>">
                                                <button class="btn btn-sm btn-primary" type="submit">Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php else: ?>
                <section class="panel">
                    <h2>All <?= h($title) ?></h2>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <?php if ($rows): ?>
                                        <?php foreach (array_keys($rows[0]) as $column): ?>
                                            <th><?= h((string) $column) ?></th>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $column => $value): ?>
                                            <td>
                                                <?php if (in_array($column, ['image', 'cover_image'], true) && $value): ?>
                                                    <img class="thumb" src="<?= h(media_url((string) $value)) ?>" alt="">
                                                <?php elseif (in_array($column, ['is_active', 'is_featured', 'is_approved'], true)): ?>
                                                    <?= (int) $value ? 'Yes' : 'No' ?>
                                                <?php else: ?>
                                                    <?= h((string) $value) ?>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td>
                                            <div class="action-row">
                                                <?php if (!$readonly && !empty($fields)): ?>
                                                    <a class="btn btn-sm btn-light" href="<?= h(site_url('admin/manage.php?entity=' . $entity . '&edit=' . (string) $row[$pk])) ?>">Edit</a>
                                                <?php endif; ?>
                                                <?php if ($entity === 'reservations'): ?>
                                                    <a class="btn btn-sm btn-light" href="<?= h(site_url('admin/manage.php?entity=reservations&status=confirmed&id=' . (string) $row[$pk])) ?>">Confirm</a>
                                                    <a class="btn btn-sm btn-light" href="<?= h(site_url('admin/manage.php?entity=reservations&status=cancelled&id=' . (string) $row[$pk])) ?>">Cancel</a>
                                                <?php endif; ?>
                                                <?php if ($entity === 'contacts'): ?>
                                                    <a class="btn btn-sm btn-light" href="<?= h(site_url('admin/manage.php?entity=contacts&status=read&id=' . (string) $row[$pk])) ?>">Mark Read</a>
                                                <?php endif; ?>
                                                <?php if ($entity === 'orders'): ?>
                                                    <a class="btn btn-sm btn-light" href="<?= h(site_url('admin/order.php?id=' . (string) $row[$pk])) ?>">Details</a>
                                                    <a class="btn btn-sm btn-light" href="<?= h(site_url('admin/invoice.php?id=' . (string) $row[$pk])) ?>" target="_blank">Invoice</a>
                                                    <a class="btn btn-sm btn-light" href="<?= h(site_url('admin/manage.php?entity=orders&status=processing&id=' . (string) $row[$pk])) ?>">Processing</a>
                                                    <a class="btn btn-sm btn-light" href="<?= h(site_url('admin/manage.php?entity=orders&status=delivered&id=' . (string) $row[$pk])) ?>">Delivered</a>
                                                    <a class="btn btn-sm btn-light" href="<?= h(site_url('admin/manage.php?entity=orders&status=cancelled&id=' . (string) $row[$pk])) ?>">Cancel</a>
                                                <?php endif; ?>
                                                <?php if ($entity === 'applications'): ?>
                                                    <a class="btn btn-sm btn-light" href="<?= h(site_url('admin/manage.php?entity=applications&status=reviewed&id=' . (string) $row[$pk])) ?>">Reviewed</a>
                                                    <a class="btn btn-sm btn-light" href="<?= h(site_url('admin/manage.php?entity=applications&status=rejected&id=' . (string) $row[$pk])) ?>">Rejected</a>
                                                <?php endif; ?>
                                                <?php if (!$readonly): ?>
                                                    <form method="post" onsubmit="return confirm('Delete this item?');">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="form_action" value="delete">
                                                        <input type="hidden" name="<?= h($pk) ?>" value="<?= h((string) $row[$pk]) ?>">
                                                        <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>
    <script src="<?= h(asset_url('js/admin.js')) ?>"></script>
</body>
</html>
