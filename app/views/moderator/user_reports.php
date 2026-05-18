<?php if ($message): ?>
    <div class="alert <?= e($message_type) ?>"><?= e($message) ?></div>
<?php endif; ?>

<div class="card">
    <h1>User Reports</h1>
    <p style="color:#6b7280;font-size:13px;margin-top:4px;">Review reports submitted about users. You can dismiss, issue a warning, or escalate serious cases to admin for suspension.</p>

    <?php if (empty($rows)): ?>
        <p style="color:#6b7280;margin-top:12px;">No user reports found.</p>
    <?php else: ?>
        <?php foreach ($rows as $r): ?>
        <?php
            $border_color = $r['status'] === 'pending' ? '#ef4444' : ($r['status'] === 'escalated' ? '#f59e0b' : ($r['status'] === 'dismissed' ? '#9ca3af' : '#16a34a'));
            $status_bg = $r['status'] === 'pending' ? '#fee2e2' : ($r['status'] === 'escalated' ? '#fef3c7' : ($r['status'] === 'dismissed' ? '#f3f4f6' : '#dcfce7'));
            $status_color = $r['status'] === 'pending' ? '#991b1b' : ($r['status'] === 'escalated' ? '#92400e' : ($r['status'] === 'dismissed' ? '#374151' : '#166534'));
        ?>
        <div class="card" style="border-left:4px solid <?= $border_color ?>;margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
                <div>
                    <strong style="font-size:15px;">Report #<?= (int)$r['id'] ?>:</strong>
                    Reported User: <strong><?= e($r['reported_name']) ?></strong>
                    &nbsp;<a href="index.php?page=trust_detail&id=<?= (int)$r['reported_user_id'] ?>" class="btn secondary" style="width:auto;padding:3px 10px;font-size:12px;">View History</a>
                    <div style="margin-top:4px;color:#6b7280;font-size:13px;">
                        Reporter: <strong><?= e($r['reporter_name']) ?></strong> &middot;
                        Reason: <strong><?= e($r['reason']) ?></strong> &middot;
                        Reported: <?= e($r['created_at']) ?>
                    </div>
                    <div style="margin-top:6px;padding:8px;background:#f9fafb;border-radius:4px;font-size:13px;">
                        <?= nl2br(e($r['description'])) ?>
                    </div>
                </div>
                <span class="badge" style="background:<?= $status_bg ?>;color:<?= $status_color ?>;white-space:nowrap;"><?= e($r['status']) ?></span>
            </div>

            <?php if ($r['moderator_note']): ?>
            <div style="margin-top:8px;font-size:13px;color:#374151;">
                <strong>Moderator Note:</strong> <?= e($r['moderator_note']) ?>
            </div>
            <?php endif; ?>

            <?php if ($r['status'] === 'pending'): ?>
            <div style="margin-top:14px;">
                <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
                    <button class="secondary" style="width:auto;padding:7px 16px;font-size:13px;"
                        onclick="toggleUserAction('ur-action-<?= (int)$r['id'] ?>','<?= (int)$r['reported_user_id'] ?>','dismiss')">
                        Dismiss
                    </button>
                    <button class="secondary" style="width:auto;padding:7px 16px;font-size:13px;background:#d97706;"
                        onclick="toggleUserAction('ur-action-<?= (int)$r['id'] ?>','<?= (int)$r['reported_user_id'] ?>','warn')">
                        Issue Warning
                    </button>
                    <button class="danger" style="width:auto;padding:7px 16px;font-size:13px;"
                        onclick="toggleUserAction('ur-action-<?= (int)$r['id'] ?>','<?= (int)$r['reported_user_id'] ?>','escalate')">
                        Recommend Suspension (Escalate to Admin)
                    </button>
                </div>
                <div id="ur-action-<?= (int)$r['id'] ?>" style="display:none;padding:12px;background:#f0f9ff;border-radius:6px;border:1px solid #bae6fd;">
                    <form method="post">
                        <input type="hidden" name="report_id" value="<?= (int)$r['id'] ?>">
                        <input type="hidden" name="reported_user_id" value="<?= (int)$r['reported_user_id'] ?>">
                        <input type="hidden" name="action" id="ur-act-<?= (int)$r['id'] ?>" value="">
                        <label style="font-weight:600;display:block;margin-bottom:4px;" id="ur-lbl-<?= (int)$r['id'] ?>">Note:</label>
                        <textarea name="moderator_note" rows="3" placeholder="Add a note or reason…" style="resize:vertical;"></textarea>
                        <div style="display:flex;gap:8px;margin-top:4px;">
                            <button style="width:auto;padding:8px 20px;">Confirm</button>
                            <button type="button" class="secondary" style="width:auto;padding:8px 14px;"
                                onclick="document.getElementById('ur-action-<?= (int)$r['id'] ?>').style.display='none'">Cancel</button>
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
function toggleUserAction(containerId, userId, action) {
    var c = document.getElementById(containerId);
    var id = containerId.replace('ur-action-', '');
    var actionInput = document.getElementById('ur-act-' + id);
    var label = document.getElementById('ur-lbl-' + id);
    var labels = {
        'dismiss': 'Dismissal Note (optional):',
        'warn': 'Warning Reason (required — will be recorded against user):',
        'escalate': 'Escalation Note (required — admin will review for suspension):'
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