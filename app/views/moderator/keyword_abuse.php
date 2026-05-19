<?php if ($message): ?>
    <div class="alert <?= e($message_type) ?>"><?= e($message) ?></div>
<?php endif; ?>

<h1>Keyword Abuse Log</h1>

<div style="display:flex;gap:16px;flex-wrap:wrap;">
    <!-- Manage Keywords -->
    <div class="card" style="flex:1;min-width:260px;">
        <h2>Manage Flagged Keywords</h2>
        <p style="color:#6b7280;font-size:13px;margin-top:0;">Listings containing these keywords in their title or description will appear below for review.</p>
        <form method="post" style="display:flex;gap:8px;margin-bottom:14px;">
            <input type="hidden" name="action" value="add_keyword">
            <input type="text" name="keyword" placeholder="Enter keyword…" style="flex:1;margin:0;">
            <button style="width:auto;padding:10px 16px;margin:0;">Add</button>
        </form>

        <?php if (empty($keywords)): ?>
            <p style="color:#9ca3af;font-size:13px;">No keywords configured yet.</p>
        <?php else: ?>
        <table>
            <tr><th>Keyword</th><th>Added</th><th>Action</th></tr>
            <?php foreach ($keywords as $k): ?>
            <tr>
                <td><strong><?= e($k['keyword']) ?></strong></td>
                <td style="font-size:12px;"><?= e(substr($k['created_at'], 0, 10)) ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="action" value="delete_keyword">
                        <input type="hidden" name="keyword_id" value="<?= (int)$k['id'] ?>">
                        <button class="danger" style="width:auto;padding:4px 10px;font-size:12px;" onclick="return confirm('Remove keyword?')">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Flagged Listings -->
<div class="card" style="margin-top:16px;">
    <h2>Suspicious Listings (<?= count($flagged_listings) ?>)</h2>
    <p style="color:#6b7280;font-size:13px;margin-top:0;">Active or pending listings that contain one or more flagged keywords.</p>

    <?php if (empty($keywords)): ?>
        <div class="alert">No flagged keywords have been configured. Add keywords above to start detecting suspicious listings.</div>
    <?php elseif (empty($flagged_listings)): ?>
        <p style="color:#16a34a;font-weight:600;">&#10003; No suspicious listings found with the current keyword set.</p>
    <?php else: ?>
    <table>
        <tr>
            <th>Title</th>
            <th>Seller</th>
            <th>Category</th>
            <th>Status</th>
            <th>Submitted</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($flagged_listings as $r): ?>
        <tr>
            <td>
                <strong><?= e($r['title']) ?></strong>
                <div style="font-size:12px;color:#6b7280;margin-top:2px;"><?= e(substr($r['description'], 0, 80)) ?><?= strlen($r['description']) > 80 ? '…' : '' ?></div>
            </td>
            <td><?= e($r['seller_name']) ?></td>
            <td><?= e($r['category_name']) ?></td>
            <td>
                <span class="badge" style="background:<?= $r['status'] === 'active' ? '#dcfce7' : '#fef3c7' ?>;color:<?= $r['status'] === 'active' ? '#166534' : '#92400e' ?>;">
                    <?= e($r['status']) ?>
                </span>
            </td>
            <td style="font-size:13px;"><?= e(substr($r['created_at'], 0, 10)) ?></td>
            <td>
                <?php if ($r['status'] === 'active'): ?>
                    <a href="index.php?page=active_listings" class="btn danger" style="width:auto;padding:5px 12px;font-size:12px;">Suspend</a>
                <?php elseif ($r['status'] === 'pending_review'): ?>
                    <a href="index.php?page=pending_listings" class="btn secondary" style="width:auto;padding:5px 12px;font-size:12px;">Review</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>