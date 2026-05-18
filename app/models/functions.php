<?php
function db_rows(mysqli $conn, string $sql, string $types = '', array $params = []): array {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) { return []; }
    if ($types) { mysqli_stmt_bind_param($stmt, $types, ...$params); }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function db_row(mysqli $conn, string $sql, string $types = '', array $params = []): ?array {
    $rows = db_rows($conn, $sql, $types, $params);
    return $rows[0] ?? null;
}

function db_execute(mysqli $conn, string $sql, string $types = '', array $params = []): bool {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) { return false; }
    if ($types) { mysqli_stmt_bind_param($stmt, $types, ...$params); }
    return mysqli_stmt_execute($stmt);
}

function db_insert(mysqli $conn, string $sql, string $types = '', array $params = []): int {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) { return 0; }
    if ($types) { mysqli_stmt_bind_param($stmt, $types, ...$params); }
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($conn);
}

function render_view(string $view, array $data = []): void {
    global $conn;
    extract($data);
    $viewFile = __DIR__ . '/../views/' . $view . '.php';
    require __DIR__ . '/../views/layouts/header.php';
    require $viewFile;
    require __DIR__ . '/../views/layouts/footer.php';
}

function format_errors(array $errors): string {
    return implode('<br>', array_map('e', $errors));
}

function user_find_by_email(mysqli $conn, string $email): ?array { return db_row($conn, 'SELECT * FROM users WHERE email = ?', 's', [$email]); }
function user_find(mysqli $conn, int $id): ?array { return db_row($conn, 'SELECT * FROM users WHERE id = ?', 'i', [$id]); }
function user_create(mysqli $conn, string $name, string $email, string $hash, string $phone, string $bio): int { return db_insert($conn, 'INSERT INTO users (name,email,password_hash,phone,bio,role) VALUES (?,?,?,?,?,\'buyer\')', 'sssss', [$name, $email, $hash, $phone, $bio]); }
function user_update_profile(mysqli $conn, int $id, string $name, string $phone, string $bio, ?string $pic = null): bool { return $pic ? db_execute($conn, 'UPDATE users SET name=?, phone=?, bio=?, profile_pic=? WHERE id=?', 'ssssi', [$name, $phone, $bio, $pic, $id]) : db_execute($conn, 'UPDATE users SET name=?, phone=?, bio=? WHERE id=?', 'sssi', [$name, $phone, $bio, $id]); }
function user_change_password(mysqli $conn, int $id, string $hash): bool { return db_execute($conn, 'UPDATE users SET password_hash=? WHERE id=?', 'si', [$hash, $id]); }
function user_all(mysqli $conn, string $keyword = ''): array { $like = '%' . $keyword . '%'; return db_rows($conn, 'SELECT * FROM users WHERE name LIKE ? OR email LIKE ? OR role LIKE ? ORDER BY created_at DESC', 'sss', [$like, $like, $like]); }
function user_set_active(mysqli $conn, int $id, int $active): bool { return db_execute($conn, 'UPDATE users SET is_active=? WHERE id=?', 'ii', [$active, $id]); }
function user_set_role(mysqli $conn, int $id, string $role): bool { return db_execute($conn, 'UPDATE users SET role=? WHERE id=?', 'si', [$role, $id]); }
function user_revoke_seller(mysqli $conn, int $id): bool { return db_execute($conn, 'UPDATE users SET seller_verified=0, role=\'buyer\' WHERE id=?', 'i', [$id]); }
function user_stats(mysqli $conn): ?array { return db_row($conn, "SELECT COUNT(*) total, SUM(role='buyer') buyers, SUM(role='seller') sellers, SUM(role='moderator') moderators, SUM(role='admin') admins FROM users"); }

function listing_close_expired_auctions(mysqli $conn): void {
    $expired = db_rows($conn, "SELECT id, seller_id, reserve_price FROM listings WHERE status='active' AND end_datetime < NOW()");
    foreach ($expired as $listing) {
        $top = db_row($conn, 'SELECT id, amount FROM bids WHERE listing_id=? ORDER BY amount DESC LIMIT 1', 'i', [$listing['id']]);
        if ($top && ($listing['reserve_price'] === null || (float)$top['amount'] >= (float)$listing['reserve_price'])) {
            db_execute($conn, "UPDATE listings SET status='ended', winner_bid_id=? WHERE id=?", 'ii', [$top['id'], $listing['id']]);
            $rateRow = db_row($conn, 'SELECT rate FROM commission_rates WHERE is_default=1 ORDER BY id DESC LIMIT 1');
            $rate = (float)($rateRow['rate'] ?? 5.00);
            $commission = ((float)$top['amount'] * $rate) / 100;
            db_execute($conn, 'INSERT INTO platform_fees (listing_id,seller_id,final_price,commission_rate,commission_amount) VALUES (?,?,?,?,?)', 'iiddd', [$listing['id'], $listing['seller_id'], $top['amount'], $rate, $commission]);
        } else {
            db_execute($conn, "UPDATE listings SET status='ended' WHERE id=?", 'i', [$listing['id']]);
        }
    }
}

