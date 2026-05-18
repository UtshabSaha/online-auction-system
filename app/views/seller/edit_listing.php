<div class="card">
    <h1>Edit Listing</h1>

    <?php if ($message): ?>
        <div class="alert"><?= e($message) ?></div>
    <?php endif; ?>

    <!-- Show existing images -->
    <?php if (!empty($images)): ?>
    <div style="margin-bottom:16px;">
        <label style="font-weight:600;display:block;margin-bottom:6px;">Current Images</label>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <?php foreach ($images as $img): ?>
                <img src="<?= e(base_url($img['image_path'])) ?>"
                     onerror="this.onerror=null;this.src='<?= base_url('assets/images/no-image.png') ?>';"
                     alt="Listing image"
                     style="max-width:140px;max-height:120px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb;">
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

        <label>Title</label>
        <input name="title" value="<?= e($listing['title']) ?>" required>

        <label>Description</label>
        <textarea name="description" rows="4"><?= e($listing['description']) ?></textarea>

        <label>Category</label>
        <select name="category_id">
            <?php foreach ($cats as $c): ?>
                <!-- FIX: pre-select the listing's current category -->
                <option value="<?= (int)$c['id'] ?>"
                    <?= (int)$c['id'] === (int)$listing['category_id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Condition</label>
        <select name="condition">
            <?php
                // FIX: pre-select the listing's current condition
                $conditions = [
                    'good'     => 'Good',
                    'new'      => 'New',
                    'like_new' => 'Like New',
                    'fair'     => 'Fair',
                ];
            ?>
            <?php foreach ($conditions as $val => $label): ?>
                <option value="<?= $val ?>" <?= $val === $listing['condition'] ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Starting Price</label>
        <input type="number" step="0.01" name="starting_price"
               value="<?= e($listing['starting_price']) ?>" required>

        <label>Reserve Price <span style="font-weight:400;color:#6b7280;">(optional)</span></label>
        <input type="number" step="0.01" name="reserve_price"
               value="<?= e($listing['reserve_price'] ?? '') ?>">

        <label>End Date &amp; Time</label>
        <input type="datetime-local" name="end_datetime"
               value="<?= date('Y-m-d\TH:i', strtotime($listing['end_datetime'])) ?>" required>

        <!-- FIX: image upload field -->
        <label style="margin-top:10px;display:block;font-weight:600;">
            Add Image
            <span style="font-weight:400;color:#6b7280;">(optional — JPG, PNG, WebP, max 2 MB)</span>
        </label>
        <input type="file" name="single_image" accept=".jpg,.jpeg,.png,.webp"
               style="margin-bottom:12px;">

        <button>Save if zero bids</button>
    </form>
</div>