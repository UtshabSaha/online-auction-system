<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/ModeratorModel.php';
class ModeratorController extends BaseController {
    public function dashboard(){ require_role('moderator'); $stats=(new ModeratorModel($this->conn))->dashboard(); $this->view('moderator/dashboard',compact('stats')); }
    public function pendingListings(){ require_role('moderator'); $rows=(new Listing($this->conn))->pending(); $this->view('moderator/pending',compact('rows')); }
    public function listingReports(){ require_role('moderator'); $report=new Report($this->conn); if($_SERVER['REQUEST_METHOD']==='POST') $report->updateListingReport((int)$_POST['report_id'],$_POST['status'],trim($_POST['moderator_note'])); $rows=$report->listingReports(); $this->view('moderator/listing_reports',compact('rows')); }
    public function userReports(){ require_role('moderator'); $report=new Report($this->conn); $mod=new ModeratorModel($this->conn); if($_SERVER['REQUEST_METHOD']==='POST'){ $report->updateUserReport((int)$_POST['report_id'],$_POST['status'],trim($_POST['moderator_note'])); if($_POST['status']==='resolved' && !empty($_POST['warn_user_id'])) $mod->warn((int)$_POST['warn_user_id'],current_user_id(),trim($_POST['moderator_note'])); } $rows=$report->userReports(); $this->view('moderator/user_reports',compact('rows')); }
    public function warnings(){ require_role('moderator'); $mod=new ModeratorModel($this->conn); if($_SERVER['REQUEST_METHOD']==='POST') $mod->warn((int)$_POST['user_id'],current_user_id(),trim($_POST['reason'])); $rows=$mod->warnings(); $this->view('moderator/warnings',compact('rows')); }
    public function categories(){ require_role('moderator'); $mod=new ModeratorModel($this->conn); $listing=new Listing($this->conn); if($_SERVER['REQUEST_METHOD']==='POST'){ if($_POST['action']==='add')$mod->addCategory(trim($_POST['name']),trim($_POST['description']),(int)$_POST['parent_id']?:null); if($_POST['action']==='rename')$mod->renameCategory((int)$_POST['id'],trim($_POST['name']),trim($_POST['description'])); if($_POST['action']==='merge')$mod->mergeCategory((int)$_POST['source_id'],(int)$_POST['dest_id']); if($_POST['action']==='delete')$mod->deleteEmptyCategory((int)$_POST['id']); } $rows=$listing->categories(); $this->view('moderator/categories',compact('rows')); }
    public function activityReport(){ require_role('moderator'); $stats=(new ModeratorModel($this->conn))->activity(); $this->view('moderator/activity',compact('stats')); }
    public function trustScore(){ require_role('moderator'); $rows=(new class($this->conn) extends BaseModel{ public function get(){return $this->rows('SELECT u.*, (SELECT COUNT(*) FROM warnings w WHERE w.user_id=u.id) warnings, (SELECT COUNT(*) FROM user_reports r WHERE r.reported_user_id=u.id) reports FROM users u ORDER BY reputation_score DESC');}})->get(); $this->view('moderator/trust',compact('rows')); }
}
?>