function listing_categories(mysqli $conn): array { return db_rows($conn, 'SELECT * FROM categories ORDER BY name'); }
function listing_search(mysqli $conn, string $keyword = '', string $category = '', string $condition = '', string $min = '', string $max = '', string $time = ''): array {
    $sql = "SELECT l.*, u.name seller_name, c.name category_name, (SELECT image_path FROM listing_images WHERE listing_id=l.id ORDER BY display_order LIMIT 1) image_path FROM listings l JOIN users u ON u.id=l.seller_id JOIN categories c ON c.id=l.category_id WHERE l.status='active'";
    $types = ''; $params = [];
    if ($keyword !== '') { $sql .= ' AND (l.title LIKE ? OR l.description LIKE ?)'; $types .= 'ss'; $like = '%' . $keyword . '%'; $params[] = $like; $params[] = $like; }
    if ($category !== '') { $sql .= ' AND l.category_id=?'; $types .= 'i'; $params[] = (int)$category; }
    if ($condition !== '') { $sql .= ' AND l.`condition`=?'; $types .= 's'; $params[] = $condition; }
    if ($min !== '') { $sql .= ' AND l.current_bid>=?'; $types .= 'd'; $params[] = (float)$min; }
    if ($max !== '') { $sql .= ' AND l.current_bid<=?'; $types .= 'd'; $params[] = (float)$max; }
    if ($time === '1h') { $sql .= ' AND l.end_datetime <= DATE_ADD(NOW(), INTERVAL 1 HOUR)'; }
    if ($time === '24h') { $sql .= ' AND l.end_datetime <= DATE_ADD(NOW(), INTERVAL 24 HOUR)'; }
    if ($time === '7d') { $sql .= ' AND l.end_datetime <= DATE_ADD(NOW(), INTERVAL 7 DAY)'; }
    $sql .= ' ORDER BY l.end_datetime ASC';
    return db_rows($conn, $sql, $types, $params);
}
function listing_find(mysqli $conn, int $id): ?array { return db_row($conn, 'SELECT l.*, u.name seller_name, u.email seller_email, u.phone seller_phone, u.reputation_score, c.name category_name FROM listings l JOIN users u ON u.id=l.seller_id JOIN categories c ON c.id=l.category_id WHERE l.id=?', 'i', [$id]); }
function listing_images(mysqli $conn, int $id): array { return db_rows($conn, 'SELECT * FROM listing_images WHERE listing_id=? ORDER BY display_order', 'i', [$id]); }
function listing_create(mysqli $conn, int $seller, int $category, string $title, string $description, string $condition, float $start, ?float $reserve, string $end): int { return db_insert($conn, 'INSERT INTO listings (seller_id,category_id,title,description,`condition`,starting_price,reserve_price,current_bid,end_datetime,status) VALUES (?,?,?,?,?,?,?,?,?,\'pending_review\')', 'iisssddds', [$seller, $category, $title, $description, $condition, $start, $reserve, $start, $end]); }
function listing_add_image(mysqli $conn, int $listing, string $path, int $order): bool { return db_execute($conn, 'INSERT INTO listing_images (listing_id,image_path,display_order) VALUES (?,?,?)', 'isi', [$listing, $path, $order]); }
function listing_by_seller(mysqli $conn, int $seller): array { return db_rows($conn, 'SELECT l.*, c.name category_name, (SELECT COUNT(*) FROM bids b WHERE b.listing_id=l.id) bid_count, TIMESTAMPDIFF(SECOND, NOW(), l.end_datetime) seconds_left FROM listings l JOIN categories c ON c.id=l.category_id WHERE l.seller_id=? ORDER BY l.created_at DESC', 'i', [$seller]); }
function listing_update_if_no_bids(mysqli $conn, int $id, int $seller, int $category, string $title, string $description, string $condition, float $start, ?float $reserve, string $end): bool { return db_execute($conn, 'UPDATE listings SET category_id=?, title=?, description=?, `condition`=?, starting_price=?, reserve_price=?, current_bid=?, end_datetime=? WHERE id=? AND seller_id=? AND (SELECT COUNT(*) FROM bids WHERE bids.listing_id=listings.id)=0', 'isssdddsii', [$category, $title, $description, $condition, $start, $reserve, $start, $end, $id, $seller]); }
function listing_cancel_if_no_bids(mysqli $conn, int $id, int $seller, string $reason): bool { return db_execute($conn, "UPDATE listings SET status='cancelled', cancel_reason=? WHERE id=? AND seller_id=? AND status IN ('pending_review','active') AND (SELECT COUNT(*) FROM bids WHERE bids.listing_id=listings.id)=0", 'sii', [$reason, $id, $seller]); }
function listing_pending(mysqli $conn): array { return db_rows($conn, "SELECT l.*, u.name seller_name, c.name category_name FROM listings l JOIN users u ON u.id=l.seller_id JOIN categories c ON c.id=l.category_id WHERE l.status='pending_review' ORDER BY l.created_at"); }
function listing_set_status(mysqli $conn, int $id, string $status, string $reason = ''): bool { if ($status === 'rejected') return db_execute($conn, 'UPDATE listings SET status=?, rejection_reason=? WHERE id=?', 'ssi', [$status, $reason, $id]); if ($status === 'cancelled') return db_execute($conn, 'UPDATE listings SET status=?, cancel_reason=? WHERE id=?', 'ssi', [$status, $reason, $id]); return db_execute($conn, 'UPDATE listings SET status=? WHERE id=?', 'si', [$status, $id]); }
function listing_all_admin(mysqli $conn, string $status = ''): array { return $status ? db_rows($conn, 'SELECT l.*, u.name seller_name, c.name category_name FROM listings l JOIN users u ON u.id=l.seller_id JOIN categories c ON c.id=l.category_id WHERE l.status=? ORDER BY l.created_at DESC', 's', [$status]) : db_rows($conn, 'SELECT l.*, u.name seller_name, c.name category_name FROM listings l JOIN users u ON u.id=l.seller_id JOIN categories c ON c.id=l.category_id ORDER BY l.created_at DESC'); }
function listing_set_featured(mysqli $conn, int $id, int $featured): bool { return db_execute($conn, 'UPDATE listings SET is_featured=? WHERE id=?', 'ii', [$featured, $id]); }

