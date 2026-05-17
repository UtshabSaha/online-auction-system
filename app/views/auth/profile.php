<div class="card">
    <h1>Profile</h1>

    <?php if($message): ?>
        <div class="alert success-msg"><?= e($message) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="profile-avatar-section">
            <div class="profile-avatar">
                <?php if(!empty($user['profile_pic'])): ?>
                    <img src="<?= e($user['profile_pic']) ?>" alt="Profile">
                <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=2563eb&color=fff&size=200" alt="Profile">
                <?php endif; ?>

                <label for="profileUpload" class="edit-avatar-btn"><i class="fas fa-pen"></i></label>
                <input type="file" name="profile_pic" id="profileUpload" accept="image/jpeg,image/png,image/webp" hidden>
            </div>
        </div>

        <script>
            document.getElementById('profileUpload').addEventListener('change', function () {
                var file = this.files[0];
                if (!file) return;
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.querySelector('.profile-avatar img').src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        </script>

        <label>Name</label>
        <input name="name" value="<?= e($user['name']) ?>" required>

        <label>Phone</label>
        <input name="phone" value="<?= e($user['phone']) ?>">

        <label>Bio</label>
        <textarea name="bio"><?= e($user['bio']) ?></textarea>

        <button>Update Profile</button>
    </form>
</div>

<div class="card">
    <h2>My Reputation</h2>
    <p><strong>Reputation Score:</strong> <?= e($user['reputation_score']) ?></p>
    <h3>Reviews Received</h3>
    <?php if(empty($reviews)): ?>
        <p>No reviews received yet.</p>
    <?php else: ?>
        <table>
            <tr><th>Auction</th><th>Reviewer</th><th>Rating</th><th>Review</th><th>Response</th></tr>
            <?php foreach($reviews as $review): ?>
                <tr>
                    <td><?= e($review['title']) ?></td>
                    <td><?= e($review['reviewer_name']) ?></td>
                    <td><?= e($review['rating']) ?>/5</td>
                    <td><?= e($review['review_text']) ?></td>
                    <td><?= e($review['response_text']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Change Password</h2>
    <form method="post">
        <input type="hidden" name="change_password" value="1">

        <label>New Password</label>
        <input type="password" name="new_password" minlength="8" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" minlength="8" required>

        <button>Change Password</button>
    </form>
</div>