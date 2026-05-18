<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
    <h1 style="margin:0;">Trust Profile: <?= e($user['name']) ?></h1>
    <a href="index.php?page=trust_score" class="btn secondary" style="width:auto;padding:8px 16px;">&larr; Back to Trust Scores</a>
</div>

<!-- User Overview -->
<div class="card">
    <h2>User Overview</h2>
    <div class="grid">
        <div>
            <table>
                <tr><th>Field</th><th>Value</th></tr>
                <tr><td>Name</td><td><strong><?= e($user['name']) ?></strong></td></tr>
                <tr><td>Email</td><td><?= e($user['email']) ?></td></tr>
                <tr><td>Phone</td><td><?= e($user['phone'] ?? '—') ?></td></tr>
                <tr><td>Role</td><td><?= e($user['role']) ?></td></tr>
                <tr><td>Account Status</td><td>
                    <?php if ($user['is_active']): ?>
                        <span class="badge" style="background:#dcfce7;color:#166534;">Active</span>
                    <?php else: ?>
                        <span class="badge" style="background:#fee2e2;color:#991b1b;">Suspended / Inactive</span>
                    <?php endif; ?>
                </td></tr>
                <tr><td>Member Since</td><td><?= e($user['created_at']) ?></td></tr>
            </table>
        </div>
        <div>
            <table>
                <tr><th>Metric</th><th>Value</th></tr>
                <tr>
                    <td>Reputation Score</td>
                    <td>
                        <?php $score = (float)$user['reputation_score']; $color = $score >= 4 ? '#16a34a' : ($score >= 2.5 ? '#d97706' : '#dc2626'); ?>
                        <strong style="font-size:20px;color:<?= $color ?>;"><?= number_format($score, 2) ?></strong>
                        <span style="color:#9ca3af;">/ 5.00</span>
                    </td>
                </tr>
                <tr>
                    <td>Total Warnings</td>
                    <td>
                        <?php $wc = (int)$user['warning_count']; $wcolor = $wc >= 3 ? '#dc2626' : ($wc >= 1 ? '#d97706' : '#16a34a'); ?>
                        <strong style="color:<?= $wcolor ?>;"><?= $wc ?></strong>
                    </td>
                </tr>
                <tr>
                    <td>Reports Received</td>
                    <td><strong><?= (int)$user['report_count'] ?></strong></td>
                </tr>
                <?php if ($user['role'] === 'seller'): ?>
                <tr>
                    <td>Total Listings</td>
                    <td><?= (int)$user['listing_count'] ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <?php if ($user['bio']): ?>
    <div style="margin-top:12px;">
        <strong>Bio:</strong> <?= e($user['bio']) ?>
    </div>
    <?php endif; ?>

    <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
        <a href="index.php?page=warnings" class="btn danger" style="width:auto;padding:8px 18px;font-size:13px;">Issue Warning</a>
        <a href="index.php?page=mod_messaging" class="btn secondary" style="width:auto;padding:8px 18px;font-size:13px;">Send Message</a>
    </div>
</div>

<!-- Warning History -->
<div class="card">
    <h2>Warning History (<?= count($warnings_history) ?>)</h2>
    <?php if (empty($warnings_history)): ?>
        <p style="color:#6b7280;">No warnings on record.</p>
    <?php else: ?>
    <table>
        <tr><th>#</th><th>Warning Reason</th><th>Issued By</th><th>Date</th></tr>
        <?php foreach ($warnings_history as $i => $w): ?>
        <tr style="<?= $i < 3 ? 'background:#fff7ed;' : '' ?>">
            <td><?= $i + 1 ?></td>
            <td><?= nl2br(e($w['reason'])) ?></td>
            <td><?= e($w['issued_by_name']) ?></td>
            <td style="font-size:13px;white-space:nowrap;"><?= e($w['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>

<!-- Report History -->
<div class="card">
    <h2>Report History (<?= count($reports_history) ?> reports received)</h2>
    <?php if (empty($reports_history)): ?>
        <p style="color:#6b7280;">No reports on record for this user.</p>
    <?php else: ?>
    <table>
        <tr><th>Reporter</th><th>Reason</th><th>Description</th><th>Status</th><th>Date</th></tr>
        <?php foreach ($reports_history as $r): ?>
        <?php
            $status_bg = $r['status'] === 'pending' ? '#fee2e2' : ($r['status'] === 'escalated' ? '#fef3c7' : ($r['status'] === 'dismissed' ? '#f3f4f6' : '#dcfce7'));
            $status_color = $r['status'] === 'pending' ? '#991b1b' : ($r['status'] === 'escalated' ? '#92400e' : ($r['status'] === 'dismissed' ? '#374151' : '#166534'));
        ?>
        <tr>
            <td><?= e($r['reporter_name']) ?></td>
            <td><strong><?= e($r['reason']) ?></strong></td>
            <td style="font-size:13px;"><?= e(substr($r['description'], 0, 120)) ?><?= strlen($r['description']) > 120 ? '…' : '' ?></td>
            <td><span class="badge" style="background:<?= $status_bg ?>;color:<?= $status_color ?>;"><?= e($r['status']) ?></span></td>
            <td style="font-size:13px;white-space:nowrap;"><?= e($r['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>