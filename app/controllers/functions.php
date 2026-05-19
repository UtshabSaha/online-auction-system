<?php
require_once __DIR__ . '/../models/functions.php';

function auth_login(mysqli $conn): void {
    $error = '';
    $old_email = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $old_email = $email;
        $password = $_POST['password'] ?? '';

        if ($email === '') {
            $error = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($password === '') {
            $error = 'Password is required.';
        } else {
            $user = user_find_by_email($conn, $email);
        }

        if (!$error && (!$user || !$user['is_active'] || !password_verify($password, $user['password_hash']))) {
            $error = 'Invalid login or inactive account.';
        } elseif (!$error) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['seller_verified'] = $user['seller_verified'];
            redirect_to('index.php?page=' . $user['role'] . '_dashboard');
        }
    }

    render_view('auth/login', compact('error', 'old_email'));
}

function auth_register(mysqli $conn): void {
    $error = '';
    $old = ['name' => '', 'email' => '', 'phone' => '', 'bio' => ''];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $old = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
        ];
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $errors = [];

        if ($old['name'] === '') { $errors[] = 'Name is required'; }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required'; }
        if (user_find_by_email($conn, $old['email'])) { $errors[] = 'Email already exists'; }
        if (strlen($password) < 8) { $errors[] = 'Password must be at least 8 characters'; }
        if ($password !== $confirm) { $errors[] = 'Passwords must match'; }

        if ($errors) {
            $error = format_errors($errors);
        } else {
            user_create($conn, $old['name'], $old['email'], password_hash($password, PASSWORD_DEFAULT), $old['phone'], $old['bio']);
            redirect_to('index.php?page=login');
        }
    }

    render_view('auth/register', compact('error', 'old'));
}

function auth_logout(): void {
    session_unset();
    session_destroy();
    redirect_to('index.php?page=login');
}

function auth_profile(mysqli $conn): void {
    require_login();
    $user = user_find($conn, current_user_id());
    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['change_password'])) {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($newPassword === '' || $confirmPassword === '') {
                $message = 'Both password fields are required.';
            } elseif ($newPassword !== $confirmPassword) {
                $message = 'New passwords and confirm passwords must be same.';
            } elseif (strlen($newPassword) < 8) {
                $message = 'Password must be at least 8 characters.';
            } else {
                user_change_password($conn, current_user_id(), password_hash($newPassword, PASSWORD_DEFAULT));
                $message = 'Password changed.';
            }
        } else {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $message = 'Name is required.';
            } else {
                $pic = $user['profile_pic'] ?? null;
                $uploaded = upload_file('profile_pic', 'profiles', ['jpg', 'jpeg', 'png', 'webp']);
                if ($uploaded) { $pic = $uploaded; }

                user_update_profile($conn, current_user_id(), $name, trim($_POST['phone'] ?? ''), trim($_POST['bio'] ?? ''), $pic);
                $message = 'Profile updated.';
                $user = user_find($conn, current_user_id());
            }
        }
    }

    $reviews = review_received($conn, current_user_id());
    render_view('auth/profile', compact('user', 'message', 'reviews'));
}

function buyer_browse(mysqli $conn): void {
    listing_close_expired_auctions($conn);
    $listings = listing_search($conn, $_GET['q'] ?? '', $_GET['category'] ?? '', $_GET['condition'] ?? '', $_GET['min'] ?? '', $_GET['max'] ?? '', $_GET['time'] ?? '');
    $categories = listing_categories($conn);
    render_view('public/browse', compact('listings', 'categories'));
}

function buyer_auction(mysqli $conn): void {
    $listing = listing_find($conn, (int)($_GET['id'] ?? 0));
    $images = listing_images($conn, $listing['id'] ?? 0);
    $bids = bid_history($conn, $listing['id'] ?? 0);
    render_view('public/auction', compact('listing', 'images', 'bids'));
}

function user_messages(mysqli $conn): void {
    require_login();
    $rows = user_inbox($conn, current_user_id());
    render_view('auth/messages', compact('rows'));
}

function buyer_dashboard(mysqli $conn): void {
    require_role('buyer');
    listing_close_expired_auctions($conn);
    $listings = listing_search($conn);
    $myBids = bid_by_buyer($conn, current_user_id());
    $notifications = buyer_notifications($conn, current_user_id());
    render_view('buyer/dashboard', compact('listings', 'myBids', 'notifications'));
}