function bid_history(mysqli $conn, int $listingId): array { return db_rows($conn, 'SELECT b.*, u.name buyer_name FROM bids b JOIN users u ON u.id=b.buyer_id WHERE b.listing_id=? ORDER BY b.amount DESC, b.created_at DESC', 'i', [$listingId]); }
function bid_place(mysqli $conn, int $listingId, int $buyerId, float $amount, int $auto = 0): array {
    $listing = db_row($conn, 'SELECT * FROM listings WHERE id=?', 'i', [$listingId]);
    if (!$listing) return ['success'=>false,'message'=>'Listing not found'];
    if ($listing['status'] !== 'active') return ['success'=>false,'message'=>'Auction is not active'];
    if ((int)$listing['seller_id'] === (int)$buyerId) return ['success'=>false,'message'=>'You cannot bid on your own listing'];
    if (strtotime($listing['end_datetime']) <= time()) return ['success'=>false,'message'=>'Auction already ended'];
    if ((float)$amount <= (float)$listing['current_bid']) return ['success'=>false,'message'=>'Bid must be greater than current bid'];
    $bidId = db_insert($conn, 'INSERT INTO bids (listing_id,buyer_id,amount,is_auto_bid) VALUES (?,?,?,?)', 'iidi', [$listingId, $buyerId, $amount, $auto]);
    db_execute($conn, 'UPDATE listings SET current_bid=? WHERE id=?', 'di', [$amount, $listingId]);
    bid_process_auto_bids($conn, $listingId, $buyerId, $amount);
    $updated = db_row($conn, 'SELECT current_bid FROM listings WHERE id=?', 'i', [$listingId]);
    return ['success'=>true,'message'=>'Bid placed successfully','bid_id'=>$bidId,'current_bid'=>$updated['current_bid']];
}
function bid_set_auto(mysqli $conn, int $listingId, int $buyerId, float $maxAmount): array {
    $listing = db_row($conn, 'SELECT * FROM listings WHERE id=? AND status=\'active\'', 'i', [$listingId]);
    if (!$listing) return ['success'=>false,'message'=>'Active listing not found'];
    if ((int)$listing['seller_id'] === (int)$buyerId) return ['success'=>false,'message'=>'You cannot bid on your own listing'];
    if (strtotime($listing['end_datetime']) <= time()) return ['success'=>false,'message'=>'Auction already ended'];
    if ((float)$maxAmount <= (float)$listing['current_bid']) return ['success'=>false,'message'=>'Auto bid max must be higher than current bid'];

    db_execute($conn, 'INSERT INTO auto_bids (listing_id,buyer_id,max_amount,is_active) VALUES (?,?,?,1) ON DUPLICATE KEY UPDATE max_amount=VALUES(max_amount), is_active=1', 'iid', [$listingId, $buyerId, $maxAmount]);

    $topBid = db_row($conn, 'SELECT buyer_id, amount FROM bids WHERE listing_id=? ORDER BY amount DESC LIMIT 1', 'i', [$listingId]);
    if (!$topBid || (int)$topBid['buyer_id'] !== $buyerId) {
        $nextAmount = min($maxAmount, (float)$listing['current_bid'] + 100);
        db_insert($conn, 'INSERT INTO bids (listing_id,buyer_id,amount,is_auto_bid) VALUES (?,?,?,1)', 'iid', [$listingId, $buyerId, $nextAmount]);
        db_execute($conn, 'UPDATE listings SET current_bid=? WHERE id=?', 'di', [$nextAmount, $listingId]);
    }

    $updated = db_row($conn, 'SELECT current_bid FROM listings WHERE id=?', 'i', [$listingId]);
    return ['success'=>true,'message'=>'Auto bid saved','current_bid'=>$updated['current_bid'] ?? $listing['current_bid']];
}
function bid_process_auto_bids(mysqli $conn, int $listingId, int $latestBuyer, float $latestAmount): void { $auto = db_row($conn, 'SELECT * FROM auto_bids WHERE listing_id=? AND buyer_id<>? AND is_active=1 AND max_amount>? ORDER BY max_amount DESC LIMIT 1', 'iid', [$listingId, $latestBuyer, $latestAmount]); if (!$auto) return; $newAmount = min((float)$auto['max_amount'], (float)$latestAmount + 100); db_insert($conn, 'INSERT INTO bids (listing_id,buyer_id,amount,is_auto_bid) VALUES (?,?,?,1)', 'iid', [$listingId, $auto['buyer_id'], $newAmount]); db_execute($conn, 'UPDATE listings SET current_bid=? WHERE id=?', 'di', [$newAmount, $listingId]); }
function bid_by_buyer(mysqli $conn, int $buyer): array { return db_rows($conn, "SELECT l.*, MAX(b.amount) my_highest, CASE WHEN l.winner_bid_id IN (SELECT id FROM bids WHERE buyer_id=?) THEN 'Won' WHEN l.status='ended' THEN 'Lost' WHEN MAX(b.amount)=l.current_bid THEN 'Leading' ELSE 'Outbid' END bid_status FROM bids b JOIN listings l ON l.id=b.listing_id WHERE b.buyer_id=? GROUP BY l.id ORDER BY MAX(b.created_at) DESC", 'ii', [$buyer, $buyer]); }
function bid_spending(mysqli $conn, int $buyer): ?array { return db_row($conn, 'SELECT COUNT(*) total_bids, COALESCE(SUM(CASE WHEN l.winner_bid_id=b.id THEN b.amount ELSE 0 END),0) total_spent, SUM(CASE WHEN l.winner_bid_id=b.id THEN 1 ELSE 0 END) wins FROM bids b JOIN listings l ON l.id=b.listing_id WHERE b.buyer_id=?', 'i', [$buyer]); }
function watchlist_rows(mysqli $conn, int $user): array { return db_rows($conn, 'SELECT w.*, l.title,l.current_bid,l.end_datetime,l.status FROM watchlist w JOIN listings l ON l.id=w.listing_id WHERE w.buyer_id=? ORDER BY l.end_datetime ASC', 'i', [$user]); }
function watchlist_toggle(mysqli $conn, int $user, int $listing): string { $target = db_row($conn, "SELECT id FROM listings WHERE id=? AND status='active'", 'i', [$listing]); if (!$target) return 'Active auction not found'; $exists = db_row($conn, 'SELECT id FROM watchlist WHERE buyer_id=? AND listing_id=?', 'ii', [$user, $listing]); if ($exists) { db_execute($conn, 'DELETE FROM watchlist WHERE id=?', 'i', [$exists['id']]); return 'Removed from watchlist'; } db_execute($conn, 'INSERT IGNORE INTO watchlist (buyer_id,listing_id) VALUES (?,?)', 'ii', [$user, $listing]); return 'Added to watchlist'; }
function won_auctions(mysqli $conn, int $user): array { return db_rows($conn, 'SELECT l.*, b.amount winning_amount, s.id seller_id, s.name seller_name, s.email seller_email, s.phone seller_phone FROM listings l JOIN bids b ON b.id=l.winner_bid_id JOIN users s ON s.id=l.seller_id WHERE b.buyer_id=? ORDER BY l.end_datetime DESC', 'i', [$user]); }

