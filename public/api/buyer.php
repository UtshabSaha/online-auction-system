<?php
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/functions.php';

$action = $_GET['action'] ?? '';

if ($action === 'search') {
    listing_close_expired_auctions($conn);
    $rows = listing_search($conn, $_GET['q'] ?? '', $_GET['category'] ?? '', $_GET['condition'] ?? '', $_GET['min'] ?? '', $_GET['max'] ?? '', $_GET['time'] ?? '');
    $html = '';
    foreach ($rows as $l) {
        $img = e($l['image_path'] ?: 'assets/images/no-image.png');
        $html .= '<div class="card auction-card"><img src="' . $img . '"><h3>' . e($l['title']) . '</h3><p>' . e($l['category_name']) . ' · ' . e($l['condition']) . '</p><p><strong>Current bid:</strong> ' . e($l['current_bid']) . '</p><p><strong>Ends:</strong> ' . e($l['end_datetime']) . '</p><a class="btn" href="index.php?page=auction&id=' . $l['id'] . '">View Auction</a></div>';
    }
    json_response(['success' => true, 'html' => $html]);
}

require_login();

if ($action === 'place_bid') {
    $amount = (float)($_POST['amount'] ?? 0);
    json_response(bid_place($conn, (int)$_POST['listing_id'], current_user_id(), $amount, 0));
}

if ($action === 'auto_bid') {
    json_response(bid_set_auto($conn, (int)$_POST['listing_id'], current_user_id(), (float)$_POST['max_amount']));
}

if ($action === 'bid_history') {
    $listingId = (int)($_GET['listing_id'] ?? 0);
    $listing = listing_find($conn, $listingId);
    json_response(['success' => true, 'current_bid' => $listing['current_bid'] ?? 0, 'bids' => bid_history($conn, $listingId)]);
}

if ($action === 'watchlist') {
    json_response(['success' => true, 'message' => watchlist_toggle($conn, current_user_id(), (int)$_POST['listing_id'])]);
}

json_response(['success' => false, 'message' => 'Unknown action']);
?>
