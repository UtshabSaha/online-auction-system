<?php
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/functions.php';

require_role('buyer');
json_response(['success' => true, 'data' => buyer_notifications($conn, current_user_id())]);
?>