function review_create(mysqli $conn, int $listing, int $reviewer, int $reviewee, int $rating, string $text): bool {
    $created = db_execute($conn, 'INSERT INTO reviews (listing_id,reviewer_id,reviewee_id,rating,review_text) VALUES (?,?,?,?,?)', 'iiiis', [$listing, $reviewer, $reviewee, $rating, $text]);
    if ($created) {
        db_execute($conn, 'UPDATE users SET reputation_score=(SELECT ROUND(AVG(rating),2) FROM reviews WHERE reviewee_id=?) WHERE id=?', 'ii', [$reviewee, $reviewee]);
    }
    return $created;
}
function review_received(mysqli $conn, int $user): array { return db_rows($conn, 'SELECT r.*, u.name reviewer_name, l.title FROM reviews r JOIN users u ON u.id=r.reviewer_id JOIN listings l ON l.id=r.listing_id WHERE r.reviewee_id=? ORDER BY r.created_at DESC', 'i', [$user]); }
function review_sent(mysqli $conn, int $user): array { return db_rows($conn, 'SELECT r.*, u.name reviewee_name, l.title FROM reviews r JOIN users u ON u.id=r.reviewee_id JOIN listings l ON l.id=r.listing_id WHERE r.reviewer_id=? ORDER BY r.created_at DESC', 'i', [$user]); }
function reviewable_won_auctions(mysqli $conn, int $user): array { return db_rows($conn, 'SELECT l.id listing_id, l.title, l.seller_id, s.name seller_name FROM listings l JOIN bids b ON b.id=l.winner_bid_id JOIN users s ON s.id=l.seller_id LEFT JOIN reviews r ON r.listing_id=l.id AND r.reviewer_id=? WHERE b.buyer_id=? AND l.status=\'ended\' AND r.id IS NULL ORDER BY l.end_datetime DESC', 'ii', [$user, $user]); }
function buyer_can_review_listing(mysqli $conn, int $listing, int $buyer, int $seller): bool { return (bool)db_row($conn, 'SELECT l.id FROM listings l JOIN bids b ON b.id=l.winner_bid_id LEFT JOIN reviews r ON r.listing_id=l.id AND r.reviewer_id=? WHERE l.id=? AND b.buyer_id=? AND l.seller_id=? AND l.status=\'ended\' AND r.id IS NULL', 'iiii', [$buyer, $listing, $buyer, $seller]); }
function review_respond(mysqli $conn, int $id, int $user, string $text): bool { return db_execute($conn, 'UPDATE reviews SET response_text=? WHERE id=? AND reviewee_id=?', 'sii', [$text, $id, $user]); }

