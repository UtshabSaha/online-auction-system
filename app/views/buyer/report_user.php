<div class="card">
    <h1>Report User</h1>

    <?php if ($message): ?>
        <div class="alert"><?= e($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <label>User</label>
        <select name="reported_user_id" required>
            <option value="">Select user</option>
            <?php foreach ($users as $user): ?>
                <option value="<?= $user['id'] ?>" <?= $selectedUserId === (int)$user['id'] ? 'selected' : '' ?>>
                    <?= e($user['name']) ?> (<?= e($user['role']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label>Reason</label>
        <select name="reason" required>
            <option value="">Select reason</option>
            <option value="Harassment or abuse">Harassment or abuse</option>
            <option value="Fraud or scam">Fraud or scam</option>
            <option value="Payment or collection issue">Payment or collection issue</option>
            <option value="Other problematic behavior">Other problematic behavior</option>
        </select>

        <label>Description</label>
        <textarea name="description" minlength="10" required></textarea>

        <button>Submit Report</button>
    </form>
</div>
