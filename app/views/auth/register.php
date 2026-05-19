<div class="auth-panel">
    <h1>Buyer Registration</h1>
    <p>Create a buyer account. Seller access is requested after login.</p>

    <?php if (!empty($error)): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?page=register" class="form-card">
        <label>Name</label>
        <input type="text" name="name" value="<?= e($old['name'] ?? '') ?>">

        <label>Email</label>
        <input type="text" name="email" value="<?= e($old['email'] ?? '') ?>">

        <label>Phone</label>
        <input type="text" name="phone" value="<?= e($old['phone'] ?? '') ?>">

        <label>Bio</label>
        <textarea name="bio"><?= e($old['bio'] ?? '') ?></textarea>

        <label>Password</label>
        <input type="password" name="password">

        <label>Confirm Password</label>
        <input type="password" name="confirm_password">

        <button type="submit">Create Account</button>
    </form>

    <p class="small-link">Already registered? <a href="index.php?page=login">Login</a></p>
</div>
