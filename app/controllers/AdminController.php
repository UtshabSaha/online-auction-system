<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/AdminModel.php';
require_once __DIR__ . '/../models/Report.php';
class AdminController extends BaseController {
    public function dashboard(){ require_role('admin'); $stats=(new AdminModel($this->conn))->dashboardStats(); $userStats=(new User($this->conn))->stats(); $this->view('admin/dashboard',compact('stats','userStats')); }
    public function sellerVerifications(){ require_role('admin'); $admin=new AdminModel($this->conn); if($_SERVER['REQUEST_METHOD']==='POST') $admin->decideVerification((int)$_POST['request_id'],$_POST['status'],current_user_id(),trim($_POST['reason']??'')); $rows=$admin->verifications('pending'); $this->view('admin/verifications',compact('rows')); }
    public function users(){ require_role('admin'); $u=new User($this->conn); if($_SERVER['REQUEST_METHOD']==='POST'){ if($_POST['action']==='active' && (int)$_POST['id']!==current_user_id()) $u->setActive((int)$_POST['id'],(int)$_POST['is_active']); if($_POST['action']==='role') $u->setRole((int)$_POST['id'],$_POST['role']); if($_POST['action']==='revoke') $u->revokeSeller((int)$_POST['id']); } $rows=$u->all($_GET['q']??''); $this->view('admin/users',compact('rows')); }
    public function listings(){ require_role('admin'); $m=new Listing($this->conn); if($_SERVER['REQUEST_METHOD']==='POST') $m->setStatus((int)$_POST['listing_id'],'cancelled',trim($_POST['reason'])); $rows=$m->allAdmin($_GET['status']??''); $this->view('admin/listings',compact('rows')); }
    public function commissions(){ require_role('admin'); $admin=new AdminModel($this->conn); $message=''; if($_SERVER['REQUEST_METHOD']==='POST'){ $rate=(float)$_POST['rate']; if($rate>=0&&$rate<=100){$admin->setDefaultRate($rate);$message='Commission rate saved.';} else $message='Rate must be between 0 and 100.'; } $this->view('admin/commissions',compact('message')); }
    public function financialReports(){ require_role('admin'); $rows=(new AdminModel($this->conn))->financials(); $this->view('admin/financials',compact('rows')); }
    public function analytics(){ require_role('admin'); $rows=(new class($this->conn) extends BaseModel{ public function get(){return $this->rows('SELECT DATE(created_at) day, COUNT(*) bids, AVG(amount) average_bid FROM bids GROUP BY DATE(created_at) ORDER BY day DESC');}})->get(); $this->view('admin/analytics',compact('rows')); }
    public function featured(){ require_role('admin'); $m=new Listing($this->conn); if($_SERVER['REQUEST_METHOD']==='POST') $m->setFeatured((int)$_POST['listing_id'],(int)$_POST['featured']); $rows=$m->allAdmin(); $this->view('admin/featured',compact('rows')); }
    public function announcements(){ require_role('admin'); $model = new class($this->conn) extends BaseModel { public function add($t,$m,$u){return $this->execute('INSERT INTO announcements (title,message,posted_by) VALUES (?,?,?)','ssi',[$t,$m,$u]);} public function all(){return $this->rows('SELECT * FROM announcements ORDER BY created_at DESC');} }; if($_SERVER['REQUEST_METHOD']==='POST') $model->add(trim($_POST['title']),trim($_POST['message']),current_user_id()); $rows=$model->all(); $this->view('admin/announcements',compact('rows')); }
}
?>
