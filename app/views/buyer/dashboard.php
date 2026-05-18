<h1>Buyer Dashboard</h1>

<div class="grid">
    <div class="card">
        <div class="stat"><?= count($myBids) ?></div>
        <p>Auctions with my bids</p>
    </div>
    <div class="card">
        <div class="stat"><?= count($listings) ?></div>
        <p>Active auctions</p>
    </div>
    <div class="card">
        <div class="stat"><?= count($notifications) ?></div>
        <p>Notifications</p>
    </div>
</div>

<div class="card">
    <h2>Notifications</h2>
    <div id="buyerNotifications">
        <?php if (empty($notifications)): ?>
            <p>No notifications right now.</p>
        <?php else: ?>
            <?php foreach ($notifications as $note): ?>
                <p>
                    <strong><?= e(ucwords(str_replace('_', ' ', $note['type']))) ?>:</strong>
                    <a href="index.php?page=auction&id=<?= $note['id'] ?>"><?= e($note['title']) ?></a>
                    - <?= e($note['message']) ?>
                </p>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h2>Quick Links</h2>
    <a class="btn" href="index.php?page=browse">Browse Auctions</a>
    <a class="btn secondary" href="index.php?page=watchlist">Watchlist</a>
    <a class="btn secondary" href="index.php?page=my_bids">My Bids</a>
    <a class="btn secondary" href="index.php?page=won_auctions">Won Auctions</a>
</div>
