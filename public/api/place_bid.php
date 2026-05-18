<?php
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/functions.php';

require_role('buyer');
$amount = (float)($_POST['amount'] ?? 0);
json_response(bid_place($conn, (int)($_POST['listing_id'] ?? 0), current_user_id(), $amount, 0));
?>
