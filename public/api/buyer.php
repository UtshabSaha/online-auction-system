<?php
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/Listing.php';
require_once __DIR__ . '/../../app/models/Bid.php';
require_once __DIR__ . '/../../app/models/BaseModel.php';
$action = $_GET['action'] ?? '';

if ($action === 'search') {
    $m = new Listing($conn);
    $m->closeExpiredAuctions();
    $rows = $m->search($_GET['q']??'', $_GET['category']??'', $_GET['condition']??'', $_GET['min']??'', $_GET['max']??'');
    $html = '';
    foreach ($rows as $l) {
        $img = e($l['image_path'] ?: 'assets/images/no-image.png');
        $html .= '<div class="card auction-card"><img src="'.$img.'"><h3>'.e($l['title']).'</h3><p>'.e($l['category_name']).' · '.e($l['item_condition']).'</p><p><strong>Current bid:</strong> '.e($l['current_bid']).'</p><p><strong>Ends:</strong> '.e($l['end_datetime']).'</p><a class="btn" href="index.php?page=auction&id='.$l['id'].'">View Auction</a></div>';
    }
    json_response(['success'=>true,'html'=>$html]);
}

require_login();
$bid = new Bid($conn);
if ($action === 'place_bid') {
    $amount = (float)($_POST['amount'] ?? 0);
    json_response($bid->place((int)$_POST['listing_id'], current_user_id(), $amount, 0));
}
if ($action === 'auto_bid') {
    json_response($bid->setAutoBid((int)$_POST['listing_id'], current_user_id(), (float)$_POST['max_amount']));
}
if ($action === 'bid_history') {
    $listingId = (int)($_GET['listing_id'] ?? 0);
    $listing = (new Listing($conn))->find($listingId);
    json_response(['success'=>true,'current_bid'=>$listing['current_bid'] ?? 0,'bids'=>$bid->history($listingId)]);
}
if ($action === 'watchlist') {
    $listing = (int)$_POST['listing_id']; $user = current_user_id();
    $model = new class($conn) extends BaseModel {
        public function toggle($u,$l){ $exists=$this->row('SELECT id FROM watchlist WHERE buyer_id=? AND listing_id=?','ii',[$u,$l]); if($exists){$this->execute('DELETE FROM watchlist WHERE id=?','i',[$exists['id']]); return 'Removed from watchlist';} $this->execute('INSERT INTO watchlist (buyer_id,listing_id) VALUES (?,?)','ii',[$u,$l]); return 'Added to watchlist'; }
    };
    json_response(['success'=>true,'message'=>$model->toggle($user,$listing)]);
}
json_response(['success'=>false,'message'=>'Unknown action']);
?>
