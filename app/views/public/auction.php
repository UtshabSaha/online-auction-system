<?php if(!$listing): ?>
    <div class="card">Auction not found.</div>
    <?php return; ?>
<?php endif; ?>

<div class="card">

    <h1><?= e($listing['title']) ?></h1>

    <p><?= e($listing['description']) ?></p>

    <p>
        <strong>Seller:</strong> <?= e($listing['seller_name']) ?>
        |
        Reputation: <?= e($listing['reputation_score']) ?>
    </p>

    <p>
        <strong>Condition:</strong> <?= e($listing['condition']) ?>
        |
        <strong>Category:</strong> <?= e($listing['category_name']) ?>
    </p>

    <p>
        <strong>Current Bid:</strong>
        <span id="currentBid"><?= e($listing['current_bid']) ?></span>
    </p>

    <p>
        <strong>Ends:</strong>
        <span class="countdown"><?= e($listing['end_datetime']) ?></span>
    </p>

    <!-- Images -->
    <?php foreach($images as $img): ?>
        <img 
            src="/online-auction-system/<?= e($img['image_path']) ?>" 
            style="max-width:180px;border-radius:6px;margin:4px"
        >
    <?php endforeach; ?>

    <?php if(is_logged_in()): ?>

        <hr>

        <h3>Place Bid</h3>

        <input 
            id="bidAmount" 
            type="number" 
            step="0.01" 
            placeholder="Bid amount"
        >

        <button onclick="placeBid(<?= $listing['id'] ?>)">
            Submit
        </button>

        <p id="bidMessage"></p>

        <h3>Auto Bid</h3>

        <input 
            id="autoBidAmount" 
            type="number" 
            step="0.01" 
            placeholder="Maximum bid"
        >

        <button 
            class="secondary"
            onclick="setAutoBid(<?= $listing['id'] ?>)"
        >
            Set Auto Bid
        </button>

        <p id="autoBidMessage"></p>

        <button 
            class="success"
            onclick="toggleWatch(<?= $listing['id'] ?>)"
        >
            Add/Remove Watchlist
        </button>

    <?php endif; ?>

</div>

<div class="card" data-refresh-bids="<?= $listing['id'] ?>">

    <h2>Bid History</h2>

    <table>

        <thead>
            <tr>
                <th>Buyer</th>
                <th>Amount</th>
                <th>Time</th>
            </tr>
        </thead>

        <tbody id="bidHistory">

            <?php foreach($bids as $b): ?>
                <tr>
                    <td><?= e($b['buyer_name']) ?></td>
                    <td><?= e($b['amount']) ?></td>
                    <td><?= e($b['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>

        </tbody>

    </table>

</div>