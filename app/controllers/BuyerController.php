<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Bid.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Report.php';
class BuyerController extends BaseController {
    public function browse() { $m=new Listing($this->conn); $m->closeExpiredAuctions(); $listings=$m->search($_GET['q']??'',$_GET['category']??'',$_GET['condition']??'',$_GET['min']??'',$_GET['max']??''); $categories=$m->categories(); $this->view('public/browse',compact('listings','categories')); }
    public function auction() { $m=new Listing($this->conn); $b=new Bid($this->conn); $listing=$m->find((int)($_GET['id']??0)); $images=$m->images($listing['id']??0); $bids=$b->history($listing['id']??0); $this->view('public/auction',compact('listing','images','bids')); }
    public function dashboard() { require_role(['buyer','seller','moderator','admin']); $m=new Listing($this->conn); $b=new Bid($this->conn); $listings=$m->search(); $myBids=is_logged_in()?$b->byBuyer(current_user_id()):[]; $this->view('buyer/dashboard',compact('listings','myBids')); }
    public function watchlist() { require_login(); $rows=(new class($this->conn) extends BaseModel { public function get($u){return $this->rows('SELECT w.*, l.title,l.current_bid,l.end_datetime,l.status FROM watchlist w JOIN listings l ON l.id=w.listing_id WHERE w.buyer_id=?','i',[$u]);}})->get(current_user_id()); $this->view('buyer/watchlist',compact('rows')); }
    public function myBids() { require_login(); $rows=(new Bid($this->conn))->byBuyer(current_user_id()); $this->view('buyer/my_bids',compact('rows')); }
    public function wonAuctions() { require_login(); $rows=(new class($this->conn) extends BaseModel { public function get($u){return $this->rows('SELECT l.*, b.amount winning_amount, s.name seller_name, s.email seller_email, s.phone seller_phone FROM listings l JOIN bids b ON b.id=l.winner_bid_id JOIN users s ON s.id=l.seller_id WHERE b.buyer_id=?','i',[$u]);}})->get(current_user_id()); $this->view('buyer/won',compact('rows')); }
    public function spending() { require_login(); $stats=(new Bid($this->conn))->spending(current_user_id()); $this->view('buyer/spending',compact('stats')); }
    public function reviewSeller() { require_login(); $message=''; if($_SERVER['REQUEST_METHOD']==='POST'){ $rating=(int)$_POST['rating']; if($rating>=1&&$rating<=5&&trim($_POST['review_text'])!==''){ (new Review($this->conn))->create((int)$_POST['listing_id'],current_user_id(),(int)$_POST['seller_id'],$rating,trim($_POST['review_text'])); $message='Review submitted.'; } else $message='Valid rating and text required.'; } $this->view('buyer/review_seller',compact('message')); }
    public function reportListing() { require_login(); $message=''; if($_SERVER['REQUEST_METHOD']==='POST'){ if(trim($_POST['reason'])&&strlen(trim($_POST['description']))>=10){ (new Report($this->conn))->createListingReport((int)$_POST['listing_id'],current_user_id(),trim($_POST['reason']),trim($_POST['description'])); $message='Report submitted.'; } else $message='Reason and 10 character description required.'; } $this->view('buyer/report_listing',compact('message')); }
    public function reportUser() { require_login(); $message=''; if($_SERVER['REQUEST_METHOD']==='POST'){ if(trim($_POST['reason'])&&strlen(trim($_POST['description']))>=10){ (new Report($this->conn))->createUserReport(current_user_id(),(int)$_POST['reported_user_id'],trim($_POST['reason']),trim($_POST['description'])); $message='User report submitted.'; } else $message='Reason and 10 character description required.'; } $this->view('buyer/report_user',compact('message')); }
}
?>
