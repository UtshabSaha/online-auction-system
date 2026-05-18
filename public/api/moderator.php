<<<<<<< HEAD
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
=======
<?php
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/functions.php';

require_role('moderator');
$action = $_GET['action'] ?? '';

// ── Listing Status ────────────────────────────────────────────────────────────
if ($action === 'approve_listing') {
    $id = (int)($_POST['listing_id'] ?? 0);
    if ($id <= 0) json_response(['success' => false, 'message' => 'Invalid listing ID']);
    $ok = moderator_approve_listing($conn, $id);
    json_response(['success' => $ok, 'message' => $ok ? 'Listing approved and set to active.' : 'Approval failed — listing may not be in pending_review status.']);
}

if ($action === 'reject_listing') {
    $id = (int)($_POST['listing_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    if ($id <= 0) json_response(['success' => false, 'message' => 'Invalid listing ID']);
    if ($reason === '') json_response(['success' => false, 'message' => 'A rejection reason is required.']);
    $ok = moderator_reject_listing($conn, $id, $reason);
    json_response(['success' => $ok, 'message' => $ok ? 'Listing rejected.' : 'Rejection failed.']);
}

if ($action === 'suspend_listing') {
    $id = (int)($_POST['listing_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    if ($id <= 0) json_response(['success' => false, 'message' => 'Invalid listing ID']);
    if ($reason === '') json_response(['success' => false, 'message' => 'A suspension reason is required.']);
    $ok = moderator_suspend_listing($conn, $id, $reason);
    json_response(['success' => $ok, 'message' => $ok ? 'Listing suspended and moved to pending_review.' : 'Suspension failed — listing may not be active.']);
}

// legacy: keep old listing_status action for backward compatibility
if ($action === 'listing_status') {
    $id = (int)($_POST['listing_id'] ?? 0);
    $status = $_POST['status'] ?? '';
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

// ── Report Actions ────────────────────────────────────────────────────────────
if ($action === 'resolve_listing_report') {
    $report_id = (int)($_POST['report_id'] ?? 0);
    $act = $_POST['act'] ?? '';
    $note = trim($_POST['note'] ?? '');
    if ($act === 'dismiss') {
        report_update_listing($conn, $report_id, 'dismissed', $note);
        json_response(['success' => true, 'message' => 'Report dismissed.']);
    } elseif ($act === 'suspend') {
        $report = db_row($conn, 'SELECT listing_id FROM listing_reports WHERE id=?', 'i', [$report_id]);
        if ($report) {
            moderator_suspend_listing($conn, (int)$report['listing_id'], $note ?: 'Suspended following a report.');
            report_update_listing($conn, $report_id, 'resolved', $note);
            json_response(['success' => true, 'message' => 'Listing suspended and report resolved.']);
        }
    } elseif ($act === 'warn_seller') {
        if ($note === '') json_response(['success' => false, 'message' => 'Warning reason is required.']);
        $report = db_row($conn, 'SELECT lr.listing_id, l.seller_id FROM listing_reports lr JOIN listings l ON l.id=lr.listing_id WHERE lr.id=?', 'i', [$report_id]);
        if ($report) {
            moderator_warn($conn, (int)$report['seller_id'], current_user_id(), $note);
            report_update_listing($conn, $report_id, 'resolved', $note);
            json_response(['success' => true, 'message' => 'Warning issued and report resolved.']);
        }
    }
    json_response(['success' => false, 'message' => 'Invalid action.']);
}

if ($action === 'resolve_user_report') {
    $report_id = (int)($_POST['report_id'] ?? 0);
    $reported_user_id = (int)($_POST['reported_user_id'] ?? 0);
    $act = $_POST['act'] ?? '';
    $note = trim($_POST['note'] ?? '');
    if ($act === 'dismiss') {
        report_update_user($conn, $report_id, 'dismissed', $note);
        json_response(['success' => true, 'message' => 'Report dismissed.']);
    } elseif ($act === 'warn') {
        if ($note === '') json_response(['success' => false, 'message' => 'Warning reason is required.']);
        moderator_warn($conn, $reported_user_id, current_user_id(), $note);
        report_update_user($conn, $report_id, 'resolved', $note);
        json_response(['success' => true, 'message' => 'Warning issued and report resolved.']);
    } elseif ($act === 'escalate') {
        report_update_user($conn, $report_id, 'escalated', $note ?: 'Escalated to admin for suspension review.');
        json_response(['success' => true, 'message' => 'Escalated to admin.']);
    }
    json_response(['success' => false, 'message' => 'Invalid action.']);
}

// ── Warning ───────────────────────────────────────────────────────────────────
if ($action === 'issue_warning') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    if ($user_id <= 0 || $reason === '') json_response(['success' => false, 'message' => 'User ID and reason are required.']);
    $target = user_find($conn, $user_id);
    if (!$target || !in_array($target['role'], ['buyer', 'seller'], true)) {
        json_response(['success' => false, 'message' => 'Only buyers and sellers can receive warnings.']);
    }
    $ok = moderator_warn($conn, $user_id, current_user_id(), $reason);
    json_response(['success' => $ok, 'message' => $ok ? 'Warning issued.' : 'Failed to issue warning.']);
}

// ── Categories ────────────────────────────────────────────────────────────────
if ($action === 'add_category') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $parent = (int)($_POST['parent_id'] ?? 0) ?: null;
    if ($name === '') json_response(['success' => false, 'message' => 'Category name is required.']);
    $ok = moderator_add_category($conn, $name, $desc, $parent);
    json_response(['success' => $ok, 'message' => $ok ? 'Category added.' : 'Failed.']);
}

if ($action === 'rename_category') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if ($id <= 0 || $name === '') json_response(['success' => false, 'message' => 'ID and name are required.']);
    $ok = moderator_rename_category($conn, $id, $name, $desc);
    json_response(['success' => $ok, 'message' => $ok ? 'Category renamed.' : 'Failed.']);
}

if ($action === 'merge_categories') {
    $src = (int)($_POST['source_id'] ?? 0);
    $dst = (int)($_POST['dest_id'] ?? 0);
    if ($src <= 0 || $dst <= 0 || $src === $dst) json_response(['success' => false, 'message' => 'Select two different valid categories.']);
    $ok = moderator_merge_category($conn, $src, $dst);
    json_response(['success' => $ok, 'message' => $ok ? 'Categories merged.' : 'Failed.']);
}

if ($action === 'delete_category') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) json_response(['success' => false, 'message' => 'Invalid category ID.']);
    $ok = moderator_delete_empty_category($conn, $id);
    json_response(['success' => $ok, 'message' => $ok ? 'Category deleted.' : 'Could not delete — it may still have listings.']);
}

