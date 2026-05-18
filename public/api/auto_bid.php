<?php
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/functions.php';

require_role('buyer');
json_response(bid_set_auto($conn, (int)($_POST['listing_id'] ?? 0), current_user_id(), (float)($_POST['max_amount'] ?? 0)));
?>
