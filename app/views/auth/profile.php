<div class="card">

    <h1>Profile</h1>

    <form method="post" enctype="multipart/form-data">

        <!-- PROFILE IMAGE -->

        <div class="profile-avatar-section">

            <div class="profile-avatar">

                <?php if(!empty($user['profile_pic'])): ?>

                    <img src="uploads/<?= e($user['profile_pic']) ?>" alt="Profile">

                <?php else: ?>

                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=2563eb&color=fff&size=200">

                <?php endif; ?>

                <label for="profileUpload" class="edit-avatar-btn">
                    <i class="fas fa-pen"></i>
                </label>

                <input 
                    type="file" 
                    name="profile_pic" 
                    id="profileUpload" 
                    hidden
                >

            </div>

        </div>

        <!-- NAME -->

        <label>Name</label>

        <input 
            name="name" 
            value="<?= e($user['name']) ?>" 
            required
        >

        <!-- PHONE -->

        <label>Phone</label>

        <input 
            name="phone" 
            value="<?= e($user['phone']) ?>"
        >

        <!-- BIO -->

        <label>Bio</label>

        <textarea name="bio"><?= e($user['bio']) ?></textarea>

        <button>Update Profile</button>

    </form>

</div>

<!-- PASSWORD SECTION -->

<div class="card">

    <h2>Change Password</h2>

    <?php if($message): ?>
        <div class="alert success-msg">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <input 
            type="hidden" 
            name="change_password" 
            value="1"
        >

        <label>New Password</label>

        <input 
            type="password" 
            name="new_password" 
            minlength="6" 
            required
        >

        <label>Confirm Password</label>

        <input 
            type="password" 
            name="confirm_password" 
            minlength="6" 
            required
        >

        <button>Change Password</button>

    </form>

</div>