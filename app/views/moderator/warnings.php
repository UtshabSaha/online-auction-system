<<<<<<< HEAD
<div class="card"><h1>Warnings</h1><form method="post"><input type="number" name="user_id" placeholder="User ID" required><textarea name="reason" placeholder="Warning reason" required></textarea><button>Issue Warning</button></form><table><tr><th>User</th><th>Issued By</th><th>Reason</th><th>Date</th></tr><?php foreach($rows as $r): ?><tr><td><?= e($r['user_name']) ?></td><td><?= e($r['issued_by_name']) ?></td><td><?= e($r['reason']) ?></td><td><?= e($r['created_at']) ?></td></tr><?php endforeach; ?></table></div>
=======
<?php if ($message): ?>
    <div class="alert <?= e($message_type) ?>"><?= e($message) ?></div>
<?php endif; ?>

<div class="card">
    <h1>Issue Warning</h1>
    <p style="color:#6b7280;font-size:13px;margin-top:2px;">Issue an official written warning to any buyer or seller. Warnings are logged permanently against the user.</p>

    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
        <div style="flex:1;min-width:260px;">
            <label style="font-weight:600;display:block;margin-bottom:4px;">Search Users (buyers &amp; sellers)</label>
            <form method="get" style="display:flex;gap:8px;">
                <input type="hidden" name="page" value="warnings">
                <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search by name or email…" style="flex:1;margin:0;">
                <button style="width:auto;padding:10px 16px;margin:0;">Search</button>
            </form>
            <?php if (!empty($users)): ?>
            <table style="margin-top:10px;font-size:13px;">
                <tr><th>Name</th><th>Email</th><th>Role</th><th>ID</th></tr>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['role']) ?></td>
                    <td><strong><?= (int)$u['id'] ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </div>
        <div style="flex:1;min-width:260px;">
            <form method="post">
                <label style="font-weight:600;display:block;margin-bottom:4px;">User ID <span style="font-weight:400;color:#6b7280;">(from search results)</span></label>
                <input type="number" name="user_id" placeholder="Enter User ID" required min="1" style="margin-bottom:10px;">
                <label style="font-weight:600;display:block;margin-bottom:4px;">Warning Reason</label>
                <textarea name="reason" rows="4" placeholder="Describe the policy violation and expected behaviour…" required style="resize:vertical;"></textarea>
                <button class="danger" style="width:auto;padding:9px 24px;margin-top:4px;">Issue Warning</button>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <h2>All Warnings Issued</h2>
    <?php if (empty($rows)): ?>
        <p style="color:#6b7280;margin-top:8px;">No warnings have been issued yet.</p>
    <?php else: ?>
    <table style="margin-top:8px;">
        <tr>
            <th>User</th>
            <th>Email</th>
            <th>Warning Reason</th>
            <th>Issued By</th>
            <th>Date</th>
        </tr>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td>
                <strong><?= e($r['user_name']) ?></strong><br>
                <a href="index.php?page=trust_detail&id=<?= (int)$r['user_id'] ?>" style="font-size:12px;color:#2563eb;">View Trust Profile</a>
            </td>
            <td style="font-size:13px;"><?= e($r['user_email']) ?></td>
            <td><?= nl2br(e($r['reason'])) ?></td>
            <td><?= e($r['issued_by_name']) ?></td>
            <td style="font-size:13px;white-space:nowrap;"><?= e($r['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>
>>>>>>> origin/moderator/features
