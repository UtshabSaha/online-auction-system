<?php
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/functions.php';

require_role('moderator');
$action = $_GET['action'] ?? '';

if ($action === 'listing_status') {
    $id = (int)$_POST['listing_id'];
    $status = $_POST['status'];
    $reason = trim($_POST['reason'] ?? '');

    if (!in_array($status, ['active', 'rejected', 'pending_review', 'cancelled'], true)) {
        json_response(['success' => false, 'message' => 'Invalid status']);
    }
    if (($status === 'rejected' || $status === 'cancelled') && $reason === '') {
        json_response(['success' => false, 'message' => 'Reason is required']);
    }

    listing_set_status($conn, $id, $status, $reason);
    json_response(['success' => true, 'message' => 'Listing status updated']);
}

json_response(['success' => false, 'message' => 'Unknown action']);
?>