function buyer_watchlist(mysqli $conn): void { require_role('buyer'); listing_close_expired_auctions($conn); $rows = watchlist_rows($conn, current_user_id()); render_view('buyer/watchlist', compact('rows')); }
function buyer_my_bids(mysqli $conn): void { require_role('buyer'); listing_close_expired_auctions($conn); $rows = bid_by_buyer($conn, current_user_id()); render_view('buyer/my_bids', compact('rows')); }
function buyer_won_auctions(mysqli $conn): void { require_role('buyer'); listing_close_expired_auctions($conn); $rows = won_auctions($conn, current_user_id()); render_view('buyer/won', compact('rows')); }
function buyer_spending(mysqli $conn): void { require_role('buyer'); listing_close_expired_auctions($conn); $stats = bid_spending($conn, current_user_id()); render_view('buyer/spending', compact('stats')); }

function buyer_review_seller(mysqli $conn): void {
    require_role('buyer');
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rating = (int)($_POST['rating'] ?? 0);
        $listingId = (int)($_POST['listing_id'] ?? 0);
        $sellerId = (int)($_POST['seller_id'] ?? 0);
        $text = trim($_POST['review_text'] ?? '');

        if ($rating < 1 || $rating > 5 || $text === '') {
            $message = 'Valid rating and text required.';
        } elseif (!buyer_can_review_listing($conn, $listingId, current_user_id(), $sellerId)) {
            $message = 'You can review only sellers from completed auctions you won, and only once per auction.';
        } else {
            review_create($conn, $listingId, current_user_id(), $sellerId, $rating, $text);
            $message = 'Review submitted.';
        }
    }
    $reviewable = reviewable_won_auctions($conn, current_user_id());
    $sentReviews = review_sent($conn, current_user_id());
    render_view('buyer/review_seller', compact('message', 'reviewable', 'sentReviews'));
}

function buyer_report_listing(mysqli $conn): void {
    require_role('buyer');
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $listingId = (int)($_POST['listing_id'] ?? 0);
        $listing = listing_find($conn, $listingId);
        if (!$listing) {
            $message = 'Listing not found.';
        } elseif (trim($_POST['reason'] ?? '') && strlen(trim($_POST['description'] ?? '')) >= 10) {
            report_create_listing($conn, $listingId, current_user_id(), trim($_POST['reason']), trim($_POST['description']));
            $message = 'Report submitted.';
        } else {
            $message = 'Reason and 10 character description required.';
        }
    }
    $listings = buyer_reportable_listings($conn);
    $selectedListingId = (int)($_GET['listing_id'] ?? 0);
    render_view('buyer/report_listing', compact('message', 'listings', 'selectedListingId'));
}

function buyer_report_user(mysqli $conn): void {
    require_role('buyer');
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reportedId = (int)($_POST['reported_user_id'] ?? 0);
        if ($reportedId === current_user_id()) {
            $message = 'You cannot report yourself.';
        } elseif (!user_find($conn, $reportedId)) {
            $message = 'User not found.';
        } elseif (trim($_POST['reason'] ?? '') && strlen(trim($_POST['description'] ?? '')) >= 10) {
            report_create_user($conn, current_user_id(), $reportedId, trim($_POST['reason']), trim($_POST['description']));
            $message = 'User report submitted.';
        } else {
            $message = 'Reason and 10 character description required.';
        }
    }
    $users = buyer_reportable_users($conn, current_user_id());
    $selectedUserId = (int)($_GET['user_id'] ?? 0);
    render_view('buyer/report_user', compact('message', 'users', 'selectedUserId'));
}

function listing_errors(array $p): array {
    $errors = [];
    if (trim($p['title'] ?? '') === '') { $errors[] = 'Title required'; }
    if (trim($p['description'] ?? '') === '') { $errors[] = 'Description required'; }
    if ((float)($p['starting_price'] ?? 0) <= 0) { $errors[] = 'Starting price must be positive'; }
    if (!empty($p['reserve_price']) && (float)$p['reserve_price'] < (float)$p['starting_price']) { $errors[] = 'Reserve cannot be lower than starting price'; }
    if (strtotime($p['end_datetime'] ?? '') < time() + 3600) { $errors[] = 'End date must be at least 1 hour in future'; }
    return $errors;
}

