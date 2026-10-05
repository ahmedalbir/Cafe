<?php
declare(strict_types=1);

function site_services(): array
{
    return [
        ['icon' => 'fa-solid fa-bell-concierge', 'title' => 'Dine In', 'text' => 'Relax and enjoy freshly prepared food and drinks.'],
        ['icon' => 'fa-solid fa-bag-shopping', 'title' => 'Take Away', 'text' => 'Quick pickup when you are in a hurry.'],
        ['icon' => 'fa-solid fa-motorcycle', 'title' => 'Home Delivery', 'text' => 'Delivered warm and fresh to your doorstep.'],
        ['icon' => 'fa-solid fa-laptop', 'title' => 'Online Ordering', 'text' => 'Simple ordering flow for your convenience.'],
        ['icon' => 'fa-solid fa-bullhorn', 'title' => 'Event Catering', 'text' => 'Coffee and snack service for events and gatherings.'],
        ['icon' => 'fa-solid fa-cake-candles', 'title' => 'Birthday Party', 'text' => 'Celebrate with custom packages and decor support.'],
        ['icon' => 'fa-solid fa-briefcase', 'title' => 'Corporate Space', 'text' => 'Comfortable meeting space for team discussions.'],
    ];
}

function site_faqs(): array
{
    return [
        ['q' => 'Opening Hours?', 'a' => 'We are open every day from 8:00 AM to 11:00 PM.'],
        ['q' => 'Delivery Available?', 'a' => 'Yes, home delivery is available within selected areas.'],
        ['q' => 'Online Payment?', 'a' => 'Yes, cash and online payment options are supported.'],
        ['q' => 'Reservation Required?', 'a' => 'Not required, but recommended for groups and events.'],
    ];
}

function social_links(): array
{
    return [
        ['name' => 'Facebook', 'icon' => 'fa-brands fa-facebook-f', 'url' => 'https://facebook.com'],
        ['name' => 'Instagram', 'icon' => 'fa-brands fa-instagram', 'url' => 'https://instagram.com'],
        ['name' => 'WhatsApp', 'icon' => 'fa-brands fa-whatsapp', 'url' => 'https://wa.me/880000000000'],
        ['name' => 'TikTok', 'icon' => 'fa-brands fa-tiktok', 'url' => 'https://tiktok.com'],
        ['name' => 'YouTube', 'icon' => 'fa-brands fa-youtube', 'url' => 'https://youtube.com'],
        ['name' => 'X', 'icon' => 'fa-brands fa-x-twitter', 'url' => 'https://x.com'],
        ['name' => 'LinkedIn', 'icon' => 'fa-brands fa-linkedin-in', 'url' => 'https://linkedin.com'],
    ];
}

function category_icon_map(): array
{
    return [
        'Coffee' => 'fa-solid fa-mug-hot',
        'Cold Drinks' => 'fa-solid fa-glass-water',
        'Tea' => 'fa-solid fa-mug-hot',
        'Snacks' => 'fa-solid fa-burger',
        'Desserts' => 'fa-solid fa-cake-candles',
    ];
}
