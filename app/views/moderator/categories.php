<<<<<<< HEAD
<div class="card"><h1>Categories</h1><form method="post"><input type="hidden" name="action" value="add"><input name="name" placeholder="Category name" required><input name="description" placeholder="Description"><input type="number" name="parent_id" placeholder="Parent ID optional"><button>Add Category</button></form><table><tr><th>ID</th><th>Name</th><th>Description</th><th>Parent</th></tr><?php foreach($rows as $r): ?><tr><td><?= e($r['id']) ?></td><td><?= e($r['name']) ?></td><td><?= e($r['description']) ?></td><td><?= e($r['parent_id']) ?></td></tr><?php endforeach; ?></table></div><div class="card"><h2>Merge/Delete</h2><form method="post"><input type="hidden" name="action" value="merge"><input type="number" name="source_id" placeholder="Source category ID"><input type="number" name="dest_id" placeholder="Destination category ID"><button>Merge</button></form><form method="post"><input type="hidden" name="action" value="delete"><input type="number" name="id" placeholder="Empty category ID"><button class="danger">Delete Empty</button></form></div>
=======
<?php if ($message): ?>
    <div class="alert <?= e($message_type) ?>"><?= e($message) ?></div>
<?php endif; ?>

<h1>Category Taxonomy Management</h1>

<!-- Add Category / Subcategory -->
<div class="card">
    <h2>Add Category or Subcategory</h2>
    <form method="post" style="max-width:500px;">
        <input type="hidden" name="action" value="add">
        <label style="font-weight:600;display:block;margin-bottom:2px;">Name <span style="color:#ef4444;">*</span></label>
        <input type="text" name="name" placeholder="Category name" required>
        <label style="font-weight:600;display:block;margin-bottom:2px;">Description</label>
        <input type="text" name="description" placeholder="Optional description">
        <label style="font-weight:600;display:block;margin-bottom:2px;">Parent Category <span style="font-weight:400;color:#6b7280;">(leave blank for top-level)</span></label>
        <select name="parent_id" style="margin-bottom:12px;">
            <option value="">— None (top-level category) —</option>
            <?php foreach ($rows as $r): if (!$r['parent_id']): ?>
                <option value="<?= (int)$r['id'] ?>"><?= e($r['name']) ?></option>
            <?php endif; endforeach; ?>
        </select>
        <button class="success" style="width:auto;padding:9px 24px;">Add Category</button>
    </form>
</div>

<!-- Rename Category -->
<div class="card">
    <h2>Rename Category</h2>
    <form method="post" style="max-width:500px;">
        <input type="hidden" name="action" value="rename">
        <label style="font-weight:600;display:block;margin-bottom:2px;">Category to Rename</label>
        <select name="id" style="margin-bottom:10px;" onchange="prefillRename(this)">
            <option value="">— Select category —</option>
            <?php foreach ($rows as $r): ?>
                <option value="<?= (int)$r['id'] ?>" data-name="<?= e($r['name']) ?>" data-desc="<?= e($r['description'] ?? '') ?>">
                    <?= e($r['name']) ?><?= $r['parent_id'] ? ' (subcategory)' : '' ?> [ID: <?= (int)$r['id'] ?>]
                </option>
            <?php endforeach; ?>
        </select>
        <label style="font-weight:600;display:block;margin-bottom:2px;">New Name <span style="color:#ef4444;">*</span></label>
        <input type="text" name="name" id="rename-name" placeholder="New category name" required>
        <label style="font-weight:600;display:block;margin-bottom:2px;">New Description</label>
        <input type="text" name="description" id="rename-desc" placeholder="New description">
        <button class="secondary" style="width:auto;padding:9px 24px;">Rename Category</button>
    </form>
</div>

