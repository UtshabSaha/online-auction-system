# Online Auction System

A complete beginner-friendly PHP/MySQL MVC university project for XAMPP.

## Technology

- HTML
- CSS
- JavaScript
- PHP
- MySQL
- AJAX using XMLHttpRequest
- MVC Architecture
- mysqli prepared statements
- PHP sessions

## Folder Structure

```text
online-auction-system/
├── app/
│   ├── controllers/
│   ├── models/
│   └── views/
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── config/
├── database/
├── public/
│   └── api/
├── uploads/
│   ├── documents/
│   ├── listings/
│   └── profiles/
└── index.php
```

## Main Features

### Buyer

- Register, login, logout
- Browse active auctions
- Search and filter auctions using AJAX
- Auction details with bid history
- Manual bidding using AJAX
- Auto bidding
- Watchlist add/remove using AJAX
- My bids page with Leading/Outbid/Won/Lost status
- Won auctions page with seller contact
- Reviews
- Listing reports
- User reports
- Spending history

### Seller

- Seller verification request with document upload
- Create listings after admin approval
- Upload up to 5 listing images
- Manage listings
- Edit only zero-bid listings
- Listing templates
- Ended auction/winner information
- Seller analytics
- Reviews and review response

### Moderator

- Dashboard with moderation counts
- Review pending listings
- Approve/reject listings using AJAX
- Manage listing reports
- Manage user reports
- Issue warnings
- Category management
- Moderation activity report
- User trust score report

### Admin

- Dashboard with platform metrics
- Seller verification approval/rejection
- User search using AJAX
- Deactivate/reactivate users
- Promote/demote roles
- Manage all listings
- Commission settings
- Financial reports
- Platform analytics
- Featured listings
- Announcements

## Setup on XAMPP Windows

1. Copy the `online-auction-system` folder to:

```text
C:\xampp\htdocs\online-auction-system
```

2. Start XAMPP:

- Apache
- MySQL

3. Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

4. Import the SQL file:

```text
database/auction_system.sql
```

5. Open the project:

```text
http://localhost/online-auction-system/
```

## Default Login Accounts

All demo accounts use password:

```text
password
```

Accounts:

```text
admin@example.com       Admin
moderator@example.com   Moderator
seller@example.com      Verified Seller
buyer@example.com       Buyer
```

## Database Connection

Edit this file if your XAMPP MySQL credentials are different:

```text
config/db.php
```

Default:

```php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'auction_system';
```

## AJAX Endpoints

- `public/api/place_bid.php`
- `public/api/auto_bid.php`
- `public/api/search_listings.php`
- `public/api/watchlist_toggle.php`
- `public/api/live_bid_history.php`
- `public/api/get_notifications.php`
- `public/api/filter_listings.php`
- `public/api/moderator.php?action=listing_status`
- `public/api/admin.php?action=search_users`

## Security Implemented

- mysqli prepared statements in application code
- `password_hash()` for registration
- `password_verify()` for login
- PHP sessions
- `session_regenerate_id(true)` after login
- Role-based page protection
- Server-side validation
- File upload extension and size validation
- Output escaping with `htmlspecialchars()` helper

## Notes for University Submission

This is intentionally beginner-friendly. Some advanced production features such as payment gateway integration, real-time WebSocket bidding, email delivery, and full notification delivery are represented as in-dashboard pages/logic because the requirement asks for XAMPP PHP/MySQL and XMLHttpRequest AJAX.
