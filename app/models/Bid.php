<?php
require_once __DIR__ . '/BaseModel.php';
class Bid extends BaseModel {
    public function history($listingId) { return $this->rows('SELECT b.*, u.name buyer_name FROM bids b JOIN users u ON u.id=b.buyer_id WHERE b.listing_id=? ORDER BY b.amount DESC, b.created_at DESC','i',[$listingId]); }
    public function countForListing($listingId) { $r=$this->row('SELECT COUNT(*) c FROM bids WHERE listing_id=?','i',[$listingId]); return (int)($r['c'] ?? 0); }
    public function place($listingId,$buyerId,$amount,$auto=0) {
        $listing = $this->row('SELECT * FROM listings WHERE id=?','i',[$listingId]);
        if (!$listing) return ['success'=>false,'message'=>'Listing not found'];
        if ($listing['status'] !== 'active') return ['success'=>false,'message'=>'Auction is not active'];
        if ((int)$listing['seller_id'] === (int)$buyerId) return ['success'=>false,'message'=>'You cannot bid on your own listing'];
        if (strtotime($listing['end_datetime']) <= time()) return ['success'=>false,'message'=>'Auction already ended'];
        if ((float)$amount <= (float)$listing['current_bid']) return ['success'=>false,'message'=>'Bid must be greater than current bid'];
        $bidId = $this->insert('INSERT INTO bids (listing_id,buyer_id,amount,is_auto_bid) VALUES (?,?,?,?)','iidi',[$listingId,$buyerId,$amount,$auto]);
        $this->execute('UPDATE listings SET current_bid=? WHERE id=?','di',[$amount,$listingId]);
        $this->processAutoBids($listingId,$buyerId,$amount);
        $updated = $this->row('SELECT current_bid FROM listings WHERE id=?','i',[$listingId]);
        return ['success'=>true,'message'=>'Bid placed successfully','bid_id'=>$bidId,'current_bid'=>$updated['current_bid']];
    }
    public function setAutoBid($listingId,$buyerId,$maxAmount) {
        $listing = $this->row('SELECT current_bid FROM listings WHERE id=? AND status=\'active\'','i',[$listingId]);
        if (!$listing) return ['success'=>false,'message'=>'Active listing not found'];
        if ((float)$maxAmount <= (float)$listing['current_bid']) return ['success'=>false,'message'=>'Auto bid max must be higher than current bid'];
        $this->execute('INSERT INTO auto_bids (listing_id,buyer_id,max_amount,is_active) VALUES (?,?,?,1) ON DUPLICATE KEY UPDATE max_amount=VALUES(max_amount), is_active=1','iid',[$listingId,$buyerId,$maxAmount]);
        return ['success'=>true,'message'=>'Auto bid saved'];
    }
    private function processAutoBids($listingId,$latestBuyer,$latestAmount) {
        $auto = $this->row('SELECT * FROM auto_bids WHERE listing_id=? AND buyer_id<>? AND is_active=1 AND max_amount>? ORDER BY max_amount DESC LIMIT 1','iid',[$listingId,$latestBuyer,$latestAmount]);
        if (!$auto) return;
        $newAmount = min((float)$auto['max_amount'], (float)$latestAmount + 100);
        $this->insert('INSERT INTO bids (listing_id,buyer_id,amount,is_auto_bid) VALUES (?,?,?,1)','iid',[$listingId,$auto['buyer_id'],$newAmount]);
        $this->execute('UPDATE listings SET current_bid=? WHERE id=?','di',[$newAmount,$listingId]);
    }
    public function byBuyer($buyer) { return $this->rows("SELECT l.*, MAX(b.amount) my_highest, CASE WHEN l.winner_bid_id IN (SELECT id FROM bids WHERE buyer_id=?) THEN 'Won' WHEN l.status='ended' THEN 'Lost' WHEN MAX(b.amount)=l.current_bid THEN 'Leading' ELSE 'Outbid' END bid_status FROM bids b JOIN listings l ON l.id=b.listing_id WHERE b.buyer_id=? GROUP BY l.id ORDER BY MAX(b.created_at) DESC",'ii',[$buyer,$buyer]); }
    public function spending($buyer) { return $this->row('SELECT COUNT(*) total_bids, COALESCE(SUM(CASE WHEN l.winner_bid_id=b.id THEN b.amount ELSE 0 END),0) total_spent, SUM(CASE WHEN l.winner_bid_id=b.id THEN 1 ELSE 0 END) wins FROM bids b JOIN listings l ON l.id=b.listing_id WHERE b.buyer_id=?','i',[$buyer]); }
}
?>
