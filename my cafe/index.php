<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/site.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function cart_count(array $cart): int
{
    return array_sum(array_map('intval', $cart));
}

function cart_total(array $cart, array $products): float
{
    $total = 0.0;
    foreach ($cart as $productId => $qty) {
        if (isset($products[$productId])) {
            $total += (float) $products[$productId]['price'] * (int) $qty;
        }
    }
    return $total;
}

$categories = fetch_all('SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
$products = fetch_all('SELECT p.*, c.name AS category_name, c.icon AS category_icon FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.is_active = 1 ORDER BY p.id DESC');
$featuredProducts = fetch_all('SELECT p.*, c.name AS category_name, c.icon AS category_icon FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.is_active = 1 AND p.is_featured = 1 ORDER BY p.id DESC');
$offers = fetch_all('SELECT * FROM offers WHERE is_active = 1 ORDER BY id DESC');
$testimonials = fetch_all('SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY id DESC');
$gallery = fetch_all('SELECT * FROM gallery_items WHERE is_active = 1 ORDER BY sort_order ASC, id DESC');
$blogPosts = fetch_all('SELECT * FROM blog_posts WHERE status = "published" ORDER BY id DESC');
$careerJobs = fetch_all('SELECT * FROM careers WHERE is_active = 1 ORDER BY id DESC');

$productMap = [];
foreach ($products as $product) {
    $productMap[(string) $product['id']] = $product;
}

$cart = $_SESSION['cart'];
$cartCount = cart_count($cart);
$cartTotal = cart_total($cart, $productMap);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $form = (string) ($_POST['form_type'] ?? '');

    if ($form === 'reservation') {
        execute(
            'INSERT INTO reservations (name, phone, email, reservation_date, reservation_time, guests, special_request, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, "pending", ?)',
            [
                trim((string) $_POST['name']),
                trim((string) $_POST['phone']),
                trim((string) ($_POST['email'] ?? '')),
                trim((string) $_POST['date']),
                trim((string) $_POST['time']),
                (int) ($_POST['guests'] ?? 1),
                trim((string) ($_POST['request'] ?? '')),
                date('Y-m-d H:i:s'),
            ]
        );
        flash('success', 'Reservation request submitted successfully.');
        redirect_to(page_url([], 'reservation'));
    }

    if ($form === 'contact') {
        execute(
            'INSERT INTO contacts (name, email, subject, message, type, status, created_at) VALUES (?, ?, ?, ?, "contact", "new", ?)',
            [
                trim((string) $_POST['name']),
                trim((string) $_POST['email']),
                trim((string) $_POST['subject']),
                trim((string) $_POST['message']),
                date('Y-m-d H:i:s'),
            ]
        );
        flash('success', 'Your contact message has been sent.');
        redirect_to(page_url([], 'contact'));
    }

    if ($form === 'help') {
        execute(
            'INSERT INTO contacts (name, email, subject, message, type, status, created_at) VALUES (?, ?, ?, ?, ?, "new", ?)',
            [
                trim((string) $_POST['name']),
                trim((string) $_POST['email']),
                trim((string) ($_POST['topic'] ?? 'Support')),
                trim((string) $_POST['message']),
                strtolower(trim((string) ($_POST['topic'] ?? 'support'))),
                date('Y-m-d H:i:s'),
            ]
        );
        flash('success', 'Help request received.');
        redirect_to(page_url([], 'help'));
    }

    if ($form === 'newsletter') {
        execute(
            'INSERT INTO contacts (name, email, subject, message, type, status, created_at) VALUES (?, ?, "Newsletter", "Subscribed from footer form", "newsletter", "new", ?)',
            ['Visitor', trim((string) $_POST['email']), date('Y-m-d H:i:s')]
        );
        flash('success', 'Newsletter subscription successful.');
        redirect_to(page_url([], 'footer'));
    }

    if ($form === 'career') {
        execute(
            'INSERT INTO applications (career_id, name, email, phone, resume_link, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, "new", ?)',
            [
                (int) ($_POST['career_id'] ?? 0) ?: null,
                trim((string) $_POST['name']),
                trim((string) $_POST['email']),
                trim((string) $_POST['phone']),
                trim((string) ($_POST['resume_link'] ?? '')),
                trim((string) $_POST['message']),
                date('Y-m-d H:i:s'),
            ]
        );
        flash('success', 'Career application submitted.');
        redirect_to(page_url([], 'careers'));
    }

    if ($form === 'checkout') {
        if (!$cart) {
            flash('error', 'Your cart is empty.');
            redirect_to(page_url([], 'cart'));
        }

        $customerName = trim((string) $_POST['customer_name']);
        $phone = trim((string) $_POST['phone']);
        $email = trim((string) ($_POST['email'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));

        db()->beginTransaction();
        try {
            $total = cart_total($cart, $productMap);
            execute(
                'INSERT INTO orders (customer_name, phone, email, address, notes, status, total_amount, created_at) VALUES (?, ?, ?, ?, ?, "pending", ?, ?)',
                [$customerName, $phone, $email, $address, $notes, $total, date('Y-m-d H:i:s')]
            );
            $orderId = (int) db()->lastInsertId();
            $stmt = db()->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price) VALUES (?, ?, ?, ?, ?)');
            foreach ($cart as $productId => $qty) {
                if (!isset($productMap[$productId])) {
                    continue;
                }
                $item = $productMap[$productId];
                $stmt->execute([$orderId, (int) $item['id'], $item['name'], (int) $qty, (float) $item['price']]);
            }
            db()->commit();
            $_SESSION['cart'] = [];
            flash('success', 'Your order request has been placed successfully.');
            redirect_to(page_url([], 'cart'));
        } catch (Throwable $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            flash('error', 'Could not place order.');
            redirect_to(page_url([], 'cart'));
        }
    }
}