function report_create_listing(mysqli $conn, int $listing, int $reporter, string $reason, string $description): bool { return db_execute($conn, 'INSERT INTO listing_reports (listing_id,reporter_id,reason,description) VALUES (?,?,?,?)', 'iiss', [$listing, $reporter, $reason, $description]); }
function report_create_user(mysqli $conn, int $reporter, int $reported, string $reason, string $description): bool { return db_execute($conn, 'INSERT INTO user_reports (reporter_id,reported_user_id,reason,description) VALUES (?,?,?,?)', 'iiss', [$reporter, $reported, $reason, $description]); }
function buyer_reportable_listings(mysqli $conn): array { return db_rows($conn, "SELECT id, title FROM listings WHERE status IN ('active','ended') ORDER BY created_at DESC"); }
function buyer_reportable_users(mysqli $conn, int $currentUser): array { return db_rows($conn, 'SELECT id, name, role FROM users WHERE id<>? AND is_active=1 ORDER BY name', 'i', [$currentUser]); }
function report_listing_rows(mysqli $conn): array { return db_rows($conn, 'SELECT r.*, l.title, l.seller_id, l.status listing_status, u.name reporter_name FROM listing_reports r JOIN listings l ON l.id=r.listing_id JOIN users u ON u.id=r.reporter_id ORDER BY r.created_at DESC'); }
function report_user_rows(mysqli $conn): array { return db_rows($conn, 'SELECT r.*, a.name reporter_name, b.name reported_name FROM user_reports r JOIN users a ON a.id=r.reporter_id JOIN users b ON b.id=r.reported_user_id ORDER BY r.created_at DESC'); }
function report_update_listing(mysqli $conn, int $id, string $status, string $note): bool { return db_execute($conn, 'UPDATE listing_reports SET status=?, moderator_note=? WHERE id=?', 'ssi', [$status, $note, $id]); }
function report_update_user(mysqli $conn, int $id, string $status, string $note): bool { return db_execute($conn, 'UPDATE user_reports SET status=?, moderator_note=? WHERE id=?', 'ssi', [$status, $note, $id]); }

function seller_request_verification(mysqli $conn, int $user, string $motivation, string $doc): bool { return db_execute($conn, 'INSERT INTO seller_verification_requests (user_id,motivation,id_document_path) VALUES (?,?,?)', 'iss', [$user, $motivation, $doc]); }
function seller_request_by_user(mysqli $conn, int $user): ?array { return db_row($conn, 'SELECT * FROM seller_verification_requests WHERE user_id=? ORDER BY submitted_at DESC LIMIT 1', 'i', [$user]); }
function seller_create_template(mysqli $conn, int $seller, string $title, string $desc, int $cat, string $condition, float $price, ?float $reserve): bool { return db_execute($conn, 'INSERT INTO auction_templates (seller_id,title,description,category_id,`condition`,starting_price,reserve_price) VALUES (?,?,?,?,?,?,?)', 'issisdd', [$seller, $title, $desc, $cat, $condition, $price, $reserve]); }
function seller_templates(mysqli $conn, int $seller): array { return db_rows($conn, 'SELECT t.*, c.name category_name FROM auction_templates t JOIN categories c ON c.id=t.category_id WHERE seller_id=?', 'i', [$seller]); }
function seller_template_find(mysqli $conn, int $id, int $seller): ?array { return db_row($conn, 'SELECT * FROM auction_templates WHERE id=? AND seller_id=?', 'ii', [$id, $seller]); }
function seller_relist_source(mysqli $conn, int $id, int $seller): ?array { return db_row($conn, "SELECT * FROM listings WHERE id=? AND seller_id=? AND status='ended' AND winner_bid_id IS NULL", 'ii', [$id, $seller]); }
function seller_ended(mysqli $conn, int $seller): array { return db_rows($conn, "SELECT l.*, u.id winner_id, u.name winner_name, u.email winner_email, u.phone winner_phone, (SELECT m.message FROM messages m WHERE m.listing_id=l.id AND m.sender_id=? ORDER BY m.created_at DESC LIMIT 1) arrangement_note FROM listings l LEFT JOIN bids b ON b.id=l.winner_bid_id LEFT JOIN users u ON u.id=b.buyer_id WHERE l.seller_id=? AND (l.status='ended' OR l.end_datetime<NOW()) ORDER BY l.end_datetime DESC", 'ii', [$seller, $seller]); }
function seller_note_buyer(mysqli $conn, int $listing, int $seller, int $buyer, string $message): bool { return db_execute($conn, 'INSERT INTO messages (sender_id,receiver_id,listing_id,message) VALUES (?,?,?,?)', 'iiis', [$seller, $buyer, $listing, $message]); }
function seller_reviewable_buyers(mysqli $conn, int $seller): array { return db_rows($conn, 'SELECT l.id listing_id, l.title, b.buyer_id, u.name buyer_name FROM listings l JOIN bids b ON b.id=l.winner_bid_id JOIN users u ON u.id=b.buyer_id LEFT JOIN reviews r ON r.listing_id=l.id AND r.reviewer_id=? WHERE l.seller_id=? AND l.status=\'ended\' AND r.id IS NULL ORDER BY l.end_datetime DESC', 'ii', [$seller, $seller]); }
function seller_can_review_buyer(mysqli $conn, int $listing, int $seller, int $buyer): bool { return (bool)db_row($conn, 'SELECT l.id FROM listings l JOIN bids b ON b.id=l.winner_bid_id LEFT JOIN reviews r ON r.listing_id=l.id AND r.reviewer_id=? WHERE l.id=? AND l.seller_id=? AND b.buyer_id=? AND l.status=\'ended\' AND r.id IS NULL', 'iiii', [$seller, $listing, $seller, $buyer]); }
function seller_analytics(mysqli $conn, int $seller): ?array { return db_row($conn, "SELECT COUNT(*) total_auctions, COALESCE(SUM(status='ended' AND winner_bid_id IS NOT NULL),0) sold, COALESCE(AVG(CASE WHEN winner_bid_id IS NOT NULL THEN current_bid END),0) avg_sale, COALESCE(SUM(CASE WHEN winner_bid_id IS NOT NULL THEN current_bid ELSE 0 END),0) revenue, (SELECT c.name FROM listings l2 JOIN categories c ON c.id=l2.category_id WHERE l2.seller_id=? GROUP BY c.id, c.name ORDER BY COUNT(*) DESC LIMIT 1) popular_category FROM listings WHERE seller_id=?", 'ii', [$seller, $seller]); }
function seller_sales_trend(mysqli $conn, int $seller): array { return db_rows($conn, "SELECT DATE(end_datetime) sale_day, COUNT(*) sold_count, SUM(current_bid) revenue FROM listings WHERE seller_id=? AND status='ended' AND winner_bid_id IS NOT NULL GROUP BY DATE(end_datetime) ORDER BY sale_day DESC LIMIT 10", 'i', [$seller]); }

