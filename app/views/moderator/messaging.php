<?php if ($message): ?>
    <div class="alert <?= e($message_type) ?>"><?= e($message) ?></div>
<?php endif; ?>

<h1>Send Moderation Message</h1>

<div style="display:flex;gap:16px;flex-wrap:wrap;">
    <!-- Compose -->
    <div class="card" style="flex:1;min-width:300px;">
        <h2>Compose Message</h2>
        <p style="color:#6b7280;font-size:13px;margin-top:0;">Send a clarification message to a buyer or seller regarding a moderation decision.</p>

        <!-- User Search -->
        <form method="get" style="display:flex;gap:8px;margin-bottom:14px;">
            <input type="hidden" name="page" value="mod_messaging">
            <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search recipient by name or email…" style="flex:1;margin:0;">
            <button style="width:auto;padding:10px 16px;margin:0;">Search</button>
        </form>

        <?php if (!empty($users)): ?>
        <div style="margin-bottom:14px;padding:10px;background:#f9fafb;border-radius:6px;">
            <strong style="font-size:13px;">Search Results — click an ID to pre-fill:</strong>
            <table style="margin-top:6px;font-size:13px;">
                <tr><th>Name</th><th>Email</th><th>Role</th><th>ID</th></tr>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['role']) ?></td>
                    <td>
                        <a href="#" onclick="document.getElementById('receiver_id').value=<?= (int)$u['id'] ?>;document.getElementById('receiver_name_hint').textContent='<?= addslashes(htmlspecialchars($u['name'])) ?>';return false;"
                           style="font-weight:700;color:#2563eb;"><?= (int)$u['id'] ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>

        <form method="post">
            <label style="font-weight:600;display:block;margin-bottom:2px;">
                Recipient User ID
                <span id="receiver_name_hint" style="font-weight:400;color:#2563eb;font-size:13px;margin-left:6px;"></span>
            </label>
            <input type="number" name="receiver_id" id="receiver_id" placeholder="User ID" required min="1">

            <label style="font-weight:600;display:block;margin-bottom:2px;">Related Listing ID <span style="font-weight:400;color:#6b7280;">(optional)</span></label>
            <input type="number" name="listing_id" placeholder="Listing ID (leave blank if not about a specific listing)" min="1">

            <label style="font-weight:600;display:block;margin-bottom:2px;">Message</label>
            <textarea name="message" rows="6" placeholder="Write your moderation message here. Be clear and professional." required style="resize:vertical;"></textarea>

            <button style="width:auto;padding:10px 24px;margin-top:4px;">Send Message</button>
        </form>
    </div>

    <!-- Sent Messages Log -->
    <div class="card" style="flex:1;min-width:300px;">
        <h2>Messages Sent</h2>
        <?php if (empty($sent_messages)): ?>
            <p style="color:#6b7280;font-size:13px;">No messages sent yet.</p>
        <?php else: ?>
        <?php foreach ($sent_messages as $m): ?>
        <div style="border:1px solid #e5e7eb;border-radius:6px;padding:12px;margin-bottom:10px;">
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:6px;">
                <div>
                    <strong>To:</strong> <?= e($m['receiver_name']) ?>
                    <span class="badge" style="font-size:11px;"><?= e($m['receiver_role']) ?></span>
                    <?php if ($m['listing_title']): ?>
                        &nbsp;<span style="font-size:12px;color:#6b7280;">Re: <?= e($m['listing_title']) ?></span>
                    <?php endif; ?>
                </div>
                <span style="font-size:12px;color:#9ca3af;"><?= e($m['created_at']) ?></span>
            </div>
            <div style="margin-top:8px;font-size:14px;background:#f9fafb;padding:8px;border-radius:4px;">
                <?= nl2br(e($m['message'])) ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>