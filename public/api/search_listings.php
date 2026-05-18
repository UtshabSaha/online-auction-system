<?php
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/functions.php';

listing_close_expired_auctions($conn);
$rows = listing_search($conn, $_GET['q'] ?? '', $_GET['category'] ?? '', $_GET['condition'] ?? '', $_GET['min'] ?? '', $_GET['max'] ?? '', $_GET['time'] ?? '');
json_response(['success' => true, 'data' => $rows]);
?>