function moderator_dashboard(mysqli $conn): ?array { return db_row($conn, "SELECT (SELECT COUNT(*) FROM listings WHERE status='pending_review') pending_listings, (SELECT COUNT(*) FROM listing_reports WHERE status='pending') listing_reports, (SELECT COUNT(*) FROM user_reports WHERE status='pending') user_reports, (SELECT COUNT(*) FROM warnings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) warnings_week"); }
function moderator_warn(mysqli $conn, int $user, int $by, string $reason): bool { return db_execute($conn, 'INSERT INTO warnings (user_id,issued_by,reason) VALUES (?,?,?)', 'iis', [$user, $by, $reason]); }
function moderator_warnings(mysqli $conn): array { return db_rows($conn, 'SELECT w.*, u.name user_name, u.email user_email, m.name issued_by_name FROM warnings w JOIN users u ON u.id=w.user_id JOIN users m ON m.id=w.issued_by ORDER BY w.created_at DESC'); }
function moderator_add_category(mysqli $conn, string $name, string $desc, ?int $parent): bool { return db_execute($conn, 'INSERT INTO categories (name,description,parent_id) VALUES (?,?,?)', 'ssi', [$name, $desc, $parent]); }
function moderator_rename_category(mysqli $conn, int $id, string $name, string $desc): bool { return db_execute($conn, 'UPDATE categories SET name=?, description=? WHERE id=?', 'ssi', [$name, $desc, $id]); }
function moderator_delete_empty_category(mysqli $conn, int $id): bool { return db_execute($conn, 'DELETE FROM categories WHERE id=? AND NOT EXISTS (SELECT 1 FROM listings WHERE category_id=?)', 'ii', [$id, $id]); }
function moderator_merge_category(mysqli $conn, int $source, int $dest): bool { db_execute($conn, 'UPDATE listings SET category_id=? WHERE category_id=?', 'ii', [$dest, $source]); return db_execute($conn, 'DELETE FROM categories WHERE id=?', 'i', [$source]); }
function moderator_activity(mysqli $conn): ?array { return db_row($conn, "SELECT (SELECT COUNT(*) FROM listings WHERE status IN ('active','rejected')) reviewed, (SELECT COUNT(*) FROM listings WHERE status='active') approved, (SELECT COUNT(*) FROM listing_reports WHERE status<>'pending') listing_reports, (SELECT COUNT(*) FROM user_reports WHERE status<>'pending') user_reports, (SELECT COUNT(*) FROM warnings) warnings"); }
function moderator_trust_scores(mysqli $conn): array { return db_rows($conn, 'SELECT u.id, u.name, u.email, u.role, u.reputation_score, u.is_active, (SELECT COUNT(*) FROM warnings w WHERE w.user_id=u.id) warnings, (SELECT COUNT(*) FROM user_reports r WHERE r.reported_user_id=u.id) reports FROM users u WHERE u.role IN (\'buyer\',\'seller\') ORDER BY reputation_score DESC'); }