<!-- Merge Categories -->
<div class="card">
    <h2>Merge Categories</h2>
    <p style="color:#6b7280;font-size:13px;margin-top:0;">All listings from the <strong>source</strong> category will be moved to the <strong>destination</strong> category. The source category will then be deleted.</p>
    <form method="post" style="max-width:500px;">
        <input type="hidden" name="action" value="merge">
        <label style="font-weight:600;display:block;margin-bottom:2px;">Source Category (will be deleted)</label>
        <select name="source_id" required style="margin-bottom:10px;">
            <option value="">— Select source —</option>
            <?php foreach ($rows as $r): ?>
                <option value="<?= (int)$r['id'] ?>"><?= e($r['name']) ?> [ID: <?= (int)$r['id'] ?>]</option>
            <?php endforeach; ?>
        </select>
        <label style="font-weight:600;display:block;margin-bottom:2px;">Destination Category (listings moved here)</label>
        <select name="dest_id" required style="margin-bottom:12px;">
            <option value="">— Select destination —</option>
            <?php foreach ($rows as $r): ?>
                <option value="<?= (int)$r['id'] ?>"><?= e($r['name']) ?> [ID: <?= (int)$r['id'] ?>]</option>
            <?php endforeach; ?>
        </select>
        <button class="danger" style="width:auto;padding:9px 24px;" onclick="return confirm('This will permanently merge the categories. Continue?')">Merge Categories</button>
    </form>
</div>

<!-- Delete Empty Category -->
<div class="card">
    <h2>Delete Empty Category</h2>
    <p style="color:#6b7280;font-size:13px;margin-top:0;">Only categories with no listings can be deleted.</p>
    <form method="post" style="max-width:500px;">
        <input type="hidden" name="action" value="delete">
        <label style="font-weight:600;display:block;margin-bottom:2px;">Category to Delete</label>
        <select name="id" required style="margin-bottom:12px;">
            <option value="">— Select category —</option>
            <?php foreach ($rows as $r): ?>
                <option value="<?= (int)$r['id'] ?>"><?= e($r['name']) ?> [ID: <?= (int)$r['id'] ?>]</option>
            <?php endforeach; ?>
        </select>
        <button class="danger" style="width:auto;padding:9px 24px;" onclick="return confirm('Delete this category? This only works if it has no listings.')">Delete Empty Category</button>
    </form>
</div>

<!-- Category List -->
<div class="card">
    <h2>All Categories</h2>
    <?php if (empty($rows)): ?>
        <p style="color:#6b7280;">No categories found.</p>
    <?php else: ?>
    <?php
        $top = array_filter($rows, fn($r) => !$r['parent_id']);
        $subs = [];
        foreach ($rows as $r) { if ($r['parent_id']) $subs[$r['parent_id']][] = $r; }
    ?>
    <table>
        <tr><th>ID</th><th>Name</th><th>Description</th><th>Type</th></tr>
        <?php foreach ($top as $r): ?>
        <tr style="background:#f8fafc;">
            <td><?= (int)$r['id'] ?></td>
            <td><strong><?= e($r['name']) ?></strong></td>
            <td><?= e($r['description'] ?? '') ?></td>
            <td><span class="badge" style="background:#dbeafe;color:#1d4ed8;">Top Level</span></td>
        </tr>
        <?php if (isset($subs[$r['id']])): foreach ($subs[$r['id']] as $s): ?>
        <tr>
            <td style="color:#9ca3af;"><?= (int)$s['id'] ?></td>
            <td style="padding-left:28px;">↳ <?= e($s['name']) ?></td>
            <td><?= e($s['description'] ?? '') ?></td>
            <td><span class="badge" style="background:#f3e8ff;color:#6b21a8;">Subcategory of <?= e($r['name']) ?></span></td>
        </tr>
        <?php endforeach; endif; ?>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>

<script>
function prefillRename(sel) {
    var opt = sel.options[sel.selectedIndex];
    document.getElementById('rename-name').value = opt.dataset.name || '';
    document.getElementById('rename-desc').value = opt.dataset.desc || '';
}
</script>
>>>>>>> origin/moderator/features
