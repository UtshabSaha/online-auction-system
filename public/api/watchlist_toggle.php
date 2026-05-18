<?php
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/functions.php';

require_role('buyer');
$message = watchlist_toggle($conn, current_user_id(), (int)($_POST['listing_id'] ?? 0));
json_response(['success' => true, 'message' => $message]);
?>
