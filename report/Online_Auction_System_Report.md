# Online Auction System Report

## Project Overview

The Online Auction System is a PHP and MySQL web application where sellers can list items for timed auctions and buyers can place bids. Moderators review listings and reports, while admins manage users, seller verification, commission settings, and platform reports.

The project follows an MVC-style structure with separate folders for controllers, models, and views. Server-side logic is written in PHP, database access uses mysqli prepared statements, and protected pages use PHP sessions with role-based access control.

## Technologies Used

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- AJAX with XMLHttpRequest
- mysqli prepared statements
- XAMPP Apache server

## Database Design

The shared database includes the required auction platform tables:

- users
- categories
- listings
- listing_images
- bids
- auto_bids
- watchlist
- seller_verification_requests
- reviews
- listing_reports
- user_reports
- warnings
- auction_templates
- platform_fees

Additional useful tables are also included for commission rates, announcements, and platform messages.

## Authentication and Security

The system uses PHP sessions for login management. Passwords are stored using PHP password hashing. Each protected page checks the logged-in user's role before allowing access. User input is validated on the server side, and database queries are written with prepared statements.

## Buyer Role Features

The buyer can register, log in, browse active auctions, search auctions, view auction details, place manual bids, set automatic bids, manage a watchlist, view personal bids, check won auctions, submit seller reviews, report listings, report users, and view spending history.

The buyer AJAX feature is used for auction search, bid placement, bid history refresh, and watchlist actions.

## Seller Role Features

The seller can submit a verification request, manage profile information, create auction listings, upload listing images, manage active listings, edit zero-bid listings, cancel eligible listings, use saved auction templates, view ended auctions, relist unsold items, review winning buyers, and view sales analytics.

Listings created by sellers first go to pending review before becoming active.

## Moderator Role Features

The moderator can view a dashboard, review pending listings, approve or reject listings, manage listing reports, manage user reports, issue official warnings, manage categories, view moderation activity reports, and check user trust score information.

The moderator AJAX feature supports listing status updates.

## Admin Role Features

The admin can view platform statistics, manage seller verification requests, manage all user accounts, deactivate and reactivate users, promote or demote moderators, manage all listings, set commission rates, view financial reports, view platform analytics, manage featured listings, and post announcements.

The admin AJAX feature supports user search.

## Separation of Concerns

The project separates application responsibilities:

- Controllers handle request flow.
- Models handle database queries.
- Views handle HTML display.
- API files return JSON for AJAX requests.
- Configuration files keep database connection settings separate.

## How to Run

1. Copy the project folder to `C:\xampp\htdocs\online-auction-system`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin.
4. Import `database/auction_system.sql`.
5. Visit `http://localhost/online-auction-system/`.

## Demo Accounts

All demo accounts use the password `password`.

- Admin: `admin@example.com`
- Moderator: `moderator@example.com`
- Seller: `seller@example.com`
- Buyer: `buyer@example.com`

## Conclusion

This project implements the required online auction system in a simple, beginner-friendly way. It includes the required database schema, role-based dashboards, session authentication, prepared statements, server-side validation, and AJAX features for each major role.