$action = (string) ($_GET['action'] ?? '');
if ($action === 'add_to_cart' && isset($_GET['id']) && isset($productMap[(string) $_GET['id']])) {
    $id = (string) $_GET['id'];
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    flash('success', $productMap[$id]['name'] . ' added to cart.');
    redirect_to(page_url([], 'menu'));
}

if ($action === 'remove_from_cart' && isset($_GET['id'])) {
    unset($_SESSION['cart'][(string) $_GET['id']]);
    flash('success', 'Item removed from cart.');
    redirect_to(page_url([], 'cart'));
}

if ($action === 'clear_cart') {
    $_SESSION['cart'] = [];
    flash('success', 'Cart cleared.');
    redirect_to(page_url([], 'cart'));
}

$flashSuccess = get_flash('success');
$flashError = get_flash('error');

$siteName = setting('site_name', APP_NAME);
$tagline = setting('tagline', 'Where Every Cup Tells a Story');
$heroTitle = setting('hero_title', $tagline);
$heroText = setting('hero_text', 'Discover signature coffee, handcrafted desserts, hearty snacks, and a cozy space designed for everyday moments and special celebrations.');
$phone = setting('phone', '');
$whatsapp = setting('whatsapp', '');
$email = setting('email', '');
$address = setting('address', '');
$mapUrl = setting('map_url', 'https://maps.google.com');
$hours = setting('hours', '');
$currency = setting('currency', '৳');
?>
<!DOCTYPE html>
<html lang="<?= h(locale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($siteName) ?> | <?= h($tagline) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= h(asset_url('css/style.css')) ?>">
</head>
<body>
    <header class="topbar">
        <div class="container nav-wrap">
            <a class="brand" href="#home">
                <span class="brand-mark"><i class="fa-solid fa-mug-hot"></i></span>
                <span>
                    <strong><?= h($siteName) ?></strong>
                    <small><?= h($tagline) ?></small>
                </span>
            </a>
            <button class="menu-toggle" aria-label="Open navigation" data-menu-toggle>
                <i class="fa-solid fa-bars"></i>
            </button>
            <nav class="nav" data-nav>
                <a href="#home"><i class="fa-solid fa-house"></i> <?= t('Home') ?></a>
                <a href="#about"><i class="fa-solid fa-circle-info"></i> <?= t('About') ?></a>
                <a href="#menu"><i class="fa-solid fa-mug-hot"></i> <?= t('Menu') ?></a>
                <a href="#reservation"><i class="fa-solid fa-calendar-check"></i> <?= t('Booking') ?></a>
                <details class="nav-dropdown">
                    <summary><i class="fa-solid fa-layer-group"></i> <?= t('More') ?></summary>
                    <div class="nav-dropdown-menu">
                        <a href="#featured"><i class="fa-solid fa-book-open"></i> <?= t('Featured Products') ?></a>
                        <a href="#services"><i class="fa-solid fa-bell-concierge"></i> <?= t('Services') ?></a>
                        <a href="#gallery"><i class="fa-solid fa-camera"></i> <?= t('Gallery') ?></a>
                        <a href="#offers"><i class="fa-solid fa-gift"></i> <?= t('Offers') ?></a>
                        <a href="#testimonials"><i class="fa-solid fa-star"></i> <?= t('Reviews') ?></a>
                        <a href="#faq"><i class="fa-solid fa-circle-question"></i> <?= t('FAQ') ?></a>
                        <a href="#contact"><i class="fa-solid fa-phone"></i> <?= t('Contact') ?></a>
                    </div>
                </details>
                <a href="#cart" class="nav-cart"><i class="fa-solid fa-cart-shopping"></i> <?= t('Shopping Cart') ?> <span><?= (int) $cartCount ?></span></a>
                <a href="<?= h(lang_toggle_href()) ?>" class="btn btn-light btn-sm"><?= locale() === 'en' ? 'বাংলা' : 'English' ?></a>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero" id="home">
            <div class="container hero-grid">
                <div class="hero-copy">
                    <p class="eyebrow"><?= t('Welcome to') ?> <?= h($siteName) ?></p>
                    <h1><?= h($heroTitle) ?></h1>
                    <p class="lead"><?= h($heroText) ?></p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="<?= h(page_url([], 'menu')) ?>"><?= t('Order Now') ?></a>
                        <a class="btn btn-secondary" href="<?= h(page_url([], 'reservation')) ?>"><?= t('Book a Table') ?></a>
                    </div>
                    <div class="hero-metrics">
                        <div><strong>Premium</strong><span>Crafted menu</span></div>
                        <div><strong>Fast</strong><span>Easy ordering</span></div>
                        <div><strong>Open</strong><span>Daily service</span></div>
                    </div>
                </div>
                <div class="hero-visual">
                    <img src="<?= h(asset_url('images/hero.svg')) ?>" alt="D Cafe hero image">
                    <div class="hero-floating card">
                        <span><i class="fa-solid fa-star"></i> 4.9 Google rating</span>
                        <strong>Special offer: Buy 1 get 1 on selected drinks</strong>
                    </div>
                </div>
            </div>
        </section>

        <?php if ($flashSuccess): ?>
            <section class="container"><div class="flash"><?= h($flashSuccess) ?></div></section>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <section class="container"><div class="flash"><?= h($flashError) ?></div></section>
        <?php endif; ?>

        <section class="section" id="about">
            <div class="container split">
                <div class="section-visual">
                    <img src="<?= h(asset_url('images/about.svg')) ?>" alt="About D Cafe">
                </div>
                <div class="section-copy">
                    <p class="eyebrow"><?= t('About') ?></p>
                    <h2>Our cafe story, mission and vision</h2>
                    <p>D Cafe was created to build a warm neighborhood space where coffee, food, and hospitality feel personal and premium.</p>
                    <div class="info-grid">
                        <article class="mini-card"><h3>Mission</h3><p>Deliver quality, comfort, and consistent service.</p></article>
                        <article class="mini-card"><h3>Vision</h3><p>Become a beloved cafe brand with memorable experiences.</p></article>
                        <article class="mini-card"><h3>Why Choose Us</h3><p>Fresh ingredients, great ambience, and flexible service options.</p></article>
                        <article class="mini-card"><h3>Team</h3><p>Friendly staff focused on warm, fast, and reliable service.</p></article>
                    </div>
                </div>
            </div>
        </section>

        <section class="section featured" id="featured">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow"><?= t('Featured Products') ?></p>
                        <h2><?= t('Popular picks from D Cafe') ?></h2>
                    </div>
                </div>
                <div class="cards featured-cards">
                    <?php foreach ($featuredProducts as $item): ?>
                        <article class="product-card featured-card">
                            <img src="<?= h(media_url((string) $item['image'])) ?>" alt="<?= h($item['name']) ?>">
                            <div class="product-body">
                                <div class="product-top">
                                    <h4><?= h($item['name']) ?></h4>
                                    <span><?= money((float) $item['price']) ?></span>
                                </div>
                                <p><?= h($item['description']) ?></p>
                                <a class="btn btn-primary btn-sm" href="<?= h(page_url(['action' => 'add_to_cart', 'id' => (string) $item['id']], 'cart')) ?>"><?= t('Add to Cart') ?></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section alt" id="menu">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow"><?= t('Menu') ?> / <?= t('Featured Products') ?></p>
                        <h2><?= t('All items by category') ?></h2>
                    </div>
                        <a class="btn btn-light" href="<?= h(page_url([], 'cart')) ?>"><i class="fa-solid fa-cart-shopping"></i> <?= t('View Cart') ?></a>
                </div>
                <?php
                $grouped = [];
                foreach ($products as $item) {
                    $grouped[$item['category_name']][] = $item;
                }
                ?>
                <?php foreach ($grouped as $categoryName => $items): ?>
                    <div class="category-block">
                        <h3><i class="<?= h(category_icon_map()[$categoryName] ?? 'fa-solid fa-mug-hot') ?>"></i> <?= h($categoryName) ?></h3>
                        <div class="cards">
                            <?php foreach ($items as $item): ?>
                                <article class="product-card">
                                    <img src="<?= h(media_url((string) $item['image'])) ?>" alt="<?= h($item['name']) ?>">
                                    <div class="product-body">
                                        <div class="product-top">
                                            <h4><?= h($item['name']) ?></h4>
                                            <span><?= money((float) $item['price']) ?></span>
                                        </div>
                                        <p><?= h($item['description']) ?></p>
                                        <a class="btn btn-primary btn-sm" href="<?= h(page_url(['action' => 'add_to_cart', 'id' => (string) $item['id']], 'cart')) ?>"><?= t('Add to Cart') ?></a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section" id="services">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow"><?= t('Services') ?></p>
                        <h2><?= t('Everything your cafe customers need') ?></h2>
                    </div>
                </div>
                <div class="service-grid">
                    <?php foreach (site_services() as $service): ?>
                        <article class="service-card">
                            <i class="<?= h($service['icon']) ?>"></i>
                            <h3><?= h($service['title']) ?></h3>
                            <p><?= h($service['text']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section alt" id="gallery">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow"><?= t('Gallery') ?></p>
                        <h2><?= t('Interior, food, coffee, and moments') ?></h2>
                    </div>
                </div>
                <div class="gallery-grid">
                    <?php foreach ($gallery as $item): ?>
                        <figure class="gallery-item">
                            <img src="<?= h(media_url((string) $item['image'])) ?>" alt="<?= h($item['title']) ?>">
                            <figcaption>
                                <strong><?= h($item['title']) ?></strong>
                                <span><?= h($item['caption']) ?></span>
                            </figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section" id="reservation">
            <div class="container split">
                <div class="section-copy">
                    <p class="eyebrow"><?= t('Booking') ?></p>
                    <h2><?= t('Table booking') ?></h2>
                    <p>Book a comfortable table for friends, family, meetings, or celebrations.</p>
                    <form class="form-grid" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="form_type" value="reservation">
                        <input name="name" placeholder="<?= t('Name') ?>" required>
                        <input name="phone" placeholder="<?= t('Phone') ?>" required>
                        <input name="email" type="email" placeholder="<?= t('Email') ?>">
                        <input type="date" name="date" required>
                        <input type="time" name="time" required>
                        <input type="number" name="guests" placeholder="Number of Guests" min="1" max="20" required>
                        <textarea name="request" placeholder="<?= t('Special Request') ?>" rows="4"></textarea>
                        <button class="btn btn-primary" type="submit"><?= t('Reserve Now') ?></button>
                    </form>
                </div>
                <div class="section-visual">
                    <img src="<?= h(asset_url('images/reservation.svg')) ?>" alt="Reservation">
                </div>
            </div>
        </section>

        <section class="section alt" id="offers">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow"><?= t('Offers') ?></p>
                        <h2><?= t('Discount coupons, happy hour, combo offers') ?></h2>
                    </div>
                </div>
                <div class="offer-grid">
                    <?php foreach ($offers as $offer): ?>
                        <article class="offer-card">
                            <span class="badge"><?= h($offer['badge']) ?></span>
                            <h3><?= h($offer['title']) ?></h3>
                            <p><?= h($offer['description']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section" id="testimonials">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow"><?= t('Reviews') ?></p>
                        <h2><?= t('Customer reviews and success stories') ?></h2>
                    </div>
                </div>
                <div class="testimonial-grid">
                    <?php foreach ($testimonials as $item): ?>
                        <article class="testimonial-card">
                            <div class="stars">
                                <?php for ($i = 0; $i < (int) $item['rating']; $i++): ?>
                                    <i class="fa-solid fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p><?= h($item['body']) ?></p>
                            <strong><?= h($item['name']) ?></strong>
                            <span><?= h($item['role']) ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section alt" id="owner">
            <div class="container split">
                <div class="section-visual">
                    <img src="<?= h(asset_url('images/owner.svg')) ?>" alt="Owner message">
                </div>
                <div class="section-copy">
                    <p class="eyebrow">Owner Message</p>
                    <h2>Welcome message from the owner</h2>
                    <p>Our goal is simple: serve honest coffee, delicious food, and create a place where people want to return again and again.</p>
                    <p>The vision for D Cafe is to grow into a modern hospitality brand while keeping the warmth of a neighborhood cafe.</p>
                </div>
            </div>
        </section>

        <section class="section" id="faq">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow"><?= t('FAQ') ?></p>
                        <h2><?= t('Frequently asked questions') ?></h2>
                    </div>
                </div>
                <div class="faq-list">
                    <?php foreach (site_faqs() as $faq): ?>
                        <details class="faq-item">
                            <summary><?= h($faq['q']) ?></summary>
                            <p><?= h($faq['a']) ?></p>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section alt" id="contact">
            <div class="container split">
                <div class="section-copy">
                    <p class="eyebrow"><?= t('Contact') ?></p>
                    <h2><?= t('Get in touch') ?></h2>
                    <div class="contact-list">
                        <div><i class="fa-solid fa-phone"></i> <?= h($phone) ?></div>
                        <div><i class="fa-brands fa-whatsapp"></i> <?= h($whatsapp) ?></div>
                        <div><i class="fa-solid fa-envelope"></i> <?= h($email) ?></div>
                        <div><i class="fa-solid fa-location-dot"></i> <?= h($address) ?></div>
                    </div>
                    <form class="form-grid" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="form_type" value="contact">
                        <input name="name" placeholder="<?= t('Name') ?>" required>
                        <input name="email" type="email" placeholder="<?= t('Email') ?>" required>
                        <input name="subject" placeholder="<?= t('Subject') ?>" required>
                        <textarea name="message" placeholder="<?= t('Message') ?>" rows="5" required></textarea>
                        <button class="btn btn-primary" type="submit"><?= t('Send Message') ?></button>
                    </form>
                </div>
                <div class="section-visual map-card">
                    <img src="<?= h(asset_url('images/contact.svg')) ?>" alt="Contact D Cafe">
                    <a class="btn btn-light" href="<?= h($mapUrl) ?>" target="_blank" rel="noreferrer">
                        <i class="fa-solid fa-location-dot"></i> Open Google Map
                    </a>
                </div>
            </div>
        </section>

        <section class="section" id="help">
            <div class="container split">
                <div class="section-copy">
                    <p class="eyebrow">Help Center</p>
                    <h2><?= t('Support, complaint, feedback forms') ?></h2>
                    <form class="form-grid" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="form_type" value="help">
                        <input name="name" placeholder="<?= t('Name') ?>" required>
                        <input name="email" type="email" placeholder="<?= t('Email') ?>" required>
                        <select name="topic">
                            <option>Support</option>
                            <option>Complaint</option>
                            <option>Feedback</option>
                        </select>
                        <textarea name="message" placeholder="<?= t('Message') ?>" rows="5" required></textarea>
                        <button class="btn btn-primary" type="submit"><?= t('Submit Request') ?></button>
                    </form>
                </div>
                <div class="section-visual">
                    <img src="<?= h(asset_url('images/help.svg')) ?>" alt="Help center">
                </div>
            </div>
        </section>

        <section class="section alt" id="blog">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Blog / News</p>
                        <h2><?= t('Coffee tips, new products, events, and food blogs') ?></h2>
                    </div>
                </div>
                <div class="blog-grid">
                    <?php foreach ($blogPosts as $post): ?>
                        <article class="blog-card">
                            <img src="<?= h(media_url((string) $post['cover_image'])) ?>" alt="<?= h($post['title']) ?>">
                            <div class="blog-body">
                                <span class="badge"><?= h($post['tag']) ?></span>
                                <h3><?= h($post['title']) ?></h3>
                                <p><?= h($post['excerpt']) ?></p>
                                <span class="meta"><?= h((string) $post['published_at']) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section" id="careers">
            <div class="container split">
                <div class="section-copy">
                    <p class="eyebrow">Careers</p>
                    <h2><?= t('Join the D Cafe team') ?></h2>
                    <p>We are always looking for friendly, energetic people for service, kitchen, and admin roles.</p>
                    <div class="info-grid">
                        <?php foreach ($careerJobs as $job): ?>
                            <article class="mini-card">
                                <h3><?= h($job['title']) ?></h3>
                                <p><?= h($job['location']) ?> | <?= h($job['employment_type']) ?></p>
                                <p><?= h($job['description']) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <form class="form-grid" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="form_type" value="career">
                        <select name="career_id">
                            <option value=""><?= t('Select a job') ?></option>
                            <?php foreach ($careerJobs as $job): ?>
                                <option value="<?= h((string) $job['id']) ?>"><?= h($job['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input name="name" placeholder="<?= t('Name') ?>" required>
                        <input name="email" type="email" placeholder="<?= t('Email') ?>" required>
                        <input name="phone" placeholder="<?= t('Phone') ?>" required>
                        <input name="resume_link" placeholder="Resume link or file URL">
                        <textarea name="message" placeholder="<?= t('Why do you want to join?') ?>" rows="5" required></textarea>
                        <button class="btn btn-primary" type="submit"><?= t('Apply Online') ?></button>
                    </form>
                </div>
                <div class="section-visual">
                    <img src="<?= h(asset_url('images/careers.svg')) ?>" alt="Careers">
                </div>
            </div>
        </section>

        <section class="section alt" id="cart">
            <div class="container">
                <div class="section-heading">
                    <div>
                    <p class="eyebrow"><?= t('Shopping Cart') ?></p>
                    <h2><?= t('Your selected items') ?></h2>
                    </div>
                        <a class="btn btn-light" href="<?= h(page_url(['action' => 'clear_cart'], 'cart')) ?>"><i class="fa-solid fa-trash"></i> <?= t('Clear Cart') ?></a>
                </div>
                <div class="cart-layout">
                    <div class="cart-items">
                        <?php if (!$cart): ?>
                            <div class="empty-state">
                                <i class="fa-solid fa-cart-shopping"></i>
                                <p>Your cart is empty. Add products from the menu.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($cart as $productId => $qty): ?>
                                <?php if (isset($productMap[$productId])): $product = $productMap[$productId]; ?>
                                    <article class="cart-item">
                                        <img src="<?= h(media_url((string) $product['image'])) ?>" alt="<?= h($product['name']) ?>">
                                        <div>
                                            <h3><?= h($product['name']) ?></h3>
                                            <p><?= h($product['category_name']) ?></p>
                                        </div>
                                        <strong><?= (int) $qty ?> x <?= money((float) $product['price']) ?></strong>
                                        <a class="btn btn-link" href="<?= h(page_url(['action' => 'remove_from_cart', 'id' => (string) $productId], 'cart')) ?>">Remove</a>
                                    </article>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <aside class="cart-summary">
                        <h3><?= t('Summary') ?></h3>
                        <p>Items: <?= (int) $cartCount ?></p>
                        <p>Total: <?= money($cartTotal) ?></p>
                        <form method="post" class="form-grid">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_type" value="checkout">
                            <input name="customer_name" placeholder="<?= t('Customer name') ?>" required>
                            <input name="phone" placeholder="<?= t('Phone') ?>" required>
                            <input name="email" type="email" placeholder="<?= t('Email') ?>">
                            <textarea name="address" placeholder="<?= t('Delivery address') ?>" rows="3"></textarea>
                            <textarea name="notes" placeholder="Order notes" rows="3"></textarea>
                            <button class="btn btn-primary" type="submit"><?= t('Place Order') ?></button>
                        </form>
                    </aside>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer" id="footer">
        <div class="container footer-grid">
            <div>
                <div class="brand footer-brand">
                    <span class="brand-mark"><i class="fa-solid fa-mug-hot"></i></span>
                    <span>
                        <strong><?= h($siteName) ?></strong>
                        <small><?= h($tagline) ?></small>
                    </span>
                </div>
                <p>Fresh coffee, cozy ambience, and friendly service in one place.</p>
            </div>
            <div>
                <h3><?= t('Quick Links') ?></h3>
                <ul>
                    <li><a href="<?= h(page_url([], 'about')) ?>">About</a></li>
                    <li><a href="<?= h(page_url([], 'menu')) ?>">Menu</a></li>
                    <li><a href="<?= h(page_url([], 'reservation')) ?>">Booking</a></li>
                    <li><a href="<?= h(page_url([], 'contact')) ?>">Contact</a></li>
                </ul>
            </div>
            <div>
                <h3><?= t('Opening Hours') ?></h3>
                <p><?= h($hours) ?></p>
                <form class="newsletter" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_type" value="newsletter">
                    <input name="email" type="email" placeholder="<?= t('Newsletter email') ?>" required>
                    <button class="btn btn-primary btn-sm" type="submit">Subscribe</button>
                </form>
            </div>
            <div>
                <h3><?= t('Social Media') ?></h3>
                <div class="socials">
                    <?php foreach (social_links() as $social): ?>
                        <a href="<?= h($social['url']) ?>" target="_blank" rel="noreferrer" aria-label="<?= h($social['name']) ?>">
                            <i class="<?= h($social['icon']) ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>&copy; <?= date('Y') ?> <?= h($siteName) ?>. All rights reserved.</span>
            <span>Public site + admin panel powered by PHP and SQLite.</span>
        </div>
    </footer>

    <script src="<?= h(asset_url('js/main.js')) ?>"></script>
</body>
</html>