// ── Keywords ──────────────────────────────────────────────────────────────────
if ($action === 'add_keyword') {
    $kw = trim($_POST['keyword'] ?? '');
    if ($kw === '') json_response(['success' => false, 'message' => 'Keyword cannot be empty.']);
    $ok = moderator_add_keyword($conn, $kw);
    json_response(['success' => $ok, 'message' => $ok ? 'Keyword added.' : 'Failed (may already exist).']);
}

if ($action === 'delete_keyword') {
    $id = (int)($_POST['keyword_id'] ?? 0);
    if ($id <= 0) json_response(['success' => false, 'message' => 'Invalid keyword ID.']);
    $ok = moderator_delete_keyword($conn, $id);
    json_response(['success' => $ok, 'message' => $ok ? 'Keyword removed.' : 'Failed.']);
}

// ── Messaging ─────────────────────────────────────────────────────────────────
if ($action === 'send_message') {
    $receiver_id = (int)($_POST['receiver_id'] ?? 0);
    $listing_id = (int)($_POST['listing_id'] ?? 0) ?: null;
    $msg = trim($_POST['message'] ?? '');
    if ($receiver_id <= 0 || $msg === '') json_response(['success' => false, 'message' => 'Recipient and message are required.']);
    $target = user_find($conn, $receiver_id);
    if (!$target || !in_array($target['role'], ['buyer', 'seller'], true)) {
        json_response(['success' => false, 'message' => 'Messages can only be sent to buyers and sellers.']);
    }
    $ok = moderator_send_message($conn, current_user_id(), $receiver_id, $listing_id, $msg);
    json_response(['success' => $ok, 'message' => $ok ? 'Message sent.' : 'Failed to send message.']);
}

json_response(['success' => false, 'message' => 'Unknown action']);
?>
>>>>>>> origin/moderator/features
