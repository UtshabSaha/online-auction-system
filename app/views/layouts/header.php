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
    <a href="index.php?page=buyer_dashboard">Buyer Dashboard</a>
    <a href="index.php?page=watchlist">Watchlist</a>
    <a href="index.php?page=my_bids">My Bids</a>
    <a href="index.php?page=spending">Spending</a>
    <?php if (current_role()==='seller'): ?>
        <a href="index.php?page=seller_dashboard">Seller Dashboard</a>
        <a href="index.php?page=create_listing">Create Listing</a>
        <a href="index.php?page=seller_listings">My Listings</a>
        <a href="index.php?page=seller_templates">Templates</a>
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
