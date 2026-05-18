<div class="card">
    <h1>User Trust Scores</h1>
    <p style="color:#6b7280;font-size:13px;margin-top:4px;">Overview of buyer and seller trust profiles. Click a user to view their full warning and report history.</p>

    <?php if (empty($rows)): ?>
        <p style="color:#6b7280;margin-top:12px;">No users found.</p>
    <?php else: ?>
    <table style="margin-top:8px;">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Reputation Score</th>
            <th>Warnings</th>
            <th>Reports Received</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($rows as $r): ?>
        <?php
            $score = (float)$r['reputation_score'];
            $score_color = $score >= 4 ? '#16a34a' : ($score >= 2.5 ? '#d97706' : '#dc2626');
            $warn_color = (int)$r['warnings'] >= 3 ? '#dc2626' : ((int)$r['warnings'] >= 1 ? '#d97706' : '#16a34a');
        ?>
        <tr>
            <td><strong><?= e($r['name']) ?></strong></td>
            <td style="font-size:13px;"><?= e($r['email']) ?></td>
            <td><?= e($r['role']) ?></td>
            <td>
                <span style="font-weight:700;color:<?= $score_color ?>;"><?= number_format($score, 2) ?></span>
                <span style="color:#9ca3af;font-size:12px;">/ 5.00</span>
            </td>
            <td>
                <span style="font-weight:700;color:<?= $warn_color ?>;"><?= (int)$r['warnings'] ?></span>
            </td>
            <td><?= (int)$r['reports'] ?></td>
            <td>
                <?php if ($r['is_active']): ?>
                    <span class="badge" style="background:#dcfce7;color:#166534;">Active</span>
                <?php else: ?>
                    <span class="badge" style="background:#fee2e2;color:#991b1b;">Suspended</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="index.php?page=trust_detail&id=<?= (int)$r['id'] ?>" class="btn secondary" style="width:auto;padding:5px 12px;font-size:12px;">View Details</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>