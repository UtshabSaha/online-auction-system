<div class="auth-panel">
    <h1>Login</h1>
    <p>Use your email and password to open the correct role dashboard.</p>

    <?php if (!empty($error)): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?page=login" class="form-card">
        <label>Email</label>
        <input type="email" name="email" value="<?= e($old_email ?? '') ?>" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <p class="small-link">No account yet? <a href="index.php?page=register">Register as buyer</a></p>
</div>
