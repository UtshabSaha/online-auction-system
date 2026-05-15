<?php
require_once __DIR__ . '/BaseModel.php';
class Listing extends BaseModel {
    public function closeExpiredAuctions() {
        $expired = $this->rows("SELECT id, seller_id, reserve_price FROM listings WHERE status='active' AND end_datetime < NOW()");
        foreach ($expired as $listing) {
            $top = $this->row('SELECT id, amount FROM bids WHERE listing_id=? ORDER BY amount DESC LIMIT 1', 'i', [$listing['id']]);
            if ($top && ($listing['reserve_price'] === null || (float)$top['amount'] >= (float)$listing['reserve_price'])) {
                $this->execute("UPDATE listings SET status='ended', winner_bid_id=? WHERE id=?", 'ii', [$top['id'], $listing['id']]);
                $rateRow = $this->row('SELECT rate FROM commission_rates WHERE is_default=1 ORDER BY id DESC LIMIT 1');
                $rate = (float)($rateRow['rate'] ?? 5.00);
                $commission = ((float)$top['amount'] * $rate) / 100;
                $this->execute('INSERT INTO platform_fees (listing_id,seller_id,final_price,commission_rate,commission_amount) VALUES (?,?,?,?,?)', 'iiddd', [$listing['id'], $listing['seller_id'], $top['amount'], $rate, $commission]);
            } else {
                $this->execute("UPDATE listings SET status='ended' WHERE id=?", 'i', [$listing['id']]);
            }
        }
    }
    public function categories() { return $this->rows('SELECT * FROM categories ORDER BY name'); }
    public function search($keyword='',$category='',$condition='',$min='',$max='') {
        $sql = "SELECT l.*, u.name seller_name, c.name category_name, (SELECT image_path FROM listing_images WHERE listing_id=l.id ORDER BY display_order LIMIT 1) image_path FROM listings l JOIN users u ON u.id=l.seller_id JOIN categories c ON c.id=l.category_id WHERE l.status='active'";
        $types=''; $params=[];
        if ($keyword !== '') { $sql .= ' AND (l.title LIKE ? OR l.description LIKE ?)'; $types.='ss'; $like='%'.$keyword.'%'; $params[]=$like; $params[]=$like; }
        if ($category !== '') { $sql .= ' AND l.category_id=?'; $types.='i'; $params[]=(int)$category; }
        if ($condition !== '') { $sql .= ' AND l.item_condition=?'; $types.='s'; $params[]=$condition; }
        if ($min !== '') { $sql .= ' AND l.current_bid>=?'; $types.='d'; $params[]=(float)$min; }
        if ($max !== '') { $sql .= ' AND l.current_bid<=?'; $types.='d'; $params[]=(float)$max; }
        $sql .= ' ORDER BY l.end_datetime ASC';
        return $this->rows($sql,$types,$params);
    }
    public function find($id) { return $this->row('SELECT l.*, u.name seller_name, u.email seller_email, u.phone seller_phone, u.reputation_score, c.name category_name FROM listings l JOIN users u ON u.id=l.seller_id JOIN categories c ON c.id=l.category_id WHERE l.id=?', 'i', [$id]); }
    public function images($id) { return $this->rows('SELECT * FROM listing_images WHERE listing_id=? ORDER BY display_order', 'i', [$id]); }
    public function create($seller,$category,$title,$description,$condition,$start,$reserve,$end) {
        return $this->insert('INSERT INTO listings (seller_id,category_id,title,description,item_condition,starting_price,reserve_price,current_bid,end_datetime,status) VALUES (?,?,?,?,?,?,?,?,?,\'pending_review\')','iisssddds',[$seller,$category,$title,$description,$condition,$start,$reserve,$start,$end]);
    }
    public function addImage($listing,$path,$order) { return $this->execute('INSERT INTO listing_images (listing_id,image_path,display_order) VALUES (?,?,?)','isi',[$listing,$path,$order]); }
    public function bySeller($seller) { return $this->rows('SELECT l.*, c.name category_name, COUNT(b.id) bid_count FROM listings l JOIN categories c ON c.id=l.category_id LEFT JOIN bids b ON b.listing_id=l.id WHERE l.seller_id=? GROUP BY l.id ORDER BY l.created_at DESC','i',[$seller]); }
    public function updateIfNoBids($id,$seller,$title,$description,$condition,$start,$reserve,$end) {
        return $this->execute('UPDATE listings SET title=?, description=?, item_condition=?, starting_price=?, reserve_price=?, current_bid=?, end_datetime=? WHERE id=? AND seller_id=? AND (SELECT COUNT(*) FROM bids WHERE bids.listing_id=listings.id)=0','sssdddsii',[$title,$description,$condition,$start,$reserve,$start,$end,$id,$seller]);
    }
    public function cancelIfNoBids($id,$seller,$reason) { return $this->execute("UPDATE listings SET status='cancelled', cancel_reason=? WHERE id=? AND seller_id=? AND (SELECT COUNT(*) FROM bids WHERE bids.listing_id=listings.id)=0",'sii',[$reason,$id,$seller]); }
    public function pending() { return $this->rows("SELECT l.*, u.name seller_name, c.name category_name FROM listings l JOIN users u ON u.id=l.seller_id JOIN categories c ON c.id=l.category_id WHERE l.status='pending_review' ORDER BY l.created_at"); }
    public function setStatus($id,$status,$reason='') {
        if ($status === 'rejected') return $this->execute('UPDATE listings SET status=?, rejection_reason=? WHERE id=?','ssi',[$status,$reason,$id]);
        if ($status === 'cancelled') return $this->execute('UPDATE listings SET status=?, cancel_reason=? WHERE id=?','ssi',[$status,$reason,$id]);
        return $this->execute('UPDATE listings SET status=? WHERE id=?','si',[$status,$id]);
    }
    public function allAdmin($status='') { return $status ? $this->rows('SELECT l.*, u.name seller_name, c.name category_name FROM listings l JOIN users u ON u.id=l.seller_id JOIN categories c ON c.id=l.category_id WHERE l.status=? ORDER BY l.created_at DESC','s',[$status]) : $this->rows('SELECT l.*, u.name seller_name, c.name category_name FROM listings l JOIN users u ON u.id=l.seller_id JOIN categories c ON c.id=l.category_id ORDER BY l.created_at DESC'); }
    public function setFeatured($id,$featured) { return $this->execute('UPDATE listings SET is_featured=? WHERE id=?','ii',[$featured,$id]); }
}
?>