function seller_dashboard(mysqli $conn): void {
    require_role(['seller', 'buyer']);
    $listings = current_role() === 'seller' ? listing_by_seller($conn, current_user_id()) : [];
    $request = seller_request_by_user($conn, current_user_id());
    render_view('seller/dashboard', compact('listings', 'request'));
}

function seller_verification(mysqli $conn): void {
    require_login();
    $request = seller_request_by_user($conn, current_user_id());
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $doc = upload_file('id_document', 'documents', ['jpg', 'jpeg', 'png', 'pdf']);
        if (!$doc || trim($_POST['motivation']) === '') {
            $message = 'Motivation and valid ID document are required.';
        } else {
            seller_request_verification($conn, current_user_id(), trim($_POST['motivation']), $doc);
            $message = 'Verification request submitted.';
            $request = seller_request_by_user($conn, current_user_id());
        }
    }
    render_view('seller/verification', compact('request', 'message'));
}

function seller_create_listing(mysqli $conn): void {
    require_seller_verified();
    $cats = listing_categories($conn);
    $message = '';
    $old = ['title' => '', 'description' => '', 'category_id' => '', 'condition' => 'good', 'starting_price' => '', 'reserve_price' => '', 'end_datetime' => ''];

    if (!empty($_GET['template_id'])) {
        $template = seller_template_find($conn, (int)$_GET['template_id'], current_user_id());
        if ($template) { $old = array_merge($old, $template); }
    }
    if (!empty($_GET['relist_id'])) {
        $source = seller_relist_source($conn, (int)$_GET['relist_id'], current_user_id());
        if ($source) {
            $old = array_merge($old, $source);
            $old['starting_price'] = max(1, round((float)$source['starting_price'] * 0.90, 2));
            $old['reserve_price'] = '';
            $old['end_datetime'] = '';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $old = array_merge($old, $_POST);
        $errors = listing_errors($_POST);
        $imageCount = !empty($_FILES['images']['name']) ? count(array_filter($_FILES['images']['name'])) : 0;
        if ($imageCount > 5) { $errors[] = 'Upload maximum 5 images.'; }
        if (!$errors) {
            $id = listing_create($conn, current_user_id(), (int)$_POST['category_id'], trim($_POST['title']), trim($_POST['description']), $_POST['condition'], (float)$_POST['starting_price'], ($_POST['reserve_price'] === '' ? null : (float)$_POST['reserve_price']), $_POST['end_datetime']);
            for ($i = 0; $i < 5; $i++) {
                if (!empty($_FILES['images']['name'][$i])) {
                    $_FILES['single_image'] = ['name' => $_FILES['images']['name'][$i], 'type' => $_FILES['images']['type'][$i], 'tmp_name' => $_FILES['images']['tmp_name'][$i], 'error' => $_FILES['images']['error'][$i], 'size' => $_FILES['images']['size'][$i]];
                    $path = upload_file('single_image', 'listings', ['jpg', 'jpeg', 'png', 'webp']);
                    if ($path) { listing_add_image($conn, $id, $path, $i + 1); }
                }
            }
            if (!empty($_POST['save_template'])) {
                seller_create_template($conn, current_user_id(), trim($_POST['title']), trim($_POST['description']), (int)$_POST['category_id'], $_POST['condition'], (float)$_POST['starting_price'], ($_POST['reserve_price'] === '' ? null : (float)$_POST['reserve_price']));
            }
            $message = 'Listing submitted for moderator review.';
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                json_response(['success' => true, 'message' => $message]);
            }
            $old = ['title' => '', 'description' => '', 'category_id' => '', 'condition' => 'good', 'starting_price' => '', 'reserve_price' => '', 'end_datetime' => ''];
        } else {
            $message = format_errors($errors);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                json_response(['success' => false, 'message' => $message]);
            }
        }
    }
    render_view('seller/create_listing', compact('cats', 'message', 'old'));
}

