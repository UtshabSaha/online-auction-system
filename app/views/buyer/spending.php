<?php
$totalBids = (int)($stats['total_bids'] ?? 0);
$wins = (int)($stats['wins'] ?? 0);
$winRate = $totalBids > 0 ? round(($wins / $totalBids) * 100, 2) : 0;
?>

<h1>Spending History</h1>

<div class="grid">
    <div class="card">
        <div class="stat"><?= e($totalBids) ?></div>
        <p>Total bids placed</p>
    </div>
    <div class="card">
        <div class="stat"><?= e($wins) ?></div>
        <p>Total wins</p>
    </div>
    <div class="card">
        <div class="stat"><?= e($stats['total_spent'] ?? 0) ?></div>
        <p>Total spent on won auctions</p>
    </div>
    <div class="card">
        <div class="stat"><?= e($winRate) ?>%</div>
        <p>Win rate</p>
    </div>
</div>
