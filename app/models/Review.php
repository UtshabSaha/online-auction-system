<?php
require_once __DIR__ . '/BaseModel.php';
class Review extends BaseModel {
    public function create($listing,$reviewer,$reviewee,$rating,$text) { return $this->execute('INSERT INTO reviews (listing_id,reviewer_id,reviewee_id,rating,review_text) VALUES (?,?,?,?,?)','iiiis',[$listing,$reviewer,$reviewee,$rating,$text]); }
    public function received($user) { return $this->rows('SELECT r.*, u.name reviewer_name, l.title FROM reviews r JOIN users u ON u.id=r.reviewer_id JOIN listings l ON l.id=r.listing_id WHERE r.reviewee_id=? ORDER BY r.created_at DESC','i',[$user]); }
    public function sent($user) { return $this->rows('SELECT r.*, u.name reviewee_name, l.title FROM reviews r JOIN users u ON u.id=r.reviewee_id JOIN listings l ON l.id=r.listing_id WHERE r.reviewer_id=? ORDER BY r.created_at DESC','i',[$user]); }
    public function respond($id,$user,$text) { return $this->execute('UPDATE reviews SET response_text=? WHERE id=? AND reviewee_id=?','sii',[$text,$id,$user]); }
}
?>
