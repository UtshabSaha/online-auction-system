<?php
$total_reports = ($stats['listing_reports_done'] ?? 0) + ($stats['user_reports_done'] ?? 0);
$approval_rate = ($stats['reviewed'] ?? 0) > 0 ? round(($stats['approved'] / $stats['reviewed']) * 100, 1) : 0;
?>

<div class="card">
    <h1>Moderation Activity Report</h1>

    <form method="get" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;margin-top:12px;">
        <input type="hidden" name="page" value="moderation_report">
        <div>
            <label style="font-weight:600;display:block;margin-bottom:4px;">From Date</label>
            <input type="date" name="from" value="<?= e($from) ?>" style="margin:0;width:160px;">
        </div>
        <div>
            <label style="font-weight:600;display:block;margin-bottom:4px;">To Date</label>
            <input type="date" name="to" value="<?= e($to) ?>" style="margin:0;width:160px;">
        </div>
        <button style="width:auto;padding:10px 22px;margin:0;">Apply Filter</button>
        <a href="index.php?page=moderation_report" class="btn secondary" style="width:auto;padding:10px 16px;margin:0;">Reset</a>
    </form>
    <p style="color:#6b7280;font-size:13px;margin-top:6px;">
        Showing data from <strong><?= e($from) ?></strong> to <strong><?= e($to) ?></strong>
    </p>
</div>

<div class="grid">
    <div class="card">
        <div class="stat"><?= (int)($stats['reviewed'] ?? 0) ?></div>
        <p>Total Listings Reviewed</p>
    </div>
    <div class="card">
        <div class="stat"><?= (int)($stats['approved'] ?? 0) ?></div>
        <p>Approved Listings</p>
    </div>
    <div class="card">
        <div class="stat"><?= (int)($stats['rejected_count'] ?? 0) ?></div>
        <p>Rejected Listings</p>
    </div>
    <div class="card">
        <div class="stat"><?= $approval_rate ?>%</div>
        <p>Approval Rate</p>
    </div>
    <div class="card">
        <div class="stat"><?= (int)$total_reports ?></div>
        <p>Total Reports Processed</p>
    </div>
    <div class="card">
        <div class="stat"><?= (int)($stats['listing_reports_done'] ?? 0) ?></div>
        <p>Listing Reports Processed</p>
    </div>
    <div class="card">
        <div class="stat"><?= (int)($stats['user_reports_done'] ?? 0) ?></div>
        <p>User Reports Processed</p>
    </div>
    <div class="card">
        <div class="stat"><?= (int)($stats['warnings_issued'] ?? 0) ?></div>
        <p>Warnings Issued</p>
    </div>
</div>

<div class="card">
    <h2>Summary</h2>
    <table>
        <tr><th>Metric</th><th>Value</th></tr>
        <tr><td>Period</td><td><?= e($from) ?> &rarr; <?= e($to) ?></td></tr>
        <tr><td>Total Listings Reviewed</td><td><?= (int)($stats['reviewed'] ?? 0) ?></td></tr>
        <tr><td>Approved</td><td><?= (int)($stats['approved'] ?? 0) ?></td></tr>
        <tr><td>Rejected</td><td><?= (int)($stats['rejected_count'] ?? 0) ?></td></tr>
        <tr><td>Approval Rate</td><td><?= $approval_rate ?>%</td></tr>
        <tr><td>Listing Reports Processed</td><td><?= (int)($stats['listing_reports_done'] ?? 0) ?></td></tr>
        <tr><td>User Reports Processed</td><td><?= (int)($stats['user_reports_done'] ?? 0) ?></td></tr>
        <tr><td>Total Reports Processed</td><td><?= (int)$total_reports ?></td></tr>
        <tr><td>Warnings Issued</td><td><?= (int)($stats['warnings_issued'] ?? 0) ?></td></tr>
    </table>
</div>