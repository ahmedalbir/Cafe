<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (current_admin()) {
    redirect_to(site_url('admin/index.php'));
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $user = fetch_one('SELECT * FROM users WHERE email = ? AND role = "admin"', [$email]);
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        flash('success', 'Welcome back, ' . $user['name'] . '.');
        redirect_to(site_url('admin/index.php'));
    }

    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= h(asset_url('css/admin.css')) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-brand">
                <div class="brand-mark"><i class="fa-solid fa-mug-hot"></i></div>
                <div>
                    <h1><?= h(APP_NAME) ?></h1>
                    <p>Admin access</p>
                </div>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            <form method="post" class="stack">
                <?= csrf_field() ?>
                <label>Email
                    <input type="email" name="email" value="admin@dcafe.local" required>
                </label>
                <label>Password
                    <input type="password" name="password" required>
                </label>
                <button class="btn btn-primary" type="submit">Sign In</button>
            </form>
            <p class="auth-note">Default login: admin@dcafe.local / admin123</p>
        </div>
    </div>
</body>
</html>
