<?php
require_once __DIR__ . '/BaseModel.php';
class ModeratorModel extends BaseModel {
    public function dashboard() { return $this->row("SELECT (SELECT COUNT(*) FROM listings WHERE status='pending_review') pending_listings, (SELECT COUNT(*) FROM listing_reports WHERE status='pending') listing_reports, (SELECT COUNT(*) FROM user_reports WHERE status='pending') user_reports, (SELECT COUNT(*) FROM warnings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) warnings_week"); }
    public function warn($user,$by,$reason) { return $this->execute('INSERT INTO warnings (user_id,issued_by,reason) VALUES (?,?,?)','iis',[$user,$by,$reason]); }
    public function warnings() { return $this->rows('SELECT w.*, u.name user_name, m.name issued_by_name FROM warnings w JOIN users u ON u.id=w.user_id JOIN users m ON m.id=w.issued_by ORDER BY w.created_at DESC'); }
    public function addCategory($name,$desc,$parent) { return $this->execute('INSERT INTO categories (name,description,parent_id) VALUES (?,?,?)','ssi',[$name,$desc,$parent]); }
    public function renameCategory($id,$name,$desc) { return $this->execute('UPDATE categories SET name=?, description=? WHERE id=?','ssi',[$name,$desc,$id]); }
    public function deleteEmptyCategory($id) { return $this->execute('DELETE FROM categories WHERE id=? AND NOT EXISTS (SELECT 1 FROM listings WHERE category_id=?)','ii',[$id,$id]); }
    public function mergeCategory($source,$dest) { $this->execute('UPDATE listings SET category_id=? WHERE category_id=?','ii',[$dest,$source]); return $this->execute('DELETE FROM categories WHERE id=?','i',[$source]); }
    public function activity() { return $this->row("SELECT (SELECT COUNT(*) FROM listings WHERE status IN ('active','rejected')) reviewed, (SELECT COUNT(*) FROM listings WHERE status='active') approved, (SELECT COUNT(*) FROM listing_reports WHERE status<>'pending') listing_reports, (SELECT COUNT(*) FROM user_reports WHERE status<>'pending') user_reports, (SELECT COUNT(*) FROM warnings) warnings"); }
}
?>
