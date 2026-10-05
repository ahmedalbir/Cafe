<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Dhaka');

const APP_NAME = 'D Cafe';
const APP_ROOT = __DIR__ . '/..';
const STORAGE_PATH = APP_ROOT . '/storage';
const DB_PATH = STORAGE_PATH . '/mycafe.sqlite';
const SCHEMA_PATH = APP_ROOT . '/database/schema.sql';

if (!is_dir(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0775, true);
}

$sessionPath = STORAGE_PATH . '/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}
session_save_path($sessionPath);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function db_driver(): string
{
    $driver = strtolower(trim((string) getenv('DB_DRIVER')));
    return in_array($driver, ['mysql', 'sqlite'], true) ? $driver : 'sqlite';
}

function request_script_path(): string
{
    foreach (['SCRIPT_NAME', 'PHP_SELF'] as $key) {
        $value = (string) ($_SERVER[$key] ?? '');
        $value = str_replace('\\', '/', trim($value));
        if ($value !== '' && str_starts_with($value, '/')) {
            return $value;
        }
    }

    return '';
}

function app_base_url(): string
{
    $script = request_script_path();
    if ($script === '') {
        return '';
    }

    $dir = rtrim(dirname($script), '/');

    if ($dir === '' || $dir === '.' || $dir === '/') {
        return '';
    }

    if (str_ends_with($dir, '/admin')) {
        $dir = rtrim(dirname($dir), '/');
    }

    if ($dir === '' || $dir === '.' || $dir === '/') {
        return '';
    }

    return $dir;
}

function site_url(string $path = ''): string
{
    $path = ltrim($path, '/');
    $base = app_base_url();
    $prefix = $base === '' ? '' : $base;
    return ($prefix === '' ? '/' : $prefix . '/') . $path;
}

function asset_url(string $path = ''): string
{
    return site_url('assets/' . ltrim($path, '/'));
}

function active_lang_code(): string
{
    $lang = $_SESSION['lang'] ?? ($_GET['lang'] ?? 'en');
    return in_array($lang, ['en', 'bn'], true) ? (string) $lang : 'en';
}

function page_url(array $query = [], string $fragment = ''): string
{
    $path = site_url('index.php');
    $query = array_merge(['lang' => active_lang_code()], $query);
    $queryString = http_build_query($query);
    if ($queryString !== '') {
        $path .= '?' . $queryString;
    }
    if ($fragment !== '') {
        $path .= '#' . ltrim($fragment, '#');
    }
    return $path;
}

function media_url(string $path = ''): string
{
    $path = trim($path);
    if ($path === '') {
        return '';
    }

    if (preg_match('~^(https?:)?//~', $path) || str_starts_with($path, 'data:')) {
        return $path;
    }

    $base = app_base_url();
    if ($base !== '' && str_starts_with($path, $base . '/')) {
        return $path;
    }

    if (str_starts_with($path, '/assets/')) {
        return site_url(ltrim($path, '/'));
    }

    if (str_starts_with($path, 'assets/')) {
        return site_url($path);
    }

    if ($path[0] === '/') {
        return site_url(ltrim($path, '/'));
    }

    return asset_url($path);
}

function db_dsn(): string
{
    if (db_driver() === 'mysql') {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $name = getenv('DB_NAME') ?: 'dcafe';
        return sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);
    }

    return 'sqlite:' . DB_PATH;
}

