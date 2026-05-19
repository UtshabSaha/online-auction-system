<div class="card">
    <h1>Review Seller</h1>

    <?php if ($message): ?>
        <div class="alert"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if (empty($reviewable)): ?>
        <p>No completed won auctions are waiting for a seller review.</p>
    <?php else: ?>
        <form method="post">
            <label>Completed Auction</label>
            <select name="listing_id" id="reviewListing" onchange="document.getElementById('sellerId').value=this.options[this.selectedIndex].dataset.seller">
                <option value="">Select auction</option>
                <?php foreach ($reviewable as $row): ?>
                    <option value="<?= $row['listing_id'] ?>" data-seller="<?= $row['seller_id'] ?>">
                        <?= e($row['title']) ?> - <?= e($row['seller_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="seller_id" id="sellerId">

            <label>Rating</label>
            <input type="number" name="rating">

            <label>Review</label>
            <textarea name="review_text"></textarea>

            <button>Submit Review</button>
        </form>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Sent Reviews</h2>
    <?php if (empty($sentReviews)): ?>
        <p>No sent reviews yet.</p>
    <?php else: ?>
        <table>
            <tr><th>Auction</th><th>Seller</th><th>Rating</th><th>Review</th><th>Response</th></tr>
            <?php foreach ($sentReviews as $review): ?>
                <tr>
                    <td><?= e($review['title']) ?></td>
                    <td><?= e($review['reviewee_name']) ?></td>
                    <td><?= e($review['rating']) ?>/5</td>
                    <td><?= e($review['review_text']) ?></td>
                    <td><?= e($review['response_text']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
