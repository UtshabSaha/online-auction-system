<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/app/controllers/functions.php';

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'home': buyer_browse($conn); break;
    case 'login': auth_login($conn); break;
    case 'register': auth_register($conn); break;
    case 'logout': auth_logout(); break;
    case 'profile': auth_profile($conn); break;

    case 'browse': buyer_browse($conn); break;
    case 'auction': buyer_auction($conn); break;
    case 'buyer_dashboard': buyer_dashboard($conn); break;
    case 'watchlist': buyer_watchlist($conn); break;
    case 'my_bids': buyer_my_bids($conn); break;
    case 'won_auctions': buyer_won_auctions($conn); break;
    case 'spending': buyer_spending($conn); break;
    case 'review_seller': buyer_review_seller($conn); break;
    case 'report_listing': buyer_report_listing($conn); break;
    case 'report_user': buyer_report_user($conn); break;

    case 'seller_dashboard': seller_dashboard($conn); break;
    case 'seller_verification': seller_verification($conn); break;
    case 'create_listing': seller_create_listing($conn); break;
    case 'seller_listings': seller_listings($conn); break;
    case 'edit_listing': seller_edit_listing($conn); break;
    case 'seller_templates': seller_templates_page($conn); break;
    case 'seller_ended': seller_ended_page($conn); break;
    case 'seller_analytics': seller_analytics_page($conn); break;
    case 'seller_reviews': seller_reviews($conn); break;
    case 'relist': seller_relist(); break;

    case 'moderator_dashboard': moderator_dashboard_page($conn); break;
    case 'pending_listings': moderator_pending_listings($conn); break;
    case 'listing_reports': moderator_listing_reports($conn); break;
    case 'user_reports': moderator_user_reports($conn); break;
    case 'warnings': moderator_warnings_page($conn); break;
    case 'categories': moderator_categories($conn); break;
    case 'moderation_report': moderator_activity_report($conn); break;
    case 'trust_score': moderator_trust_score($conn); break;

    case 'admin_dashboard': admin_dashboard($conn); break;
    case 'seller_verifications': admin_seller_verifications($conn); break;
    case 'admin_users': admin_users($conn); break;
    case 'admin_listings': admin_listings($conn); break;
    case 'commissions': admin_commissions($conn); break;
    case 'financial_reports': admin_financial_reports($conn); break;
    case 'platform_analytics': admin_analytics($conn); break;
    case 'featured': admin_featured($conn); break;
    case 'announcements': admin_announcements_page($conn); break;
    default: http_response_code(404); echo 'Page not found';
}
?>
