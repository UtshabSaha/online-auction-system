<<<<<<< HEAD
<?php if(!$listing): ?>
    <div class="card">Auction not found.</div>
    <?php return; ?>
<?php endif; ?>

<div class="card">
    <h1><?= e($listing['title']) ?></h1>
    <p><?= e($listing['description']) ?></p>
    <p><strong>Seller:</strong> <?= e($listing['seller_name']) ?> | Reputation: <?= e($listing['reputation_score']) ?></p>
    <p><strong>Condition:</strong> <?= e($listing['condition']) ?> | <strong>Category:</strong> <?= e($listing['category_name']) ?></p>
    <p><strong>Current Bid:</strong> <span id="currentBid"><?= e($listing['current_bid']) ?></span></p>
    <p><strong>Ends:</strong> <span class="countdown" data-countdown="<?= e($listing['end_datetime']) ?>"><?= e($listing['end_datetime']) ?></span></p>

    <?php foreach($images as $img): ?>
        <img src="<?= e($img['image_path']) ?>" alt="Auction image" style="max-width:180px;border-radius:6px;margin:4px">
    <?php endforeach; ?>

    <?php if(is_logged_in() && (int)$listing['seller_id'] === (int)current_user_id()): ?>
        <hr>
        <div class="alert">This is your listing. Live bid activity refreshes below.</div>
    <?php elseif(is_logged_in() && current_role() === 'buyer'): ?>
        <hr>
        <h3>Place Bid</h3>
        <input id="bidAmount" type="number" step="0.01" placeholder="Bid amount">
        <button onclick="placeBid(<?= $listing['id'] ?>)">Submit Bid AJAX</button>
        <p id="bidMessage"></p>

        <h3>Auto Bid</h3>
        <input id="autoBidAmount" type="number" step="0.01" placeholder="Maximum bid">
        <button class="secondary" onclick="setAutoBid(<?= $listing['id'] ?>)">Set Auto Bid</button>
        <p id="autoBidMessage"></p>

        <button class="success" onclick="toggleWatch(<?= $listing['id'] ?>)">Add/Remove Watchlist</button>
    <?php endif; ?>
</div>

<div class="card" data-refresh-bids="<?= $listing['id'] ?>">
    <h2>Bid History</h2>
    <p><strong>Total bids:</strong> <span id="bidCount"><?= count($bids) ?></span></p>
    <table>
        <thead>
            <tr><th>Buyer</th><th>Amount</th><th>Type</th><th>Time</th></tr>
        </thead>
        <tbody id="bidHistory">
            <?php foreach($bids as $b): ?>
                <tr>
                    <td><?= e($b['buyer_name']) ?></td>
                    <td><?= e($b['amount']) ?></td>
                    <td><?= (int)$b['is_auto_bid'] ? 'Auto' : 'Manual' ?></td>
                    <td><?= e($b['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
=======
<div class="card"><h1>Browse Active Auctions</h1><div class="filters"><input id="q" placeholder="Search keyword" onkeyup="searchAuctions()"><select id="category" onchange="searchAuctions()"><option value="">All categories</option><?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select><select id="condition" onchange="searchAuctions()"><option value="">Any condition</option><option value="new">New</option><option value="like_new">Like New</option><option value="good">Good</option><option value="fair">Fair</option></select><input id="min" type="number" placeholder="Min price" onkeyup="searchAuctions()"><input id="max" type="number" placeholder="Max price" onkeyup="searchAuctions()"></div></div><div id="auctionGrid" class="grid"><?php foreach($listings as $l): ?><div class="card auction-card"><img src="<?= e($l['image_path'] ? base_url($l['image_path']) : base_url('assets/images/no-image.png')) ?>" onerror="this.onerror=null;this.src='<?= base_url('assets/images/no-image.png') ?>';" alt=""><h3><?= e($l['title']) ?></h3><p><?= e($l['category_name']) ?> · <?= e($l['condition']) ?></p><p><strong>Current bid:</strong> <?= e($l['current_bid']) ?></p><p><strong>Ends:</strong> <?= e($l['end_datetime']) ?></p><a class="btn" href="index.php?page=auction&id=<?= $l['id'] ?>">View Auction</a></div><?php endforeach; ?></div>
>>>>>>> origin/moderator/features
