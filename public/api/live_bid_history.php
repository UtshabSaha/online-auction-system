<?php
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/functions.php';

require_role(['buyer', 'seller', 'moderator', 'admin']);
$listingId = (int)($_GET['listing_id'] ?? 0);
$listing = listing_find($conn, $listingId);
$history = bid_history($conn, $listingId);
json_response(['success' => true, 'current_bid' => $listing['current_bid'] ?? 0, 'bid_count' => count($history), 'data' => $history]);
?>