function db_mysql_server_dsn(): string
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    return sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port);
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (db_driver() === 'mysql') {
        $server = new PDO(
            db_mysql_server_dsn(),
            getenv('DB_USER') ?: 'root',
            getenv('DB_PASS') ?: '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $database = getenv('DB_NAME') ?: 'dcafe';
        $server->exec(sprintf('CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', str_replace('`', '``', $database)));
        $pdo = new PDO(
            db_dsn(),
            getenv('DB_USER') ?: 'root',
            getenv('DB_PASS') ?: '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } else {
        $pdo = new PDO(db_dsn());
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    if (db_driver() === 'sqlite') {
        $pdo->exec('PRAGMA foreign_keys = ON');
    }

    initialize_database($pdo);

    return $pdo;
}

function initialize_database(PDO $pdo): void
{
    $needsSeed = !table_exists($pdo, 'users');

    if ($needsSeed && file_exists(SCHEMA_PATH)) {
        $schema = file_get_contents(SCHEMA_PATH);
        if ($schema !== false) {
            $schema = normalize_schema_sql($schema);
            $pdo->exec($schema);
        }

        seed_database($pdo);
    }
}

function normalize_schema_sql(string $schema): string
{
    if (db_driver() === 'sqlite') {
        $schema = preg_replace('/^\s*PRAGMA foreign_keys = ON;\s*/m', '', $schema) ?? $schema;
        $schema = preg_replace('/^\s*id\s+BIGINT\s+UNSIGNED\s+NOT\s+NULL\s+AUTO_INCREMENT,\s*$/im', 'id INTEGER PRIMARY KEY AUTOINCREMENT,', $schema) ?? $schema;
        $schema = preg_replace('/^\s*id\s+INT\s+UNSIGNED\s+NOT\s+NULL\s+AUTO_INCREMENT,\s*$/im', 'id INTEGER PRIMARY KEY AUTOINCREMENT,', $schema) ?? $schema;
        $schema = preg_replace('/^\s*PRIMARY KEY\s*\(id\),?\s*$/im', '', $schema) ?? $schema;
        $schema = preg_replace('/\bBIGINT\s+UNSIGNED\b/i', 'INTEGER', $schema) ?? $schema;
        $schema = preg_replace('/\bINT\s+UNSIGNED\b/i', 'INTEGER', $schema) ?? $schema;
        $schema = preg_replace('/\bTINYINT\(1\)\b/i', 'INTEGER', $schema) ?? $schema;
        $schema = preg_replace('/\bDECIMAL\(\d+,\d+\)\b/i', 'REAL', $schema) ?? $schema;
        $schema = preg_replace("/,\s*\n\)/", "\n)", $schema) ?? $schema;
        $schema = preg_replace('/\)\s*ENGINE=InnoDB\s+DEFAULT\s+CHARSET=utf8mb4\s+COLLATE=utf8mb4_unicode_ci;/i', ');', $schema) ?? $schema;
    }

    return $schema;
}

function table_exists(PDO $pdo, string $table): bool
{
    if (db_driver() === 'mysql') {
        $stmt = $pdo->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name');
    } else {
        $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :name");
    }
    $stmt->execute([':name' => $table]);
    return (bool) $stmt->fetchColumn();
}

function seed_database(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute(['Admin', 'admin@dcafe.local', $adminPassword, 'admin', date('Y-m-d H:i:s')]);

    $settings = [
        ['site_name', 'D Cafe'],
        ['tagline', 'Where Every Cup Tells a Story'],
        ['hero_title', 'Where Every Cup Tells a Story'],
        ['hero_text', 'Discover signature coffee, handcrafted desserts, hearty snacks, and a cozy space designed for everyday moments and special celebrations.'],
        ['phone', '+880 1XXX-XXXXXX'],
        ['whatsapp', '+880 1XXX-XXXXXX'],
        ['email', 'hello@dcafe.example'],
        ['address', 'Your cafe address goes here, Dhaka, Bangladesh'],
        ['map_url', 'https://maps.google.com'],
        ['hours', 'Sat - Thu: 8:00 AM - 11:00 PM | Fri: 9:00 AM - 11:00 PM'],
        ['primary_color', '#8b4f2b'],
        ['accent_color', '#d6a057'],
        ['currency', '৳'],
    ];
    $stmt = $pdo->prepare('INSERT INTO settings (`key`, value) VALUES (?, ?)');
    foreach ($settings as $setting) {
        $stmt->execute($setting);
    }

    $categories = [
        ['Coffee', 'fa-solid fa-mug-hot', 1],
        ['Cold Drinks', 'fa-solid fa-glass-water', 2],
        ['Tea', 'fa-solid fa-mug-hot', 3],
        ['Snacks', 'fa-solid fa-burger', 4],
        ['Desserts', 'fa-solid fa-cake-candles', 5],
    ];
    $stmt = $pdo->prepare('INSERT INTO categories (name, icon, sort_order, is_active, created_at) VALUES (?, ?, ?, 1, ?)');
    foreach ($categories as $index => $category) {
        $stmt->execute([$category[0], $category[1], $category[2], date('Y-m-d H:i:s')]);
    }

    $categoryMap = [];
    foreach ($pdo->query('SELECT id, name FROM categories') as $row) {
        $categoryMap[$row['name']] = (int) $row['id'];
    }

    $products = [
        ['Classic Espresso', 'Coffee', 180, 'Strong, bold, and perfectly balanced for coffee lovers.', '/assets/images/product-espresso.svg', 1, 50],
        ['Velvet Cappuccino', 'Coffee', 220, 'Creamy milk foam with rich espresso and a soft finish.', '/assets/images/product-cappuccino.svg', 1, 42],
        ['Signature Latte', 'Coffee', 240, 'Smooth espresso, silky milk, and subtle sweetness.', '/assets/images/product-latte.svg', 1, 38],
        ['Iced Coffee', 'Cold Drinks', 260, 'Cool, refreshing, and perfect for warm afternoons.', '/assets/images/product-cold.svg', 1, 36],
        ['Premium Tea', 'Tea', 120, 'Aromatic tea for a calm and refreshing break.', '/assets/images/product-tea.svg', 0, 60],
        ['Fresh Juice', 'Cold Drinks', 180, 'Freshly squeezed fruit juice with natural sweetness.', '/assets/images/product-juice.svg', 1, 34],
        ['Crunchy Snack Box', 'Snacks', 280, 'A tasty mix of savory bites for every mood.', '/assets/images/product-snack.svg', 0, 28],
        ['Chocolate Dessert', 'Desserts', 320, 'Sweet, rich, and perfect to finish your meal.', '/assets/images/product-dessert.svg', 1, 20],
    ];
    $stmt = $pdo->prepare('INSERT INTO products (category_id, name, price, description, image, is_featured, stock, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)');
    foreach ($products as $product) {
        $stmt->execute([
            $categoryMap[$product[1]],
            $product[0],
            $product[2],
            $product[3],
            $product[4],
            $product[5],
            $product[6],
            date('Y-m-d H:i:s'),
        ]);
    }

    $offers = [
        ['Happy Hour 2PM - 5PM', 'HOT DEAL', 'Get special discounts on selected coffees and snacks.', 'percent', 15],
        ['Coffee + Dessert Bundle', 'COMBO', 'Pair your favorite drink with a sweet treat.', 'fixed', 50],
        ['Summer Refreshers', 'SEASONAL', 'Light and cool drinks for sunny afternoons.', 'percent', 20],
        ['Member Discount Coupon', 'COUPON', 'Use promo codes for regular savings.', 'percent', 10],
    ];
    $stmt = $pdo->prepare('INSERT INTO offers (title, badge, description, discount_type, discount_value, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, ?)');
    foreach ($offers as $offer) {
        $stmt->execute([$offer[0], $offer[1], $offer[2], $offer[3], $offer[4], date('Y-m-d H:i:s')]);
    }

    $testimonials = [
        ['Ayesha Rahman', 'Regular Customer', 5, 'The ambience is cozy and the cappuccino is always perfect.'],
        ['Mahin Khan', 'Google Review', 5, 'Fast service, clean space, and the desserts are excellent.'],
        ['Shila Akter', 'Happy Guest', 5, 'We booked a table for a family dinner and everything was smooth.'],
    ];
    $stmt = $pdo->prepare('INSERT INTO testimonials (name, role, rating, body, is_approved, created_at) VALUES (?, ?, ?, ?, 1, ?)');
    foreach ($testimonials as $testimonial) {
        $stmt->execute([$testimonial[0], $testimonial[1], $testimonial[2], $testimonial[3], date('Y-m-d H:i:s')]);
    }

    $gallery = [
        ['Interior Photos', 'Warm and modern atmosphere', '/assets/images/gallery-interior.svg', 'image'],
        ['Food Photos', 'Plated with care', '/assets/images/gallery-food.svg', 'image'],
        ['Coffee Photos', 'Freshly brewed every day', '/assets/images/gallery-coffee.svg', 'image'],
        ['Customer Moments', 'Memories with friends', '/assets/images/gallery-moment.svg', 'image'],
        ['Video Gallery', 'Behind the scenes vibe', '/assets/images/gallery-video.svg', 'video'],
    ];
    $stmt = $pdo->prepare('INSERT INTO gallery_items (title, caption, image, media_type, sort_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, ?)');
    foreach ($gallery as $index => $item) {
        $stmt->execute([$item[0], $item[1], $item[2], $item[3], $index + 1, date('Y-m-d H:i:s')]);
    }

    $posts = [
        ['How to Choose the Right Coffee Roast', 'coffee-tips', 'Coffee Tips', 'Learn the difference between light, medium, and dark roasts.', 'A practical guide to help customers choose a roast that matches their taste.', '/assets/images/blog-1.svg'],
        ['New Seasonal Menu Items Arriving Soon', 'new-seasonal-menu-items', 'New Products', 'Fresh drinks and desserts are coming to the menu this month.', 'A preview of what is new in the seasonal menu lineup.', '/assets/images/blog-2.svg'],
        ['Host Your Next Cafe Event With Us', 'host-your-next-cafe-event', 'Cafe Events', 'Explore packages for birthdays, meetings, and private gatherings.', 'A look at event hosting options available at D Cafe.', '/assets/images/blog-3.svg'],
    ];
    $stmt = $pdo->prepare('INSERT INTO blog_posts (title, slug, tag, excerpt, body, cover_image, status, published_at, created_at) VALUES (?, ?, ?, ?, ?, ?, "published", ?, ?)');
    foreach ($posts as $post) {
        $stmt->execute([$post[0], $post[1], $post[2], $post[3], $post[4], $post[5], date('Y-m-d'), date('Y-m-d H:i:s')]);
    }

    $careers = [
        ['Barista', 'On-site', 'Full-time', 'Friendly and skilled coffee maker with attention to detail.'],
        ['Service Crew', 'On-site', 'Part-time', 'Support guests with fast and warm table service.'],
    ];
    $stmt = $pdo->prepare('INSERT INTO careers (title, location, employment_type, description, is_active, created_at) VALUES (?, ?, ?, ?, 1, ?)');
    foreach ($careers as $career) {
        $stmt->execute([$career[0], $career[1], $career[2], $career[3], date('Y-m-d H:i:s')]);
    }
}

function fetch_all(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetch_one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function execute(string $sql, array $params = []): bool
{
    $stmt = db()->prepare($sql);
    return $stmt->execute($params);
}

function setting(string $key, string $default = ''): string
{
    $row = fetch_one('SELECT value FROM settings WHERE `key` = ?', [$key]);
    return $row['value'] ?? $default;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function money(float|int|string $value): string
{
    return setting('currency', '৳') . number_format((float) $value, 0);
}

function slugify(string $value): string
{
    $value = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower(trim($value))) ?? '';
    return trim($value, '-');
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function get_flash(string $type): ?string
{
    $message = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $message;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function current_admin(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return fetch_one('SELECT id, name, email, role FROM users WHERE id = ? AND role = "admin"', [$_SESSION['user_id']]);
}

function require_admin(): void
{
    if (!current_admin()) {
        header('Location: ' . site_url('admin/login.php'));
        exit;
    }
}

function redirect_to(string $path): void
{
    if ($path !== '' && $path[0] === '/') {
        $path = site_url(ltrim($path, '/'));
    }
    header('Location: ' . $path);
    exit;
}

function upload_asset(string $fieldName, string $fallback = ''): string
{
    if (!isset($_FILES[$fieldName]) || ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return $fallback;
    }

    $uploadDir = APP_ROOT . '/assets/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $original = basename((string) $_FILES[$fieldName]['name']);
    $safe = preg_replace('/[^a-zA-Z0-9._-]/', '-', $original) ?? 'upload';
    $target = $uploadDir . '/' . date('YmdHis') . '-' . $safe;

    if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $target)) {
        return '/assets/uploads/' . basename($target);
    }

    return $fallback;
}