// --- Moderator: Active Listing Suspension ---
function moderator_active_listings(mysqli $conn, string $keyword = ''): array {
    $sql = "SELECT l.*, u.name seller_name, c.name category_name, (SELECT COUNT(*) FROM bids WHERE listing_id=l.id) bid_count FROM listings l JOIN users u ON u.id=l.seller_id JOIN categories c ON c.id=l.category_id WHERE l.status='active'";
    $types = ''; $params = [];
    if ($keyword !== '') { $sql .= ' AND (l.title LIKE ? OR l.description LIKE ?)'; $types = 'ss'; $like = '%' . $keyword . '%'; $params = [$like, $like]; }
    $sql .= ' ORDER BY l.created_at DESC';
    return db_rows($conn, $sql, $types, $params);
}

function moderator_approve_listing(mysqli $conn, int $id): bool {
    return db_execute($conn, "UPDATE listings SET status='active', rejection_reason=NULL, suspension_reason=NULL WHERE id=? AND status='pending_review'", 'i', [$id]);
}

function moderator_reject_listing(mysqli $conn, int $id, string $reason): bool {
    return db_execute($conn, "UPDATE listings SET status='rejected', rejection_reason=? WHERE id=? AND status='pending_review'", 'si', [$reason, $id]);
}

function moderator_suspend_listing(mysqli $conn, int $id, string $reason): bool {
    return db_execute($conn, "UPDATE listings SET status='pending_review', suspension_reason=? WHERE id=? AND status='active'", 'si', [$reason, $id]);
}

// --- Moderator: Keyword Abuse Log ---
function moderator_flagged_keywords(mysqli $conn): array {
    return db_rows($conn, 'SELECT * FROM flagged_keywords ORDER BY keyword');
}

function moderator_add_keyword(mysqli $conn, string $keyword): bool {
    return db_execute($conn, 'INSERT IGNORE INTO flagged_keywords (keyword) VALUES (?)', 's', [strtolower(trim($keyword))]);
}

function moderator_delete_keyword(mysqli $conn, int $id): bool {
    return db_execute($conn, 'DELETE FROM flagged_keywords WHERE id=?', 'i', [$id]);
}

function moderator_keyword_listings(mysqli $conn): array {
    $keywords = db_rows($conn, 'SELECT keyword FROM flagged_keywords');
    if (!$keywords) return [];
    $conditions = []; $types = ''; $params = [];
    foreach ($keywords as $k) {
        $like = '%' . $k['keyword'] . '%';
        $conditions[] = '(l.title LIKE ? OR l.description LIKE ?)';
        $types .= 'ss';
        $params[] = $like;
        $params[] = $like;
    }
    $sql = "SELECT l.*, u.name seller_name, c.name category_name FROM listings l JOIN users u ON u.id=l.seller_id JOIN categories c ON c.id=l.category_id WHERE l.status IN ('active','pending_review') AND (" . implode(' OR ', $conditions) . ') ORDER BY l.created_at DESC';
    return db_rows($conn, $sql, $types, $params);
}

// --- Moderator: Messaging ---
function moderator_send_message(mysqli $conn, int $sender, int $receiver, ?int $listing_id, string $message): bool {
    return db_execute($conn, 'INSERT INTO messages (sender_id,receiver_id,listing_id,message) VALUES (?,?,?,?)', 'iiis', [$sender, $receiver, $listing_id, $message]);
}

function moderator_messages_sent(mysqli $conn, int $sender): array {
    return db_rows($conn, 'SELECT m.*, u.name receiver_name, u.role receiver_role, l.title listing_title FROM messages m JOIN users u ON u.id=m.receiver_id LEFT JOIN listings l ON l.id=m.listing_id WHERE m.sender_id=? ORDER BY m.created_at DESC', 'i', [$sender]);
}

function moderator_users_list(mysqli $conn, string $keyword = ''): array {
    $like = '%' . $keyword . '%';
    return db_rows($conn, "SELECT id, name, email, role FROM users WHERE role IN ('buyer','seller') AND (name LIKE ? OR email LIKE ?) ORDER BY name LIMIT 50", 'ss', [$like, $like]);
}

// --- Moderator: Trust Score Detail ---
function moderator_user_detail(mysqli $conn, int $id): ?array {
    return db_row($conn, 'SELECT u.*, (SELECT COUNT(*) FROM warnings w WHERE w.user_id=u.id) warning_count, (SELECT COUNT(*) FROM user_reports r WHERE r.reported_user_id=u.id) report_count, (SELECT COUNT(*) FROM listings WHERE seller_id=u.id) listing_count FROM users u WHERE u.id=?', 'i', [$id]);
}

function moderator_user_warnings_history(mysqli $conn, int $user): array {
    return db_rows($conn, 'SELECT w.*, m.name issued_by_name FROM warnings w JOIN users m ON m.id=w.issued_by WHERE w.user_id=? ORDER BY w.created_at DESC', 'i', [$user]);
}

