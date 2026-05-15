<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/BuyerController.php';
require_once __DIR__ . '/app/controllers/SellerController.php';
require_once __DIR__ . '/app/controllers/ModeratorController.php';
require_once __DIR__ . '/app/controllers/AdminController.php';

$page = $_GET['page'] ?? 'home';

$auth = new AuthController($conn);
$buyer = new BuyerController($conn);
$seller = new SellerController($conn);
$moderator = new ModeratorController($conn);
$admin = new AdminController($conn);

switch ($page) {
    case 'home': $buyer->browse(); break;
    case 'login': $auth->login(); break;
    case 'register': $auth->register(); break;
    case 'logout': $auth->logout(); break;
    case 'profile': $auth->profile(); break;

    case 'browse': $buyer->browse(); break;
    case 'auction': $buyer->auction(); break;
    case 'buyer_dashboard': $buyer->dashboard(); break;
    case 'watchlist': $buyer->watchlist(); break;
    case 'my_bids': $buyer->myBids(); break;
    case 'won_auctions': $buyer->wonAuctions(); break;
    case 'spending': $buyer->spending(); break;
    case 'review_seller': $buyer->reviewSeller(); break;
    case 'report_listing': $buyer->reportListing(); break;
    case 'report_user': $buyer->reportUser(); break;

    case 'seller_dashboard': $seller->dashboard(); break;
    case 'seller_verification': $seller->verification(); break;
    case 'create_listing': $seller->createListing(); break;
    case 'seller_listings': $seller->listings(); break;
    case 'edit_listing': $seller->editListing(); break;
    case 'seller_templates': $seller->templates(); break;
    case 'seller_ended': $seller->ended(); break;
    case 'seller_analytics': $seller->analytics(); break;
    case 'seller_reviews': $seller->reviews(); break;
    case 'relist': $seller->relist(); break;

    case 'moderator_dashboard': $moderator->dashboard(); break;
    case 'pending_listings': $moderator->pendingListings(); break;
    case 'listing_reports': $moderator->listingReports(); break;
    case 'user_reports': $moderator->userReports(); break;
    case 'warnings': $moderator->warnings(); break;
    case 'categories': $moderator->categories(); break;
    case 'moderation_report': $moderator->activityReport(); break;
    case 'trust_score': $moderator->trustScore(); break;

    case 'admin_dashboard': $admin->dashboard(); break;
    case 'seller_verifications': $admin->sellerVerifications(); break;
    case 'admin_users': $admin->users(); break;
    case 'admin_listings': $admin->listings(); break;
    case 'commissions': $admin->commissions(); break;
    case 'financial_reports': $admin->financialReports(); break;
    case 'platform_analytics': $admin->analytics(); break;
    case 'featured': $admin->featured(); break;
    case 'announcements': $admin->announcements(); break;
    default: http_response_code(404); echo 'Page not found';
}
?>