function seller_listings(mysqli $conn): void { require_seller_verified(); listing_close_expired_auctions($conn); $rows = listing_by_seller($conn, current_user_id()); render_view('seller/listings', compact('rows')); }
function seller_edit_listing(mysqli $conn): void { require_seller_verified(); $listing = listing_find($conn, (int)$_GET['id']); if (!$listing || (int)$listing['seller_id'] !== current_user_id()) { http_response_code(404); echo 'Listing not found'; return; } $cats = listing_categories($conn); $message = ''; if ($_SERVER['REQUEST_METHOD'] === 'POST') { $errors = listing_errors($_POST); if ($errors) { $message = format_errors($errors); } else { $ok = listing_update_if_no_bids($conn, (int)$_GET['id'], current_user_id(), (int)$_POST['category_id'], trim($_POST['title']), trim($_POST['description']), $_POST['condition'], (float)$_POST['starting_price'], ($_POST['reserve_price'] === '' ? null : (float)$_POST['reserve_price']), $_POST['end_datetime']); $message = $ok ? 'Listing updated.' : 'Only your zero-bid listings can be edited.'; $listing = listing_find($conn, (int)$_GET['id']); } } render_view('seller/edit_listing', compact('listing', 'cats', 'message')); }
function seller_cancel_listing(mysqli $conn): void { require_seller_verified(); if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect_to('index.php?page=seller_listings'); } $reason = trim($_POST['reason'] ?? 'Cancelled by seller before any bid.'); listing_cancel_if_no_bids($conn, (int)$_POST['listing_id'], current_user_id(), $reason); redirect_to('index.php?page=seller_listings'); }
function seller_templates_page(mysqli $conn): void { require_seller_verified(); $cats = listing_categories($conn); $message = ''; if ($_SERVER['REQUEST_METHOD'] === 'POST') { if (trim($_POST['title'] ?? '') === '' || (float)($_POST['starting_price'] ?? 0) <= 0) { $message = 'Template title and positive starting price required.'; } elseif (!empty($_POST['reserve_price']) && (float)$_POST['reserve_price'] < (float)$_POST['starting_price']) { $message = 'Reserve cannot be lower than starting price.'; } else { seller_create_template($conn, current_user_id(), trim($_POST['title']), trim($_POST['description']), (int)$_POST['category_id'], $_POST['condition'], (float)$_POST['starting_price'], ($_POST['reserve_price'] === '' ? null : (float)$_POST['reserve_price'])); $message = 'Template saved.'; } } $rows = seller_templates($conn, current_user_id()); render_view('seller/templates', compact('rows', 'cats', 'message')); }
function seller_ended_page(mysqli $conn): void { require_seller_verified(); listing_close_expired_auctions($conn); $message = ''; if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['arrangement_note'])) { $note = trim($_POST['arrangement_note']); if ($note === '') { $message = 'Arrangement note cannot be empty.'; } else { seller_note_buyer($conn, (int)$_POST['listing_id'], current_user_id(), (int)$_POST['buyer_id'], $note); $message = 'Arrangement note saved.'; } } $rows = seller_ended($conn, current_user_id()); render_view('seller/ended', compact('rows', 'message')); }
function seller_analytics_page(mysqli $conn): void { require_seller_verified(); listing_close_expired_auctions($conn); $stats = seller_analytics($conn, current_user_id()); $trend = seller_sales_trend($conn, current_user_id()); render_view('seller/analytics', compact('stats', 'trend')); }
function seller_reviews(mysqli $conn): void { require_seller_verified(); $message = ''; if ($_SERVER['REQUEST_METHOD'] === 'POST') { if (isset($_POST['response_text'])) { review_respond($conn, (int)$_POST['review_id'], current_user_id(), trim($_POST['response_text'])); $message = 'Response saved.'; } else { $rating = (int)($_POST['rating'] ?? 0); $listingId = (int)($_POST['listing_id'] ?? 0); $buyerId = (int)($_POST['buyer_id'] ?? 0); $text = trim($_POST['review_text'] ?? ''); if ($rating < 1 || $rating > 5 || $text === '') { $message = 'Valid rating and review text required.'; } elseif (!seller_can_review_buyer($conn, $listingId, current_user_id(), $buyerId)) { $message = 'You can review only the winning buyer once per completed auction.'; } else { review_create($conn, $listingId, current_user_id(), $buyerId, $rating, $text); $message = 'Buyer review submitted.'; } } } $rows = review_received($conn, current_user_id()); $reviewableBuyers = seller_reviewable_buyers($conn, current_user_id()); render_view('seller/reviews', compact('rows', 'reviewableBuyers', 'message')); }
function seller_relist(mysqli $conn): void { require_seller_verified(); redirect_to('index.php?page=create_listing&relist_id=' . (int)($_GET['id'] ?? 0)); }

