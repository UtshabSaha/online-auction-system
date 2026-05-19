<div class="card">
    <h1>Listing Templates</h1>
    <?php if(!empty($message)): ?><div class="alert"><?= e($message) ?></div><?php endif; ?>
    <form method="post">
        
        <label>Title</label>
        <input name="title" placeholder="Title">
        <label>Description</label>
        <textarea name="description" placeholder="Description"></textarea>
        <label>Category</label>
        <select name="category_id"><?php foreach($cats as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select>
        <label>Condition</label>
        <select name="condition"><option value="good">Good</option><option value="new">New</option><option value="like_new">Like New</option><option value="fair">Fair</option></select>
        <label>Starting Price</label>
        <input type="number" step="0.01" name="starting_price" placeholder="Starting price">
        <label>Reserve Price</label>
        <input type="number" step="0.01" name="reserve_price" placeholder="Optional reserve price">
        <button>Save Template</button>
    </form>
</div>
<div class="card">
    <table>
        <tr><th>Title</th><th>Category</th><th>Starting Price</th><th>Reserve Price</th><th>Action</th></tr>
        <?php foreach($rows as $r): ?>
            <tr>
                <td><?= e($r['title']) ?></td>
                <td><?= e($r['category_name']) ?></td>
                <td><?= e($r['starting_price']) ?></td>
                <td><?= e($r['reserve_price'] ?? '') ?></td>
                <td><a class="btn" href="index.php?page=create_listing&template_id=<?= $r['id'] ?>">Use Template</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
