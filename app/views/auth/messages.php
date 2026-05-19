<div class="card">
    <h1>My Messages</h1>

    <?php if (empty($rows)): ?>
        <p>No messages yet.</p>
    <?php else: ?>
        <table>
            <tr><th>From</th><th>Role</th><th>Message</th><th>Listing</th><th>Date</th></tr>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= e($r['sender_name']) ?></strong></td>
                    <td><?= e(ucfirst($r['sender_role'])) ?></td>
                    <td><?= nl2br(e($r['message'])) ?></td>
                    <td><?= $r['listing_title'] ? e($r['listing_title']) : '-' ?></td>
                    <td><?= e($r['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
