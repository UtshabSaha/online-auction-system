<?php $current_page = $_GET['page'] ?? 'home'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Auction System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<nav class="navbar">
    <a class="brand" href="index.php">Online Auction</a>
    <div class="navlinks">
        <a href="index.php?page=browse">Browse</a>
        <?php if (is_logged_in()): ?>
            <a href="index.php?page=<?= e(current_role()) ?>_dashboard">Dashboard</a>
            <a href="index.php?page=profile">Profile</a>
            <a href="index.php?page=logout">Logout</a>
        <?php else: ?>
            <a href="index.php?page=login">Login</a>
            <a href="index.php?page=register">Register</a>
        <?php endif; ?>
    </div>
</nav>
<main class="container">
<?php if (is_logged_in()): ?>
<?php function nav_link($page, $label, $current) {
    $active = ($current === $page) ? ' active' : '';
    echo '<a href="index.php?page=' . e($page) . '" class="' . e($active) . '">' . e($label) . '</a>' . "\n";
} ?>
<aside class="sidebar">
    <strong><?= e($_SESSION['user_name']) ?></strong>
    <span class="badge"><?= e(current_role()) ?></span>
    <?php if (current_role()==='buyer'): ?>
        <?php nav_link('buyer_dashboard', 'Buyer Dashboard', $current_page); ?>
        <?php nav_link('watchlist', 'Watchlist', $current_page); ?>
        <?php nav_link('my_bids', 'My Bids', $current_page); ?>
        <?php nav_link('won_auctions', 'Won Auctions', $current_page); ?>
        <?php nav_link('spending', 'Spending', $current_page); ?>
    <?php endif; ?>
    <?php if (current_role()==='seller'): ?>
        <?php nav_link('seller_dashboard', 'Seller Dashboard', $current_page); ?>
        <?php nav_link('create_listing', 'Create Listing', $current_page); ?>
        <?php nav_link('seller_listings', 'My Listings', $current_page); ?>
        <?php nav_link('seller_templates', 'Templates', $current_page); ?>
        <?php nav_link('seller_ended', 'Ended Auctions', $current_page); ?>
        <?php nav_link('seller_reviews', 'My Reviews', $current_page); ?>
        <?php nav_link('seller_analytics', 'Analytics', $current_page); ?>
    <?php endif; ?>
    <?php if (current_role()==='moderator'): ?>
        <?php nav_link('moderator_dashboard', 'Moderator Dashboard', $current_page); ?>
        <?php nav_link('pending_listings', 'Pending Listings', $current_page); ?>
        <?php nav_link('active_listings', 'Active Listings', $current_page); ?>
        <?php nav_link('listing_reports', 'Listing Reports', $current_page); ?>
        <?php nav_link('user_reports', 'User Reports', $current_page); ?>
        <?php nav_link('warnings', 'Warnings', $current_page); ?>
        <?php nav_link('categories', 'Categories', $current_page); ?>
        <?php nav_link('keyword_abuse', 'Keyword Abuse', $current_page); ?>
        <?php nav_link('mod_messaging', 'Messaging', $current_page); ?>
        <?php nav_link('moderation_report', 'Activity Report', $current_page); ?>
        <?php nav_link('trust_score', 'Trust Scores', $current_page); ?>
    <?php endif; ?>
    <?php if (current_role()==='admin'): ?>
        <?php nav_link('admin_dashboard', 'Admin Dashboard', $current_page); ?>
        <?php nav_link('seller_verifications', 'Seller Requests', $current_page); ?>
        <?php nav_link('admin_users', 'Users', $current_page); ?>
        <?php nav_link('admin_listings', 'Listings', $current_page); ?>
        <?php nav_link('commissions', 'Commissions', $current_page); ?>
        <?php nav_link('financial_reports', 'Financial Reports', $current_page); ?>
        <?php nav_link('platform_analytics', 'Platform Analytics', $current_page); ?>
        <?php nav_link('featured', 'Featured Listings', $current_page); ?>
        <?php nav_link('announcements', 'Announcements', $current_page); ?>
    <?php endif; ?>
    <?php if (current_role()==='admin' || current_role()==='moderator'): ?>
        <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Toggle dark mode">
            <i class="fa-solid fa-moon"></i>
            <span>Dark</span>
            <span class="toggle-track"><span class="toggle-thumb"></span></span>
        </button>
    <?php endif; ?>
</aside>
<section class="content">
<?php else: ?>
<section class="content full">
<?php endif; ?>