function moderator_dashboard_page(mysqli $conn): void {
    require_role('moderator');
    $stats = moderator_dashboard($conn);
    render_view('moderator/dashboard', compact('stats'));
}

function moderator_pending_listings(mysqli $conn): void {
    require_role('moderator');
    $message = '';
    $message_type = 'success-msg';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $listing_id = (int)($_POST['listing_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if ($action === 'approve') {
            moderator_approve_listing($conn, $listing_id);
            $message = 'Listing approved and set to active.';
        } elseif ($action === 'reject') {
            $reason = trim($_POST['rejection_reason'] ?? '');
            if ($reason === '') {
                $message = 'A rejection reason is required.';
                $message_type = 'error';
            } else {
                moderator_reject_listing($conn, $listing_id, $reason);
                $message = 'Listing rejected. Seller has been notified.';
            }
        }
    }
    $rows = listing_pending($conn);
    render_view('moderator/pending', compact('rows', 'message', 'message_type'));
}

function moderator_active_listings_page(mysqli $conn): void {
    require_role('moderator');
    $message = '';
    $message_type = 'success-msg';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $listing_id = (int)($_POST['listing_id'] ?? 0);
        $reason = trim($_POST['suspension_reason'] ?? '');
        if ($reason === '') {
            $message = 'A suspension reason is required.';
            $message_type = 'error';
        } else {
            moderator_suspend_listing($conn, $listing_id, $reason);
            $message = 'Listing suspended and moved back to pending review.';
        }
    }
    $keyword = trim($_GET['q'] ?? '');
    $rows = moderator_active_listings($conn, $keyword);
    render_view('moderator/active_listings', compact('rows', 'message', 'message_type', 'keyword'));
}

