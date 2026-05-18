<?php if ($message): ?>
    <div class="alert <?= e($message_type) ?>"><?= e($message) ?></div>
<?php endif; ?>

<div class="card">
    <h1>Listing Reports</h1>
    <p style="color:#6b7280;font-size:13px;margin-top:4px;">Review reports submitted by buyers about listings that may violate platform policies.</p>

    <?php if (empty($rows)): ?>
        <p style="color:#6b7280;margin-top:12px;">No listing reports found.</p>
    <?php else: ?>
        <?php foreach ($rows as $r): ?>
        <?php
            $border_color = $r['status'] === 'pending' ? '#ef4444' : ($r['status'] === 'dismissed' ? '#9ca3af' : '#16a34a');
            $status_bg    = $r['status'] === 'pending' ? '#fee2e2' : ($r['status'] === 'dismissed' ? '#f3f4f6' : '#dcfce7');
            $status_color = $r['status'] === 'pending' ? '#991b1b' : ($r['status'] === 'dismissed' ? '#374151' : '#166534');
        ?>
        <div class="card" style="border-left:4px solid <?= $border_color ?>;margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
                <div>
                    <strong style="font-size:15px;">Report #<?= (int)$r['id'] ?>:</strong>
                    <span style="font-size:15px;"> <?= e($r['title']) ?></span>
                    &nbsp;
                    <!-- ✅ FIX: Link to full listing detail so moderator can review listing content -->
                    <a href="index.php?page=auction&id=<?= (int)$r['listing_id'] ?>"
                       target="_blank"
                       class="btn secondary"
                       style="width:auto;padding:3px 10px;font-size:12px;display:inline-block;">
                       &#128279; View Full Listing
                    </a>
                    <div style="margin-top:4px;color:#6b7280;font-size:13px;">
                        Reporter: <strong><?= e($r['reporter_name']) ?></strong> &middot;
                        Reason: <strong><?= e($r['reason']) ?></strong> &middot;
                        Reported: <?= e($r['created_at']) ?>
                    </div>
                    <div style="margin-top:4px;color:#374151;font-size:13px;">
                        <?= nl2br(e($r['description'])) ?>
                    </div>
                </div>
                <span class="badge" style="background:<?= $status_bg ?>;color:<?= $status_color ?>;white-space:nowrap;"><?= e($r['status']) ?></span>
            </div>

            <div style="margin-top:8px;padding:8px 12px;background:#f9fafb;border-radius:6px;font-size:13px;">
                <strong>Listing Status:</strong>
                <span class="badge" style="font-size:12px;"><?= e($r['listing_status']) ?></span>
                <?php if ($r['moderator_note']): ?>
                    &nbsp;&middot;&nbsp;<strong>Moderator Note:</strong> <?= e($r['moderator_note']) ?>
                <?php endif; ?>
            </div>

            <?php if ($r['status'] === 'pending'): ?>
            <div style="margin-top:14px;">
                <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
                    <button class="secondary" style="width:auto;padding:7px 16px;font-size:13px;"
                        onclick="toggleAction('report-action-<?= (int)$r['id'] ?>','dismiss')">Dismiss Report</button>
                    <?php if ($r['listing_status'] === 'active'): ?>
                    <button class="danger" style="width:auto;padding:7px 16px;font-size:13px;"
                        onclick="toggleAction('report-action-<?= (int)$r['id'] ?>','suspend')">Suspend Listing</button>
                    <?php endif; ?>
                    <button class="secondary" style="width:auto;padding:7px 16px;font-size:13px;background:#d97706;"
                        onclick="toggleAction('report-action-<?= (int)$r['id'] ?>','warn_seller')">Issue Warning to Seller</button>
                </div>
                <div id="report-action-<?= (int)$r['id'] ?>" style="display:none;padding:12px;background:#f0f9ff;border-radius:6px;border:1px solid #bae6fd;">
                    <form method="post" id="report-form-<?= (int)$r['id'] ?>">
                        <input type="hidden" name="report_id" value="<?= (int)$r['id'] ?>">
                        <input type="hidden" name="action" id="action-<?= (int)$r['id'] ?>" value="">
                        <label style="font-weight:600;display:block;margin-bottom:4px;" id="action-label-<?= (int)$r['id'] ?>">Note:</label>
                        <textarea name="moderator_note" rows="3" placeholder="Add a note or reason…" style="resize:vertical;"></textarea>
                        <div style="display:flex;gap:8px;margin-top:4px;">
                            <button style="width:auto;padding:8px 20px;">Confirm</button>
                            <button type="button" class="secondary" style="width:auto;padding:8px 14px;"
                                onclick="document.getElementById('report-action-<?= (int)$r['id'] ?>').style.display='none'">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function toggleAction(containerId, action) {
    var c = document.getElementById(containerId);
    var parts = containerId.split('-');
    var id = parts[parts.length - 1];
    var actionInput = document.getElementById('action-' + id);
    var label = document.getElementById('action-label-' + id);
    var labels = {
        'dismiss':     'Dismiss Reason (optional):',
        'suspend':     'Suspension Reason (required — listing will return to pending_review):',
        'warn_seller': 'Warning Message to Seller (required):'
    };
    if (c.style.display === 'none' || actionInput.value !== action) {
        c.style.display = 'block';
        actionInput.value = action;
        label.textContent = labels[action] || 'Note:';
    } else {
        c.style.display = 'none';
    }
}
</script>