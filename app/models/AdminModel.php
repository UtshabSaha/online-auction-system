<?php
require_once __DIR__ . '/BaseModel.php';
class AdminModel extends BaseModel {
    public function verifications($status='pending') { return $this->rows('SELECT r.*, u.name, u.email, u.phone FROM seller_verification_requests r JOIN users u ON u.id=r.user_id WHERE r.status=? ORDER BY r.submitted_at','s',[$status]); }
    public function decideVerification($id,$status,$admin,$reason='') {
        $req = $this->row('SELECT * FROM seller_verification_requests WHERE id=?','i',[$id]);
        if (!$req) return false;
        $this->execute('UPDATE seller_verification_requests SET status=?, reviewed_by=?, reject_reason=?, reviewed_at=NOW() WHERE id=?','sisi',[$status,$admin,$reason,$id]);
        if ($status === 'approved') $this->execute("UPDATE users SET role='seller', seller_verified=1 WHERE id=?",'i',[$req['user_id']]);
        return true;
    }
    public function dashboardStats() { return $this->row("SELECT (SELECT COUNT(*) FROM users) users, (SELECT COUNT(*) FROM listings WHERE status='active') active_listings, (SELECT COUNT(*) FROM bids WHERE DATE(created_at)=CURDATE()) bids_today, (SELECT COALESCE(SUM(commission_amount),0) FROM platform_fees WHERE MONTH(created_at)=MONTH(CURDATE())) commission_month, (SELECT COUNT(*) FROM seller_verification_requests WHERE status='pending') pending_sellers"); }
    public function financials() { return $this->rows('SELECT DATE(created_at) day, SUM(commission_amount) commission, SUM(final_price) gross FROM platform_fees GROUP BY DATE(created_at) ORDER BY day DESC'); }
    public function setDefaultRate($rate) { $this->execute('UPDATE commission_rates SET is_default=0 WHERE is_default=1'); return $this->execute('INSERT INTO commission_rates (rate,is_default) VALUES (?,1)','d',[$rate]); }
}
?>
