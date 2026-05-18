<<<<<<< HEAD
<div class="card">
    <h1>Browse Active Auctions</h1>

    <div class="filters">
        <input id="q" type="text" placeholder="Search keyword" onkeyup="searchAuctions()">

        <select id="category" onchange="searchAuctions()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <select id="condition" onchange="searchAuctions()">
            <option value="">Any Condition</option>
            <option value="new">New</option>
            <option value="like_new">Like New</option>
            <option value="good">Good</option>
            <option value="fair">Fair</option>
        </select>

        <input id="min" type="number" placeholder="Min price" onkeyup="searchAuctions()" onchange="searchAuctions()">
        <input id="max" type="number" placeholder="Max price" onkeyup="searchAuctions()" onchange="searchAuctions()">

        <select id="time" onchange="searchAuctions()">
            <option value="">Any Time Remaining</option>
            <option value="1h">Ending within 1 hour</option>
            <option value="24h">Ending within 24 hours</option>
            <option value="7d">Ending within 7 days</option>
        </select>
    </div>
</div>

<div id="auctionGrid" class="grid">
    <?php foreach ($listings as $l): ?>
        <div class="card auction-card">
            <img src="<?= e($l['image_path'] ?: 'assets/images/auction-placeholder.png') ?>" alt="Auction Image">
            <h3><?= e($l['title']) ?></h3>
            <p><?= e($l['category_name']) ?> - <?= e($l['condition']) ?></p>
            <p><strong>Current Bid:</strong> <?= e($l['current_bid']) ?></p>
            <p><strong>Time Remaining:</strong> <span class="countdown" data-countdown="<?= e($l['end_datetime']) ?>"><?= e($l['end_datetime']) ?></span></p>
            <a class="btn" href="index.php?page=auction&id=<?= $l['id'] ?>">View Auction</a>
        </div>
    <?php endforeach; ?>
</div>
=======
<div class="card"><h1>Browse Active Auctions</h1><div class="filters"><input id="q" placeholder="Search keyword" onkeyup="searchAuctions()"><select id="category" onchange="searchAuctions()"><option value="">All categories</option><?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select><select id="condition" onchange="searchAuctions()"><option value="">Any condition</option><option value="new">New</option><option value="like_new">Like New</option><option value="good">Good</option><option value="fair">Fair</option></select><input id="min" type="number" placeholder="Min price" onkeyup="searchAuctions()"><input id="max" type="number" placeholder="Max price" onkeyup="searchAuctions()"></div></div><div id="auctionGrid" class="grid"><?php foreach($listings as $l): ?><div class="card auction-card"><img src="<?= e($l['image_path'] ? base_url($l['image_path']) : base_url('assets/images/no-image.png')) ?>" onerror="this.onerror=null;this.src='<?= base_url('assets/images/no-image.png') ?>';" alt=""><h3><?= e($l['title']) ?></h3><p><?= e($l['category_name']) ?> · <?= e($l['condition']) ?></p><p><strong>Current bid:</strong> <?= e($l['current_bid']) ?></p><p><strong>Ends:</strong> <?= e($l['end_datetime']) ?></p><a class="btn" href="index.php?page=auction&id=<?= $l['id'] ?>">View Auction</a></div><?php endforeach; ?></div>
>>>>>>> origin/moderator/features
