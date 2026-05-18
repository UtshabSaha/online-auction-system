<<<<<<< HEAD
<<<<<<< HEAD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Auction System</title>
    <link rel="stylesheet" href="assets/css/style.css">

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
<aside class="sidebar">
    <strong><?= e($_SESSION['user_name']) ?></strong>
    <span class="badge"><?= e(current_role()) ?></span>
    <?php if (current_role()==='buyer'): ?>
        <a href="index.php?page=buyer_dashboard">Buyer Dashboard</a>
        <a href="index.php?page=watchlist">Watchlist</a>
        <a href="index.php?page=my_bids">My Bids</a>
        <a href="index.php?page=won_auctions">Won Auctions</a>
        <a href="index.php?page=spending">Spending</a>
    <?php endif; ?>
    <?php if (current_role()==='seller'): ?>
        <a href="index.php?page=seller_dashboard">Seller Dashboard</a>
        <a href="index.php?page=create_listing">Create Listing</a>
        <a href="index.php?page=seller_listings">My Listings</a>
        <a href="index.php?page=seller_templates">Templates</a>
        <a href="index.php?page=seller_ended">Ended Auctions</a>
        <a href="index.php?page=seller_reviews">Reviews</a>
        <a href="index.php?page=seller_analytics">Analytics</a>
    <?php endif; ?>
    <?php if (current_role()==='moderator'): ?>
        <a href="index.php?page=moderator_dashboard">Moderator</a>
        <a href="index.php?page=pending_listings">Pending Listings</a>
        <a href="index.php?page=listing_reports">Listing Reports</a>
        <a href="index.php?page=user_reports">User Reports</a>
        <a href="index.php?page=categories">Categories</a>
    <?php endif; ?>
    <?php if (current_role()==='admin'): ?>
        <a href="index.php?page=admin_dashboard">Admin</a>
        <a href="index.php?page=seller_verifications">Seller Requests</a>
        <a href="index.php?page=admin_users">Users</a>
        <a href="index.php?page=admin_listings">Listings</a>
        <a href="index.php?page=commissions">Commissions</a>
    <?php endif; ?>
</aside>
<section class="content">
<?php else: ?>
<section class="content full">
<?php endif; ?>
=======
=======
<?php $current_page = $_GET['page'] ?? 'home'; ?>
>>>>>>> origin/moderator/features
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Auction System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<<<<<<< HEAD

=======
>>>>>>> origin/moderator/features
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
    echo "<a href=\"index.php?page={$page}\" class=\"{$active}\">{$label}</a>\n";
} ?>
<aside class="sidebar">
    <strong><?= e($_SESSION['user_name']) ?></strong>
    <span class="badge"><?= e(current_role()) ?></span>
    <?php if (current_role()==='buyer'): ?>
<<<<<<< HEAD
        <a href="index.php?page=buyer_dashboard">Buyer Dashboard</a>
        <a href="index.php?page=watchlist">Watchlist</a>
        <a href="index.php?page=my_bids">My Bids</a>
        <a href="index.php?page=spending">Spending</a>
=======
        <?php nav_link('buyer_dashboard', 'Buyer Dashboard', $current_page); ?>
        <?php nav_link('watchlist', 'Watchlist', $current_page); ?>
        <?php nav_link('my_bids', 'My Bids', $current_page); ?>
        <?php nav_link('won_auctions', 'Won Auctions', $current_page); ?>
        <?php nav_link('spending', 'Spending', $current_page); ?>
>>>>>>> origin/moderator/features
    <?php endif; ?>
    <?php if (current_role()==='seller'): ?>
        <?php nav_link('seller_dashboard', 'Seller Dashboard', $current_page); ?>
        <?php nav_link('create_listing', 'Create Listing', $current_page); ?>
        <?php nav_link('seller_listings', 'My Listings', $current_page); ?>
        <?php nav_link('seller_ended', 'Ended Auctions', $current_page); ?>
        <?php nav_link('seller_templates', 'Templates', $current_page); ?>
        <?php nav_link('seller_analytics', 'Analytics', $current_page); ?>
        <?php nav_link('seller_reviews', 'My Reviews', $current_page); ?>
    <?php endif; ?>
    <?php if (current_role()==='moderator'): ?>
        <?php nav_link('moderator_dashboard', 'Moderator Dashboard', $current_page); ?>
        <?php nav_link('pending_listings', 'Pending Listings', $current_page); ?>
        <?php nav_link('listing_reports', 'Listing Reports', $current_page); ?>
        <?php nav_link('user_reports', 'User Reports', $current_page); ?>
        <?php nav_link('warnings', 'Warnings', $current_page); ?>
        <?php nav_link('categories', 'Categories', $current_page); ?>
        <?php nav_link('moderation_report', 'Activity Report', $current_page); ?>
        <?php nav_link('trust_score', 'Trust Scores', $current_page); ?>
    <?php endif; ?>
    <?php if (current_role()==='admin'): ?>
<<<<<<< HEAD
        <a href="index.php?page=admin_dashboard">Admin Dashboard</a>
        <a href="index.php?page=seller_verifications">Seller Requests</a>
        <a href="index.php?page=admin_users">Users</a>
        <a href="index.php?page=admin_listings">Listings</a>
        <a href="index.php?page=commissions">Commissions</a>
        <a href="index.php?page=financial_reports">Financial Reports</a>
        <a href="index.php?page=platform_analytics">Platform Analytics</a>
        <a href="index.php?page=featured">Featured Listings</a>
        <a href="index.php?page=announcements">Announcements</a>
=======
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
            <span style="flex:1">Dark Mode</span>
            <span class="toggle-track"><span class="toggle-thumb"></span></span>
        </button>
>>>>>>> origin/moderator/features
    <?php endif; ?>
</aside>
<section class="content">
<?php else: ?>
<section class="content full">
<<<<<<< HEAD
<?php endif; ?>
>>>>>>> origin/admin/features
=======
<?php endif; ?>
>>>>>>> origin/moderator/features
