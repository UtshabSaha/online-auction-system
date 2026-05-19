<h1>Moderator Dashboard</h1>

<div class="grid">
    <div class="card">
        <div class="stat"><?= e($stats['pending_listings']) ?></div>
        <p>Pending Listings</p>
        <a href="index.php?page=pending_listings" class="btn" style="display:inline-block;padding:6px 14px;font-size:13px;">Review Now</a>
    </div>
    <div class="card">
        <div class="stat"><?= e($stats['listing_reports']) ?></div>
        <p>Open Listing Reports</p>
        <a href="index.php?page=listing_reports" class="btn" style="display:inline-block;padding:6px 14px;font-size:13px;">View Reports</a>
    </div>
    <div class="card">
        <div class="stat"><?= e($stats['user_reports']) ?></div>
        <p>Open User Reports</p>
        <a href="index.php?page=user_reports" class="btn" style="display:inline-block;padding:6px 14px;font-size:13px;">View Reports</a>
    </div>
    <div class="card">
        <div class="stat"><?= e($stats['warnings_week']) ?></div>
        <p>Warnings Issued This Week</p>
        <a href="index.php?page=warnings" class="btn" style="display:inline-block;padding:6px 14px;font-size:13px;">View Warnings</a>
    </div>
</div>

<div class="card">
    <h2>Quick Actions</h2>
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px;">
        <a href="index.php?page=active_listings" class="btn secondary" style="width:auto;padding:9px 18px;">View Active Listings (Suspend)</a>
        <a href="index.php?page=keyword_abuse" class="btn secondary" style="width:auto;padding:9px 18px;">Keyword Abuse Log</a>
        <a href="index.php?page=mod_messaging" class="btn secondary" style="width:auto;padding:9px 18px;">Send Message</a>
        <a href="index.php?page=categories" class="btn secondary" style="width:auto;padding:9px 18px;">Manage Categories</a>
        <a href="index.php?page=moderation_report" class="btn secondary" style="width:auto;padding:9px 18px;">Activity Report</a>
    </div>
</div>