<div class="card">
    <h1>My Watchlist</h1>
    <table>
        <tr><th>Auction</th><th>Current Bid</th><th>Time Remaining</th><th>Status</th><th>Action</th></tr>
        <?php foreach($rows as $r): ?>
            <tr>
                <td><a href="index.php?page=auction&id=<?= $r['listing_id'] ?>"><?= e($r['title']) ?></a></td>
                <td><?= e($r['current_bid']) ?></td>
                <td><span class="countdown" data-countdown="<?= e($r['end_datetime']) ?>"><?= e($r['end_datetime']) ?></span></td>
                <td><?= e($r['status']) ?></td>
                <td><button class="secondary" onclick="toggleWatch(<?= $r['listing_id'] ?>); setTimeout(function(){location.reload();}, 500)">Remove</button></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
