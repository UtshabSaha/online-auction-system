<<<<<<< HEAD
<div class="card"><h1>Pending Listing Reviews</h1><table><tr><th>Title</th><th>Seller</th><th>Category</th><th>Action</th></tr><?php foreach($rows as $r): ?><tr><td><?= e($r['title']) ?></td><td><?= e($r['seller_name']) ?></td><td><?= e($r['category_name']) ?></td><td><button onclick="moderateListing(<?= $r['id'] ?>,'active')">Approve AJAX</button><button class="danger" onclick="moderateListing(<?= $r['id'] ?>,'rejected')">Reject AJAX</button></td></tr><?php endforeach; ?></table></div>
=======
<?php if ($message): ?>
    <div class="alert <?= e($message_type) ?>"><?= e($message) ?></div>
<?php endif; ?>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h1>Pending Listing Reviews</h1>
        <a href="index.php?page=active_listings" class="btn secondary" style="width:auto;padding:8px 16px;">View Active Listings &rarr;</a>
    </div>

    <?php if (empty($rows)): ?>
        <p style="color:#6b7280;margin-top:12px;">No listings are currently awaiting review.</p>
    <?php else: ?>
        <?php foreach ($rows as $r): ?>
        <div class="card" style="border-left:4px solid #f59e0b;margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
                <div>
                    <strong style="font-size:16px;"><?= e($r['title']) ?></strong>
                    <div style="margin-top:4px;color:#6b7280;font-size:13px;">
                        Seller: <strong><?= e($r['seller_name']) ?></strong> &middot;
                        Category: <?= e($r['category_name']) ?> &middot;
                        Condition: <?= e($r['condition']) ?> &middot;
                        Starting: <strong>&#2547;<?= number_format($r['starting_price'], 2) ?></strong>
                        <?php if ($r['reserve_price']): ?> &middot; Reserve: &#2547;<?= number_format($r['reserve_price'], 2) ?><?php endif; ?>
                    </div>
                    <div style="margin-top:4px;color:#6b7280;font-size:13px;">
                        Ends: <?= e($r['end_datetime']) ?> &middot; Submitted: <?= e($r['created_at']) ?>
                    </div>
                </div>
                <span class="badge" style="background:#fef3c7;color:#92400e;">pending_review</span>
            </div>
            <div style="margin-top:10px;padding:10px;background:#f9fafb;border-radius:6px;font-size:14px;">
                <?= nl2br(e(substr($r['description'], 0, 400))) ?><?= strlen($r['description']) > 400 ? '…' : '' ?>
            </div>
            <?php if ($r['suspension_reason']): ?>
            <div class="alert" style="margin-top:8px;"><strong>Previous suspension reason:</strong> <?= e($r['suspension_reason']) ?></div>
            <?php endif; ?>
            <div style="display:flex;gap:12px;margin-top:14px;flex-wrap:wrap;">
                <!-- Approve -->
                <form method="post" style="display:inline;">
                    <input type="hidden" name="listing_id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="action" value="approve">
                    <button class="success" style="width:auto;padding:8px 20px;" onclick="return confirm('Approve this listing?')">
                        &#10003; Approve
                    </button>
                </form>
                <!-- Reject toggle -->
                <button class="secondary" style="width:auto;padding:8px 20px;"
                    onclick="document.getElementById('reject-form-<?= (int)$r['id'] ?>').style.display = document.getElementById('reject-form-<?= (int)$r['id'] ?>').style.display==='none'?'block':'none'">
                    &#10007; Reject
                </button>
            </div>
            <!-- Reject form (hidden by default) -->
            <div id="reject-form-<?= (int)$r['id'] ?>" style="display:none;margin-top:12px;">
                <form method="post">
                    <input type="hidden" name="listing_id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="action" value="reject">
                    <label style="font-weight:600;display:block;margin-bottom:4px;">Rejection Reason (required — will be sent to seller):</label>
                    <textarea name="rejection_reason" rows="3" placeholder="Explain why this listing is being rejected..." required style="resize:vertical;"></textarea>
                    <button class="danger" style="width:auto;padding:8px 20px;">Confirm Rejection</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
>>>>>>> origin/moderator/features
