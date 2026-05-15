<?php
require_once __DIR__ . '/BaseModel.php';
class SellerModel extends BaseModel {
    public function requestVerification($user,$motivation,$doc) { return $this->execute('INSERT INTO seller_verification_requests (user_id,motivation,id_document_path) VALUES (?,?,?)','iss',[$user,$motivation,$doc]); }
    public function requestByUser($user) { return $this->row('SELECT * FROM seller_verification_requests WHERE user_id=? ORDER BY submitted_at DESC LIMIT 1','i',[$user]); }
    public function createTemplate($seller,$title,$desc,$cat,$condition,$price) { return $this->execute('INSERT INTO auction_templates (seller_id,title,description,category_id,item_condition,starting_price) VALUES (?,?,?,?,?,?)','issisd',[$seller,$title,$desc,$cat,$condition,$price]); }
    public function templates($seller) { return $this->rows('SELECT t.*, c.name category_name FROM auction_templates t JOIN categories c ON c.id=t.category_id WHERE seller_id=?','i',[$seller]); }
    public function deleteTemplate($id,$seller) { return $this->execute('DELETE FROM auction_templates WHERE id=? AND seller_id=?','ii',[$id,$seller]); }
    public function ended($seller) { return $this->rows("SELECT l.*, u.name winner_name, u.email winner_email, u.phone winner_phone FROM listings l LEFT JOIN bids b ON b.id=l.winner_bid_id LEFT JOIN users u ON u.id=b.buyer_id WHERE l.seller_id=? AND (l.status='ended' OR l.end_datetime<NOW()) ORDER BY l.end_datetime DESC",'i',[$seller]); }
    public function analytics($seller) { return $this->row("SELECT COUNT(*) total_auctions, SUM(status='ended' AND winner_bid_id IS NOT NULL) sold, AVG(CASE WHEN winner_bid_id IS NOT NULL THEN current_bid END) avg_sale, COALESCE(SUM(CASE WHEN winner_bid_id IS NOT NULL THEN current_bid ELSE 0 END),0) revenue FROM listings WHERE seller_id=?",'i',[$seller]); }
}
?>
