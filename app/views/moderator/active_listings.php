<?php if ($message): ?>
    <div class="alert <?= e($message_type) ?>"><?= e($message) ?></div>
<?php endif; ?>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h1>Active Listings</h1>
        <a href="index.php?page=pending_listings" class="btn secondary" style="width:auto;padding:8px 16px;">&larr; Pending Listings</a>
    </div>
    <p style="color:#6b7280;font-size:13px;margin-top:4px;">Review active listings and suspend any that violate platform policies.</p>

    <form method="get" style="margin:14px 0;">
        <input type="hidden" name="page" value="active_listings">
        <div style="display:flex;gap:10px;">
            <input type="text" name="q" value="<?= e($keyword) ?>" placeholder="Search by title or description…" style="flex:1;margin:0;">
            <button style="width:auto;padding:10px 20px;margin:0;">Search</button>
            <?php if ($keyword): ?><a href="index.php?page=active_listings" class="btn secondary" style="width:auto;padding:10px 16px;margin:0;">Clear</a><?php endif; ?>
        </div>
    </form>

    <?php if (empty($rows)): ?>
        <p style="color:#6b7280;margin-top:12px;">No active listings found<?= $keyword ? ' for "' . e($keyword) . '"' : '' ?>.</p>
    <?php else: ?>
        <table style="margin-top:8px;">
            <tr>
                <th>Title</th>
                <th>Seller</th>
                <th>Category</th>
                <th>Current Bid</th>
                <th>Bids</th>
                <th>Ends</th>
                <th>Action</th>
            </tr>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td>
                    <strong><?= e($r['title']) ?></strong>
                    <?php if ($r['is_featured']): ?><span class="badge" style="background:#fef9c3;color:#713f12;">Featured</span><?php endif; ?>
                </td>
                <td><?= e($r['seller_name']) ?></td>
                <td><?= e($r['category_name']) ?></td>
                <td>&#2547;<?= number_format($r['current_bid'], 2) ?></td>
                <td><?= (int)$r['bid_count'] ?></td>
                <td style="font-size:13px;"><?= e($r['end_datetime']) ?></td>
                <td>
                    <button class="secondary" style="width:auto;padding:6px 14px;font-size:13px;"
                        onclick="document.getElementById('suspend-<?= (int)$r['id'] ?>').style.display = document.getElementById('suspend-<?= (int)$r['id'] ?>').style.display==='none'?'block':'none'">
                        Suspend
                    </button>
                </td>
            </tr>
            <tr id="suspend-<?= (int)$r['id'] ?>" style="display:none;background:#fff8f0;">
                <td colspan="7" style="padding:14px;">
                    <form method="post">
                        <input type="hidden" name="listing_id" value="<?= (int)$r['id'] ?>">
                        <label style="font-weight:600;display:block;margin-bottom:4px;">Suspension Reason (required):</label>
                        <textarea name="suspension_reason" rows="2" placeholder="Explain why this listing is being suspended…" style="resize:vertical;margin-bottom:8px;"></textarea>
                        <div style="display:flex;gap:8px;">
                            <button class="danger" style="width:auto;padding:8px 18px;">Confirm Suspension</button>
                            <button type="button" class="secondary" style="width:auto;padding:8px 14px;"
                                onclick="document.getElementById('suspend-<?= (int)$r['id'] ?>').style.display='none'">Cancel</button>
                        </div>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>