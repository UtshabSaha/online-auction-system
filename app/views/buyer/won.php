<div class="card">
    <h1>Won Auctions</h1>
    <table>
        <tr><th>Auction</th><th>Winning Amount</th><th>Seller Communication</th><th>Action</th></tr>
        <?php foreach($rows as $r): ?>
            <tr>
                <td><?= e($r['title']) ?></td>
                <td><?= e($r['winning_amount']) ?></td>
                <td>
                    <strong><?= e($r['seller_name']) ?></strong><br>
                    Email: <a href="mailto:<?= e($r['seller_email']) ?>"><?= e($r['seller_email']) ?></a><br>
                    Phone: <?= e($r['seller_phone']) ?>
                </td>
                <td><a class="btn" href="index.php?page=review_seller">Review Seller</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
