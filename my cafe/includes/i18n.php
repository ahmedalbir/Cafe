<?php
declare(strict_types=1);

function locale(): string
{
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'bn'], true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }

    if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], ['en', 'bn'], true)) {
        return (string) $_SESSION['lang'];
    }

    return 'en';
}

function is_bn(): bool
{
    return locale() === 'bn';
}

function t(string $key): string
{
    static $strings = [
        'Home' => ['bn' => 'হোম'],
        'About' => ['bn' => 'আমাদের সম্পর্কে'],
        'Menu' => ['bn' => 'মেনু'],
        'Services' => ['bn' => 'সার্ভিস'],
        'Gallery' => ['bn' => 'গ্যালারি'],
        'Booking' => ['bn' => 'বুকিং'],
        'Offers' => ['bn' => 'অফার'],
        'Reviews' => ['bn' => 'রিভিউ'],
        'FAQ' => ['bn' => 'প্রশ্নোত্তর'],
        'Contact' => ['bn' => 'যোগাযোগ'],
        'Admin' => ['bn' => 'অ্যাডমিন'],
        'Welcome to' => ['bn' => 'স্বাগতম'],
        'Order Now' => ['bn' => 'এখনই অর্ডার করুন'],
        'Book a Table' => ['bn' => 'টেবিল বুক করুন'],
        'View Cart' => ['bn' => 'কার্ট দেখুন'],
        'Add to Cart' => ['bn' => 'কার্টে যোগ করুন'],
        'Reserve Now' => ['bn' => 'এখনই বুক করুন'],
        'Send Message' => ['bn' => 'বার্তা পাঠান'],
        'Submit Request' => ['bn' => 'অনুরোধ পাঠান'],
        'Apply Online' => ['bn' => 'অনলাইনে আবেদন করুন'],
        'Place Order' => ['bn' => 'অর্ডার করুন'],
        'Clear Cart' => ['bn' => 'কার্ট খালি করুন'],
        'Featured Products' => ['bn' => 'ফিচার্ড প্রোডাক্ট'],
        'Popular picks from D Cafe' => ['bn' => 'D Cafe-এর জনপ্রিয় পছন্দ'],
        'All items by category' => ['bn' => 'ক্যাটাগরি অনুযায়ী সব আইটেম'],
        'Everything your cafe customers need' => ['bn' => 'আপনার cafe customers-এর প্রয়োজনীয় সবকিছু'],
        'Interior, food, coffee, and moments' => ['bn' => 'ইন্টেরিয়র, খাবার, কফি, আর সুন্দর মুহূর্ত'],
        'Table booking' => ['bn' => 'টেবিল বুকিং'],
        'Discount coupons, happy hour, combo offers' => ['bn' => 'ডিসকাউন্ট কুপন, happy hour, কম্বো অফার'],
        'Customer reviews and success stories' => ['bn' => 'গ্রাহকের রিভিউ ও সফলতার গল্প'],
        'Frequently asked questions' => ['bn' => 'সচরাচর জিজ্ঞাসিত প্রশ্ন'],
        'Get in touch' => ['bn' => 'যোগাযোগ করুন'],
        'Support, complaint, feedback forms' => ['bn' => 'সাপোর্ট, অভিযোগ, ফিডব্যাক ফর্ম'],
        'Coffee tips, new products, events, and food blogs' => ['bn' => 'কফি টিপস, নতুন প্রোডাক্ট, ইভেন্ট, আর খাবারের ব্লগ'],
        'Join the D Cafe team' => ['bn' => 'D Cafe টিমে যোগ দিন'],
        'Shopping Cart' => ['bn' => 'শপিং কার্ট'],
        'Your selected items' => ['bn' => 'আপনার নির্বাচিত আইটেম'],
        'Summary' => ['bn' => 'সারাংশ'],
        'Quick Links' => ['bn' => 'দ্রুত লিংক'],
        'Opening Hours' => ['bn' => 'খোলার সময়'],
        'Social Media' => ['bn' => 'সোশ্যাল মিডিয়া'],
        'Where Every Cup Tells a Story' => ['bn' => 'প্রতিটি কাপ একটি গল্প বলে'],
        'Fresh coffee, warm moments' => ['bn' => 'সুস্বাদু কফি, উষ্ণ মুহূর্ত'],
        'Current' => ['bn' => 'বর্তমান'],
        'Name' => ['bn' => 'নাম'],
        'Phone' => ['bn' => 'ফোন'],
        'Email' => ['bn' => 'ইমেইল'],
        'Subject' => ['bn' => 'বিষয়'],
        'Message' => ['bn' => 'বার্তা'],
        'Special Request' => ['bn' => 'বিশেষ অনুরোধ'],
        'Number of Guests' => ['bn' => 'অতিথির সংখ্যা'],
        'Delivery address' => ['bn' => 'ডেলিভারি ঠিকানা'],
        'Customer name' => ['bn' => 'গ্রাহকের নাম'],
        'Newsletter email' => ['bn' => 'নিউজলেটার ইমেইল'],
        'Select a job' => ['bn' => 'একটি চাকরি নির্বাচন করুন'],
        'Why do you want to join?' => ['bn' => 'আপনি কেন যোগ দিতে চান?'],
    ];

    if (!isset($strings[$key])) {
        return $key;
    }

    return $strings[$key][locale()] ?? $key;
}

function lang_toggle_href(string $target = ''): string
{
    $lang = locale() === 'en' ? 'bn' : 'en';
    $path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
    $anchor = $target !== '' ? '#' . ltrim($target, '#') : '';
    return $path . '?lang=' . $lang . $anchor;
}
