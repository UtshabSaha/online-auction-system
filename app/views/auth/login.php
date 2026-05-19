<div class="auth-panel auth-landing">
    <div class="login-card landing-card">
        <div class="landing-heading">
            <span class="landing-kicker">Timed online auctions</span>
            <h1>Auction Hub</h1>
            <p>Login to manage your bids, listings, moderation, and admin tools.</p>
        </div>

        <div class="landing-highlights">
            <span>Live bidding</span>
            <span>Verified sellers</span>
            <span>Secure accounts</span>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?page=login" class="form-card">
            <label>Email</label>
            <input type="text" name="email" value="<?= e($old_email ?? '') ?>">

            <label>Password</label>
            <input type="password" name="password">

            <button type="submit">Login</button>
        </form>

        <p class="small-link">No account yet? <a href="index.php?page=register">Click here to register</a></p>
    </div>
</div>