function moderator_listing_reports(mysqli $conn): void {
    require_role('moderator');
    $message = '';
    $message_type = 'success-msg';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $report_id = (int)($_POST['report_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $note = trim($_POST['moderator_note'] ?? '');

        if ($action === 'dismiss') {
            report_update_listing($conn, $report_id, 'dismissed', $note);
            $message = 'Report dismissed.';
        } elseif ($action === 'suspend') {
            $report = db_row($conn, 'SELECT listing_id FROM listing_reports WHERE id=?', 'i', [$report_id]);
            if ($report) {
                moderator_suspend_listing($conn, (int)$report['listing_id'], $note ?: 'Suspended following a report.');
                report_update_listing($conn, $report_id, 'resolved', $note);
                $message = 'Listing suspended and report resolved.';
            }
        } elseif ($action === 'warn_seller') {
            $report = db_row($conn, 'SELECT lr.listing_id, l.seller_id FROM listing_reports lr JOIN listings l ON l.id=lr.listing_id WHERE lr.id=?', 'i', [$report_id]);
            if ($report && $note !== '') {
                moderator_warn($conn, (int)$report['seller_id'], current_user_id(), $note);
                report_update_listing($conn, $report_id, 'resolved', $note);
                $message = 'Warning issued to seller and report resolved.';
            } else {
                $message = 'A note/reason is required to issue a warning.';
                $message_type = 'error';
            }
        }
    }
    $rows = report_listing_rows($conn);
    render_view('moderator/listing_reports', compact('rows', 'message', 'message_type'));
}

function moderator_user_reports(mysqli $conn): void {
    require_role('moderator');
    $message = '';
    $message_type = 'success-msg';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $report_id = (int)($_POST['report_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $note = trim($_POST['moderator_note'] ?? '');
        $reported_user_id = (int)($_POST['reported_user_id'] ?? 0);

        if ($action === 'dismiss') {
            report_update_user($conn, $report_id, 'dismissed', $note);
            $message = 'Report dismissed.';
        } elseif ($action === 'warn') {
            if ($note === '') {
                $message = 'A warning reason is required.';
                $message_type = 'error';
            } else {
                moderator_warn($conn, $reported_user_id, current_user_id(), $note);
                report_update_user($conn, $report_id, 'resolved', $note);
                $message = 'Warning issued to user and report resolved.';
            }
        } elseif ($action === 'escalate') {
            report_update_user($conn, $report_id, 'escalated', $note ?: 'Escalated to admin for suspension review.');
            $message = 'Report escalated to admin for suspension review.';
        }
    }
    $rows = report_user_rows($conn);
    render_view('moderator/user_reports', compact('rows', 'message', 'message_type'));
}

function moderator_warnings_page(mysqli $conn): void {
    require_role('moderator');
    $message = '';
    $message_type = 'success-msg';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if ($user_id <= 0 || $reason === '') {
            $message = 'User and warning reason are required.';
            $message_type = 'error';
        } else {
            $target = user_find($conn, $user_id);
            if (!$target || !in_array($target['role'], ['buyer', 'seller'], true)) {
                $message = 'Only buyers and sellers can receive warnings.';
                $message_type = 'error';
            } else {
                moderator_warn($conn, $user_id, current_user_id(), $reason);
                $message = 'Warning issued to ' . htmlspecialchars($target['name']) . '.';
            }
        }
    }
    $rows = moderator_warnings($conn);
    $users = moderator_users_list($conn, $_GET['q'] ?? '');
    render_view('moderator/warnings', compact('rows', 'users', 'message', 'message_type'));
}

function moderator_categories(mysqli $conn): void {
    require_role('moderator');
    $message = '';
    $message_type = 'success-msg';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') { $message = 'Category name is required.'; $message_type = 'error'; }
            else { moderator_add_category($conn, $name, trim($_POST['description'] ?? ''), (int)$_POST['parent_id'] ?: null); $message = 'Category added.'; }
        } elseif ($action === 'rename') {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') { $message = 'Category name is required.'; $message_type = 'error'; }
            else { moderator_rename_category($conn, (int)$_POST['id'], $name, trim($_POST['description'] ?? '')); $message = 'Category renamed.'; }
        } elseif ($action === 'merge') {
            $src = (int)$_POST['source_id']; $dst = (int)$_POST['dest_id'];
            if ($src === $dst || $src <= 0 || $dst <= 0) { $message = 'Select two different valid categories to merge.'; $message_type = 'error'; }
            else { moderator_merge_category($conn, $src, $dst); $message = 'Categories merged.'; }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $deleted = moderator_delete_empty_category($conn, $id);
            $message = $deleted ? 'Empty category deleted.' : 'Category could not be deleted — it may still contain listings.';
            if (!$deleted) $message_type = 'error';
        }
    }
    $rows = listing_categories($conn);
    render_view('moderator/categories', compact('rows', 'message', 'message_type'));
}

function moderator_activity_report(mysqli $conn): void {
    require_role('moderator');
    $from = $_GET['from'] ?? date('Y-m-01');
    $to = $_GET['to'] ?? date('Y-m-d');
    $stats = moderator_activity_period($conn, $from, $to);
    render_view('moderator/activity', compact('stats', 'from', 'to'));
}

function moderator_trust_score(mysqli $conn): void {
    require_role('moderator');
    $rows = moderator_trust_scores($conn);
    render_view('moderator/trust', compact('rows'));
}

function moderator_trust_detail_page(mysqli $conn): void {
    require_role('moderator');
    $user_id = (int)($_GET['id'] ?? 0);
    $user = moderator_user_detail($conn, $user_id);
    if (!$user) { http_response_code(404); echo 'User not found'; exit; }
    $warnings_history = moderator_user_warnings_history($conn, $user_id);
    $reports_history = moderator_user_reports_history($conn, $user_id);
    render_view('moderator/trust_detail', compact('user', 'warnings_history', 'reports_history'));
}

function moderator_keyword_abuse_page(mysqli $conn): void {
    require_role('moderator');
    $message = '';
    $message_type = 'success-msg';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'add_keyword') {
            $kw = trim($_POST['keyword'] ?? '');
            if ($kw === '') { $message = 'Keyword cannot be empty.'; $message_type = 'error'; }
            else { moderator_add_keyword($conn, $kw); $message = 'Keyword added to watchlist.'; }
        } elseif ($action === 'delete_keyword') {
            moderator_delete_keyword($conn, (int)$_POST['keyword_id']);
            $message = 'Keyword removed.';
        }
    }
    $keywords = moderator_flagged_keywords($conn);
    $flagged_listings = moderator_keyword_listings($conn);
    render_view('moderator/keyword_abuse', compact('keywords', 'flagged_listings', 'message', 'message_type'));
}

