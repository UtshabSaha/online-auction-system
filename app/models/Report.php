<?php
require_once __DIR__ . '/BaseModel.php';
class Report extends BaseModel {
    public function createListingReport($listing,$reporter,$reason,$description) { return $this->execute('INSERT INTO listing_reports (listing_id,reporter_id,reason,description) VALUES (?,?,?,?)','iiss',[$listing,$reporter,$reason,$description]); }
    public function createUserReport($reporter,$reported,$reason,$description) { return $this->execute('INSERT INTO user_reports (reporter_id,reported_user_id,reason,description) VALUES (?,?,?,?)','iiss',[$reporter,$reported,$reason,$description]); }
    public function listingReports() { return $this->rows('SELECT r.*, l.title, u.name reporter_name FROM listing_reports r JOIN listings l ON l.id=r.listing_id JOIN users u ON u.id=r.reporter_id ORDER BY r.created_at DESC'); }
    public function userReports() { return $this->rows('SELECT r.*, a.name reporter_name, b.name reported_name FROM user_reports r JOIN users a ON a.id=r.reporter_id JOIN users b ON b.id=r.reported_user_id ORDER BY r.created_at DESC'); }
    public function updateListingReport($id,$status,$note) { return $this->execute('UPDATE listing_reports SET status=?, moderator_note=? WHERE id=?','ssi',[$status,$note,$id]); }
    public function updateUserReport($id,$status,$note) { return $this->execute('UPDATE user_reports SET status=?, moderator_note=? WHERE id=?','ssi',[$status,$note,$id]); }
    public function escalated() { return $this->rows("SELECT r.*, a.name reporter_name, b.name reported_name FROM user_reports r JOIN users a ON a.id=r.reporter_id JOIN users b ON b.id=r.reported_user_id WHERE r.status='escalated'"); }
}
?>
