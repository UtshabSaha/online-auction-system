<div class="card">
    <h1>Won Auctions</h1>

    <table>
        <tr>
            <th>Auction</th>
            <th>Winning Amount</th>
            <th>Seller Contact</th>
            <th>Payment / Collection Notes</th>
            <th>Action</th>
        </tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><a href="index.php?page=auction&id=<?= $r['id'] ?>"><?= e($r['title']) ?></a></td>
                <td><?= e($r['winning_amount']) ?></td>
                <td>
                    <strong><?= e($r['seller_name']) ?></strong><br>
                    Email: <a href="mailto:<?= e($r['seller_email']) ?>"><?= e($r['seller_email']) ?></a><br>
                    Phone: <?= e($r['seller_phone']) ?>
                </td>
                <td>Contact the seller using the details shown here to confirm payment and collection instructions.</td>
                <td>
                    <a class="btn" href="index.php?page=review_seller">Review Seller</a>
                    <a class="btn secondary" href="index.php?page=report_user&user_id=<?= $r['seller_id'] ?>">Report Seller</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
