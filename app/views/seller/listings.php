<<<<<<< HEAD
<div class="card"><h1>My Listings</h1><table><tr><th>Title</th><th>Status</th><th>Current Bid</th><th>Bids</th><th>Time Remaining</th><th>Action</th></tr><?php foreach($rows as $r): ?><tr><td><?= e($r['title']) ?></td><td><?= e($r['status']) ?></td><td><?= e($r['current_bid']) ?></td><td><?= e($r['bid_count']) ?></td><td><?php if($r['status']==='active'): ?><span class="countdown" data-countdown="<?= e($r['end_datetime']) ?>"><?= e($r['end_datetime']) ?></span><?php else: ?><?= e(ucfirst($r['status'])) ?><?php endif; ?></td><td><a class="btn secondary" href="index.php?page=auction&id=<?= $r['id'] ?>">Live Bids</a><?php if((int)$r['bid_count']===0 && in_array($r['status'], ['pending_review','active'], true)): ?> <a class="btn" href="index.php?page=edit_listing&id=<?= $r['id'] ?>">Edit</a><form method="post" action="index.php?page=cancel_listing" style="display:inline"><input type="hidden" name="listing_id" value="<?= $r['id'] ?>"><input type="hidden" name="reason" value="Cancelled by seller before any bid"><button class="danger" style="width:auto;padding:9px 12px">Cancel</button></form><?php endif; ?></td></tr><?php endforeach; ?></table></div>
=======
<div class="card">
    <h1>My Listings</h1>

    <?php if (empty($rows)): ?>
        <p style="color:#6b7280;margin-top:12px;">You have not created any listings yet.</p>
    <?php else: ?>
    <table>
        <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Status</th>
            <th>Current Bid</th>
            <th>Bids</th>
            <th>Ends</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($rows as $r): ?>
        <?php
            $status_colors = [
                'active'         => ['#dcfce7','#166534'],
                'pending_review' => ['#fef3c7','#92400e'],
                'rejected'       => ['#fee2e2','#991b1b'],
                'ended'          => ['#f3f4f6','#374151'],
                'cancelled'      => ['#f3f4f6','#6b7280'],
            ];
            [$bg, $fg] = $status_colors[$r['status']] ?? ['#f3f4f6','#374151'];
        ?>
        <tr>
            <td>
                <strong><?= e($r['title']) ?></strong>

                <?php if ($r['status'] === 'rejected' && !empty($r['rejection_reason'])): ?>
                    <div style="margin-top:5px;padding:7px 10px;background:#fee2e2;border-left:3px solid #ef4444;border-radius:4px;font-size:12px;color:#7f1d1d;">
                        <strong>&#9888; Rejection reason:</strong> <?= e($r['rejection_reason']) ?>
                    </div>
                <?php endif; ?>

                <?php if ($r['status'] === 'pending_review' && !empty($r['suspension_reason'])): ?>
                    <div style="margin-top:5px;padding:7px 10px;background:#fef3c7;border-left:3px solid #f59e0b;border-radius:4px;font-size:12px;color:#78350f;">
                        <strong>&#9888; Suspended by moderator:</strong> <?= e($r['suspension_reason']) ?>
                        <div style="margin-top:3px;color:#92400e;">Please review the reason above and update your listing before it can go live again.</div>
                    </div>
                <?php endif; ?>
            </td>
            <td style="font-size:13px;"><?= e($r['category_name']) ?></td>
            <td>
                <span class="badge" style="background:<?= $bg ?>;color:<?= $fg ?>;">
                    <?= e($r['status']) ?>
                </span>
            </td>
            <td>&#2547;<?= number_format((float)$r['current_bid'], 2) ?></td>
            <td><?= (int)$r['bid_count'] ?></td>
            <td style="font-size:13px;"><?= e(substr($r['end_datetime'], 0, 16)) ?></td>
            <td>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <?php if (in_array($r['status'], ['pending_review', 'active'], true) && (int)$r['bid_count'] === 0): ?>
                        <a class="btn" href="index.php?page=edit_listing&id=<?= (int)$r['id'] ?>" style="width:auto;padding:5px 12px;font-size:12px;">Edit</a>
                    <?php endif; ?>
                    <a class="btn secondary" href="index.php?page=auction&id=<?= (int)$r['id'] ?>" style="width:auto;padding:5px 12px;font-size:12px;">View</a>
                    <?php if ($r['status'] === 'ended' && (int)$r['bid_count'] === 0): ?>
                        <a class="btn secondary" href="index.php?page=relist&id=<?= (int)$r['id'] ?>" style="width:auto;padding:5px 12px;font-size:12px;">Relist</a>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>
>>>>>>> origin/moderator/features
