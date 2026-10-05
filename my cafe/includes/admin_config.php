<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/site.php';

function admin_entities(): array
{
    return [
        'categories' => [
            'title' => 'Categories',
            'table' => 'categories',
            'pk' => 'id',
            'order_by' => 'sort_order ASC, id DESC',
            'list_sql' => 'SELECT * FROM categories ORDER BY sort_order ASC, id DESC',
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'icon', 'label' => 'Icon Class', 'type' => 'text', 'required' => true, 'placeholder' => 'fa-solid fa-mug-hot'],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'required' => true, 'default' => 0],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'products' => [
            'title' => 'Products',
            'table' => 'products',
            'pk' => 'id',
            'order_by' => 'id DESC',
            'list_sql' => 'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC',
            'fields' => [
                ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'required' => true, 'options' => 'categories'],
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'price', 'label' => 'Price', 'type' => 'number', 'step' => '1', 'required' => true],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => true],
                ['name' => 'image', 'label' => 'Image Path', 'type' => 'file', 'required' => false],
                ['name' => 'is_featured', 'label' => 'Featured', 'type' => 'checkbox', 'default' => 0],
                ['name' => 'stock', 'label' => 'Stock', 'type' => 'number', 'required' => true, 'default' => 0],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'offers' => [
            'title' => 'Offers',
            'table' => 'offers',
            'pk' => 'id',
            'order_by' => 'id DESC',
            'list_sql' => 'SELECT * FROM offers ORDER BY id DESC',
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
                ['name' => 'badge', 'label' => 'Badge', 'type' => 'text', 'required' => true, 'placeholder' => 'HOT DEAL'],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => true],
                ['name' => 'discount_type', 'label' => 'Discount Type', 'type' => 'select', 'options' => ['percent' => 'Percent', 'fixed' => 'Fixed Amount']],
                ['name' => 'discount_value', 'label' => 'Discount Value', 'type' => 'number', 'required' => true, 'default' => 0],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'reservations' => [
            'title' => 'Reservations',
            'table' => 'reservations',
            'pk' => 'id',
            'order_by' => 'id DESC',
            'list_sql' => 'SELECT * FROM reservations ORDER BY id DESC',
            'readonly' => true,
        ],
        'contacts' => [
            'title' => 'Messages',
            'table' => 'contacts',
            'pk' => 'id',
            'order_by' => 'id DESC',
            'list_sql' => 'SELECT * FROM contacts ORDER BY id DESC',
            'readonly' => true,
        ],
        'testimonials' => [
            'title' => 'Testimonials',
            'table' => 'testimonials',
            'pk' => 'id',
            'order_by' => 'id DESC',
            'list_sql' => 'SELECT * FROM testimonials ORDER BY id DESC',
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'role', 'label' => 'Role', 'type' => 'text', 'required' => true],
                ['name' => 'rating', 'label' => 'Rating', 'type' => 'number', 'required' => true, 'default' => 5],
                ['name' => 'body', 'label' => 'Review', 'type' => 'textarea', 'required' => true],
                ['name' => 'is_approved', 'label' => 'Approved', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'gallery_items' => [
            'title' => 'Gallery',
            'table' => 'gallery_items',
            'pk' => 'id',
            'order_by' => 'sort_order ASC, id DESC',
            'list_sql' => 'SELECT * FROM gallery_items ORDER BY sort_order ASC, id DESC',
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
                ['name' => 'caption', 'label' => 'Caption', 'type' => 'text', 'required' => true],
                ['name' => 'image', 'label' => 'Image Path', 'type' => 'file', 'required' => false],
                ['name' => 'media_type', 'label' => 'Media Type', 'type' => 'select', 'options' => ['image' => 'Image', 'video' => 'Video']],
                ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'required' => true, 'default' => 0],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'blog_posts' => [
            'title' => 'Blog Posts',
            'table' => 'blog_posts',
            'pk' => 'id',
            'order_by' => 'id DESC',
            'list_sql' => 'SELECT * FROM blog_posts ORDER BY id DESC',
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
                ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'required' => true],
                ['name' => 'tag', 'label' => 'Tag', 'type' => 'text', 'required' => true],
                ['name' => 'excerpt', 'label' => 'Excerpt', 'type' => 'textarea', 'required' => true],
                ['name' => 'body', 'label' => 'Body', 'type' => 'textarea', 'required' => true],
                ['name' => 'cover_image', 'label' => 'Cover Image', 'type' => 'file', 'required' => false],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'published' => 'Published']],
            ],
        ],
        'careers' => [
            'title' => 'Careers',
            'table' => 'careers',
            'pk' => 'id',
            'order_by' => 'id DESC',
            'list_sql' => 'SELECT * FROM careers ORDER BY id DESC',
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
                ['name' => 'location', 'label' => 'Location', 'type' => 'text', 'required' => true],
                ['name' => 'employment_type', 'label' => 'Employment Type', 'type' => 'text', 'required' => true],
                ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => true],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'applications' => [
            'title' => 'Applications',
            'table' => 'applications',
            'pk' => 'id',
            'order_by' => 'id DESC',
            'list_sql' => 'SELECT a.*, c.title AS career_title FROM applications a LEFT JOIN careers c ON c.id = a.career_id ORDER BY a.id DESC',
            'readonly' => true,
        ],
        'orders' => [
            'title' => 'Orders',
            'table' => 'orders',
            'pk' => 'id',
            'order_by' => 'id DESC',
            'list_sql' => 'SELECT o.*, COALESCE(i.item_count, 0) AS item_count FROM orders o LEFT JOIN (SELECT order_id, COUNT(*) AS item_count FROM order_items GROUP BY order_id) i ON i.order_id = o.id ORDER BY o.id DESC',
            'readonly' => true,
        ],
        'settings' => [
            'title' => 'Settings',
            'table' => 'settings',
            'pk' => 'key',
            'order_by' => 'key ASC',
            'list_sql' => 'SELECT * FROM settings ORDER BY key ASC',
            'fields' => [
                ['name' => 'value', 'label' => 'Value', 'type' => 'text', 'required' => true],
            ],
        ],
    ];
}

function entity_config(string $name): ?array
{
    $entities = admin_entities();
    return $entities[$name] ?? null;
}

function entity_rows(string $name): array
{
    $config = entity_config($name);
    if (!$config) {
        return [];
    }

    return fetch_all($config['list_sql']);
}

function category_options(): array
{
    $rows = fetch_all('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id DESC');
    $options = [];
    foreach ($rows as $row) {
        $options[(string) $row['id']] = $row['name'];
    }
    return $options;
}
