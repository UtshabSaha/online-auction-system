<?php
require_once __DIR__ . '/BaseModel.php';
class User extends BaseModel {
    public function findByEmail($email) { return $this->row('SELECT * FROM users WHERE email = ?', 's', [$email]); }
    public function find($id) { return $this->row('SELECT * FROM users WHERE id = ?', 'i', [$id]); }
    public function create($name,$email,$hash,$phone,$bio) {
        return $this->insert('INSERT INTO users (name,email,password_hash,phone,bio,role) VALUES (?,?,?,?,?,\'buyer\')', 'sssss', [$name,$email,$hash,$phone,$bio]);
    }
    public function updateProfile($id,$name,$phone,$bio,$pic=null) {
        if ($pic) return $this->execute('UPDATE users SET name=?, phone=?, bio=?, profile_pic=? WHERE id=?', 'ssssi', [$name,$phone,$bio,$pic,$id]);
        return $this->execute('UPDATE users SET name=?, phone=?, bio=? WHERE id=?', 'sssi', [$name,$phone,$bio,$id]);
    }
    public function changePassword($id,$hash) { return $this->execute('UPDATE users SET password_hash=? WHERE id=?', 'si', [$hash,$id]); }
    public function all($keyword='') {
        $like = '%' . $keyword . '%';
        return $this->rows('SELECT * FROM users WHERE name LIKE ? OR email LIKE ? OR role LIKE ? ORDER BY created_at DESC', 'sss', [$like,$like,$like]);
    }
    public function setActive($id,$active) { return $this->execute('UPDATE users SET is_active=? WHERE id=?', 'ii', [$active,$id]); }
    public function setRole($id,$role) { return $this->execute('UPDATE users SET role=? WHERE id=?', 'si', [$role,$id]); }
    public function approveSeller($id) { return $this->execute('UPDATE users SET role=\'seller\', seller_verified=1 WHERE id=?', 'i', [$id]); }
    public function revokeSeller($id) { return $this->execute('UPDATE users SET seller_verified=0, role=\'buyer\' WHERE id=?', 'i', [$id]); }
    public function stats() {
        return $this->row("SELECT COUNT(*) total, SUM(role='buyer') buyers, SUM(role='seller') sellers, SUM(role='moderator') moderators, SUM(role='admin') admins FROM users");
    }
}
?>
