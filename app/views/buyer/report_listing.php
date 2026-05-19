<div class="card">
    <h1>Report Listing</h1>

    <?php if ($message): ?>
        <div class="alert"><?= e($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Listing</label>
        <select name="listing_id">
            <option value="">Select listing</option>
            <?php foreach ($listings as $listing): ?>
                <option value="<?= $listing['id'] ?>" <?= $selectedListingId === (int)$listing['id'] ? 'selected' : '' ?>>
                    <?= e($listing['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Reason</label>
        <select name="reason">
            <option value="">Select reason</option>
            <option value="Misleading description">Misleading description</option>
            <option value="Prohibited item">Prohibited item</option>
            <option value="Suspicious bidding">Suspicious bidding</option>
            <option value="Other policy violation">Other policy violation</option>
        </select>

        <label>Description</label>
        <textarea name="description"></textarea>

        <button>Submit Report</button>
    </form>
</div>