function moderator_messaging_page(mysqli $conn): void {
    require_role('moderator');
    $message = '';
    $message_type = 'success-msg';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $receiver_id = (int)($_POST['receiver_id'] ?? 0);
        $listing_id = (int)($_POST['listing_id'] ?? 0) ?: null;
        $msg_text = trim($_POST['message'] ?? '');
        if ($receiver_id <= 0 || $msg_text === '') {
            $message = 'Recipient and message are required.';
            $message_type = 'error';
        } else {
            $target = user_find($conn, $receiver_id);
            if (!$target || !in_array($target['role'], ['buyer', 'seller'], true)) {
                $message = 'Messages can only be sent to buyers and sellers.';
                $message_type = 'error';
            } else {
                moderator_send_message($conn, current_user_id(), $receiver_id, $listing_id, $msg_text);
                $message = 'Message sent to ' . htmlspecialchars($target['name']) . '.';
            }
        }
    }
    $sent_messages = moderator_messages_sent($conn, current_user_id());
    $users = moderator_users_list($conn, $_GET['q'] ?? '');
    render_view('moderator/messaging', compact('sent_messages', 'users', 'message', 'message_type'));
}

// =====================================================
// ADMIN CONTROLLERS
// =====================================================

function admin_dashboard(mysqli $conn): void { require_role('admin'); $stats = admin_dashboard_stats($conn); $userStats = user_stats($conn); render_view('admin/dashboard', compact('stats', 'userStats')); }
function admin_seller_verifications(mysqli $conn): void { require_role('admin'); if ($_SERVER['REQUEST_METHOD'] === 'POST') admin_decide_verification($conn, (int)$_POST['request_id'], $_POST['status'], current_user_id(), trim($_POST['reason'] ?? '')); $rows = admin_verifications($conn, 'pending'); render_view('admin/verifications', compact('rows')); }
function admin_users(mysqli $conn): void { require_role('admin'); if ($_SERVER['REQUEST_METHOD'] === 'POST') { if ($_POST['action'] === 'active' && (int)$_POST['id'] !== current_user_id()) user_set_active($conn, (int)$_POST['id'], (int)$_POST['is_active']); if ($_POST['action'] === 'role') user_set_role($conn, (int)$_POST['id'], $_POST['role']); if ($_POST['action'] === 'revoke') user_revoke_seller($conn, (int)$_POST['id']); } $rows = user_all($conn, $_GET['q'] ?? ''); render_view('admin/users', compact('rows')); }
function admin_listings(mysqli $conn): void { require_role('admin'); if ($_SERVER['REQUEST_METHOD'] === 'POST') listing_set_status($conn, (int)$_POST['listing_id'], 'cancelled', trim($_POST['reason'])); $rows = listing_all_admin($conn, $_GET['status'] ?? ''); render_view('admin/listings', compact('rows')); }
function admin_commissions(mysqli $conn): void { require_role('admin'); $message = ''; if ($_SERVER['REQUEST_METHOD'] === 'POST') { $rate = (float)$_POST['rate']; if ($rate >= 0 && $rate <= 100) { admin_set_default_rate($conn, $rate); $message = 'Commission rate saved.'; } else { $message = 'Rate must be between 0 and 100.'; } } render_view('admin/commissions', compact('message')); }
function admin_financial_reports(mysqli $conn): void { require_role('admin'); $rows = admin_financials($conn); render_view('admin/financials', compact('rows')); }
function admin_analytics(mysqli $conn): void { require_role('admin'); $rows = admin_bid_analytics($conn); render_view('admin/analytics', compact('rows')); }
function admin_featured(mysqli $conn): void { require_role('admin'); if ($_SERVER['REQUEST_METHOD'] === 'POST') listing_set_featured($conn, (int)$_POST['listing_id'], (int)$_POST['featured']); $rows = listing_all_admin($conn); render_view('admin/featured', compact('rows')); }
function admin_announcements_page(mysqli $conn): void { require_role('admin'); if ($_SERVER['REQUEST_METHOD'] === 'POST') admin_announcement_add($conn, trim($_POST['title']), trim($_POST['message']), current_user_id()); $rows = admin_announcements($conn); render_view('admin/announcements', compact('rows')); }
?>