function moderator_user_reports_history(mysqli $conn, int $user): array {
    return db_rows($conn, 'SELECT r.*, u.name reporter_name FROM user_reports r JOIN users u ON u.id=r.reporter_id WHERE r.reported_user_id=? ORDER BY r.created_at DESC', 'i', [$user]);
}

// --- Moderator: Activity Report with Period ---
function moderator_activity_period(mysqli $conn, string $from, string $to): ?array {
    $to_end = $to . ' 23:59:59';
    return db_row($conn,
        "SELECT
            (SELECT COUNT(*) FROM listings WHERE status IN ('active','rejected') AND created_at BETWEEN ? AND ?) AS reviewed,
            (SELECT COUNT(*) FROM listings WHERE status='active' AND created_at BETWEEN ? AND ?) AS approved,
            (SELECT COUNT(*) FROM listings WHERE status='rejected' AND created_at BETWEEN ? AND ?) AS rejected_count,
            (SELECT COUNT(*) FROM listing_reports WHERE status<>'pending' AND created_at BETWEEN ? AND ?) AS listing_reports_done,
            (SELECT COUNT(*) FROM user_reports WHERE status<>'pending' AND created_at BETWEEN ? AND ?) AS user_reports_done,
            (SELECT COUNT(*) FROM warnings WHERE created_at BETWEEN ? AND ?) AS warnings_issued",
        'ssssssssssss',
        [$from, $to_end, $from, $to_end, $from, $to_end, $from, $to_end, $from, $to_end, $from, $to_end]
    );
}

function admin_verifications(mysqli $conn, string $status = 'pending'): array { return db_rows($conn, 'SELECT r.*, u.name, u.email, u.phone FROM seller_verification_requests r JOIN users u ON u.id=r.user_id WHERE r.status=? ORDER BY r.submitted_at', 's', [$status]); }
function admin_decide_verification(mysqli $conn, int $id, string $status, int $admin, string $reason = ''): bool { $req = db_row($conn, 'SELECT * FROM seller_verification_requests WHERE id=?', 'i', [$id]); if (!$req) return false; db_execute($conn, 'UPDATE seller_verification_requests SET status=?, reviewed_by=?, reject_reason=?, reviewed_at=NOW() WHERE id=?', 'sisi', [$status, $admin, $reason, $id]); if ($status === 'approved') db_execute($conn, "UPDATE users SET role='seller', seller_verified=1 WHERE id=?", 'i', [$req['user_id']]); return true; }
function admin_dashboard_stats(mysqli $conn): ?array { return db_row($conn, "SELECT (SELECT COUNT(*) FROM users) users, (SELECT COUNT(*) FROM listings WHERE status='active') active_listings, (SELECT COUNT(*) FROM bids WHERE DATE(created_at)=CURDATE()) bids_today, (SELECT COALESCE(SUM(commission_amount),0) FROM platform_fees WHERE MONTH(created_at)=MONTH(CURDATE())) commission_month, (SELECT COUNT(*) FROM seller_verification_requests WHERE status='pending') pending_sellers"); }
function admin_financials(mysqli $conn): array { return db_rows($conn, 'SELECT DATE(created_at) day, SUM(commission_amount) commission, SUM(final_price) gross FROM platform_fees GROUP BY DATE(created_at) ORDER BY day DESC'); }
function admin_set_default_rate(mysqli $conn, float $rate): bool { db_execute($conn, 'UPDATE commission_rates SET is_default=0 WHERE is_default=1'); return db_execute($conn, 'INSERT INTO commission_rates (rate,is_default) VALUES (?,1)', 'd', [$rate]); }
function admin_bid_analytics(mysqli $conn): array { return db_rows($conn, 'SELECT DATE(created_at) day, COUNT(*) bids, AVG(amount) average_bid FROM bids GROUP BY DATE(created_at) ORDER BY day DESC'); }
function admin_announcement_add(mysqli $conn, string $title, string $message, int $user): bool { return db_execute($conn, 'INSERT INTO announcements (title,message,posted_by) VALUES (?,?,?)', 'ssi', [$title, $message, $user]); }
function admin_announcements(mysqli $conn): array { return db_rows($conn, 'SELECT * FROM announcements ORDER BY created_at DESC'); }

function buyer_notifications(mysqli $conn, int $buyer): array {
   $outbid = db_rows($conn, "SELECT l.id, l.title, 'outbid' type, 'You have been outbid on this auction.' message FROM bids b JOIN listings l ON l.id=b.listing_id WHERE b.buyer_id=? AND l.status='active' GROUP BY l.id, l.title HAVING MAX(b.amount) < MAX(l.current_bid)", 'i', [$buyer]);
    $endingSoon = db_rows($conn, "SELECT l.id, l.title, 'ending_soon' type, 'This auction ends within 1 hour.' message FROM watchlist w JOIN listings l ON l.id=w.listing_id WHERE w.buyer_id=? AND l.status='active' AND l.end_datetime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 HOUR)", 'i', [$buyer]);
    $won = db_rows($conn, "SELECT l.id, l.title, 'won' type, 'You won this auction.' message FROM listings l JOIN bids b ON b.id=l.winner_bid_id WHERE b.buyer_id=? AND l.status='ended'", 'i', [$buyer]);
    return array_merge($outbid, $endingSoon, $won);
}
?>