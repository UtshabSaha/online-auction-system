<?php
if (is_logged_in()) { redirect_to('index.php?page=home'); }

$error = '';
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';
    $old_email = $email;

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $conn->prepare('SELECT id, name, password_hash, role, seller_verified, is_active FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Incorrect email or password.';
        } elseif (!$user['is_active']) {
            $error = 'Your account has been deactivated. Please contact support.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']         = $user['id'];
            $_SESSION['user_name']       = $user['name'];
            $_SESSION['user_role']       = $user['role'];
            $_SESSION['seller_verified'] = $user['seller_verified'];

            switch ($user['role']) {
                case 'admin':     redirect_to('index.php?page=admin_dashboard');     break;
                case 'moderator': redirect_to('index.php?page=moderator_dashboard'); break;
                case 'seller':    redirect_to('index.php?page=seller_dashboard');    break;
                default:          redirect_to('index.php?page=buyer_dashboard');     break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — Online Auction</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --navy:   #0f1b35;
  --ink:    #1a2d52;
  --gold:   #d4a84b;
  --gold-l: #e8c97a;
  --cream:  #faf8f4;
  --muted:  #6b7a99;
  --border: #dde3ef;
  --red:    #c0392b;
  --radius: 12px;
  --trans:  0.22s ease;
}

html, body { height: 100%; font-family: 'DM Sans', sans-serif; background: var(--cream); }

/* ── LAYOUT ── */
.page {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 100vh;
}

/* ── LEFT PANEL ── */
.panel-left {
  background: var(--navy);
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 60px 48px;
  overflow: hidden;
}
.panel-left::before,
.panel-left::after {
  content: '';
  position: absolute;
  border-radius: 50%;
  border: 1px solid rgba(212,168,75,0.18);
}
.panel-left::before { width: 520px; height: 520px; top: -160px; right: -200px; }
.panel-left::after  { width: 340px; height: 340px; bottom: -80px; left: -120px; }
.ring-mid {
  position: absolute;
  width: 280px; height: 280px;
  border-radius: 50%;
  border: 1px solid rgba(212,168,75,0.12);
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
}
.left-content {
  position: relative;
  z-index: 1;
  text-align: center;
  animation: fadeUp 0.7s ease both;
}
.brand-mark {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 48px;
  text-decoration: none;
}
.brand-icon {
  width: 44px; height: 44px;
  background: var(--gold);
  border-radius: 10px;
  display: grid;
  place-items: center;
}
.brand-name {
  font-family: 'Playfair Display', serif;
  font-size: 20px;
  color: #fff;
  letter-spacing: 0.02em;
}
.panel-headline {
  font-family: 'Playfair Display', serif;
  font-size: clamp(30px, 3vw, 44px);
  line-height: 1.18;
  color: #fff;
  margin-bottom: 20px;
}
.panel-headline em { color: var(--gold); font-style: normal; }
.panel-sub {
  font-size: 15px;
  color: rgba(255,255,255,0.55);
  line-height: 1.7;
  max-width: 300px;
  margin: 0 auto 48px;
}

/* Demo accounts box */
.demo-box {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(212,168,75,0.25);
  border-radius: 10px;
  padding: 20px 24px;
  text-align: left;
  width: 100%;
  max-width: 320px;
}
.demo-box h4 {
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--gold);
  margin-bottom: 12px;
}
.demo-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 7px 0;
  border-bottom: 1px solid rgba(255,255,255,0.06);
  cursor: pointer;
  transition: background var(--trans);
  border-radius: 4px;
  padding-left: 4px; padding-right: 4px;
}
.demo-row:last-child { border-bottom: none; }
.demo-row:hover { background: rgba(212,168,75,0.08); }
.demo-email { font-size: 12.5px; color: rgba(255,255,255,0.75); }
.demo-role  { font-size: 11px; color: rgba(255,255,255,0.35); }
.demo-hint  {
  font-size: 11px;
  color: rgba(255,255,255,0.3);
  margin-top: 10px;
  text-align: center;
}

/* ── RIGHT PANEL ── */
.panel-right {
  background: var(--cream);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 32px;
}
.form-box {
  width: 100%;
  max-width: 400px;
  animation: fadeUp 0.6s 0.1s ease both;
}
.form-title {
  font-family: 'Playfair Display', serif;
  font-size: 32px;
  color: var(--navy);
  margin-bottom: 6px;
}
.form-sub {
  font-size: 14px;
  color: var(--muted);
  margin-bottom: 32px;
}
.form-sub a {
  color: var(--ink);
  font-weight: 600;
  text-decoration: none;
  border-bottom: 1px solid var(--gold);
}
.form-sub a:hover { color: var(--gold); }

/* Error */
.error-banner {
  background: #fef2f2;
  border: 1px solid #fca5a5;
  border-radius: var(--radius);
  padding: 13px 16px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13.5px;
  color: var(--red);
  animation: shake 0.35s ease;
}

/* Fields */
.field { margin-bottom: 18px; }
.field label {
  display: block;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--ink);
  letter-spacing: 0.04em;
  text-transform: uppercase;
  margin-bottom: 7px;
}
.input-wrap { position: relative; }
.input-wrap input {
  width: 100%;
  padding: 12px 14px;
  background: #fff;
  border: 1.5px solid var(--border);
  border-radius: 8px;
  font: 14px/1.5 'DM Sans', sans-serif;
  color: var(--navy);
  transition: border-color var(--trans), box-shadow var(--trans);
  outline: none;
  -webkit-appearance: none;
}
.input-wrap input:focus {
  border-color: var(--ink);
  box-shadow: 0 0 0 3px rgba(26,45,82,0.08);
}
.input-wrap input.has-icon { padding-right: 44px; }

.eye-btn {
  position: absolute;
  right: 12px; top: 50%; transform: translateY(-50%);
  background: none; border: none; padding: 4px;
  cursor: pointer; color: var(--muted);
  display: grid; place-items: center;
  transition: color var(--trans);
  width: auto; margin: 0;
}
.eye-btn:hover { color: var(--navy); }

/* Remember + forgot row */
.row-between {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 22px;
}
.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: var(--muted);
  cursor: pointer;
  user-select: none;
}
.checkbox-label input[type="checkbox"] {
  width: 16px; height: 16px;
  accent-color: var(--navy);
  cursor: pointer;
  margin: 0;
}
.forgot-link {
  font-size: 13px;
  color: var(--muted);
  text-decoration: none;
  border-bottom: 1px solid var(--border);
  transition: color var(--trans), border-color var(--trans);
}
.forgot-link:hover { color: var(--navy); border-color: var(--navy); }

/* Submit */
.btn-submit {
  width: 100%;
  padding: 13px;
  background: var(--navy);
  color: #fff;
  border: none;
  border-radius: 8px;
  font: 600 15px/1 'DM Sans', sans-serif;
  letter-spacing: 0.02em;
  cursor: pointer;
  transition: background var(--trans), box-shadow var(--trans), transform 0.15s;
  position: relative;
  overflow: hidden;
}
.btn-submit::after {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(212,168,75,0.18), transparent);
  opacity: 0;
  transition: opacity var(--trans);
}
.btn-submit:hover { background: var(--ink); box-shadow: 0 6px 20px rgba(15,27,53,0.22); }
.btn-submit:hover::after { opacity: 1; }
.btn-submit:active { transform: scale(0.99); }

/* ── ANIMATIONS ── */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(18px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes shake {
  0%,100% { transform: translateX(0); }
  20%     { transform: translateX(-6px); }
  40%     { transform: translateX(6px); }
  60%     { transform: translateX(-4px); }
  80%     { transform: translateX(4px); }
}

@media (max-width: 820px) {
  .page { grid-template-columns: 1fr; }
  .panel-left { display: none; }
  .panel-right { padding: 40px 20px; }
}
</style>
</head>
<body>

<div class="page">

  <!-- ── LEFT ── -->
  <aside class="panel-left">
    <div class="ring-mid"></div>
    <div class="left-content">

      <a href="<?= e(base_url('index.php')) ?>" class="brand-mark">
        <div class="brand-icon">
          <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
            <path d="M4 18L10 6l3 6 2-3 3 9" stroke="#0f1b35" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <span class="brand-name">AuctionHub</span>
      </a>

      <h1 class="panel-headline">Welcome<br>Back to <em>Your</em><br>Auctions</h1>
      <p class="panel-sub">Pick up where you left off — your bids, watchlist, and wins are waiting.</p>

      <div class="demo-box">
        <h4>Demo Accounts — click to fill</h4>
        <div class="demo-row" onclick="fillDemo('admin@example.com')">
          <span class="demo-email">admin@example.com</span>
          <span class="demo-role">Admin</span>
        </div>
        <div class="demo-row" onclick="fillDemo('moderator@example.com')">
          <span class="demo-email">moderator@example.com</span>
          <span class="demo-role">Moderator</span>
        </div>
        <div class="demo-row" onclick="fillDemo('seller@example.com')">
          <span class="demo-email">seller@example.com</span>
          <span class="demo-role">Seller</span>
        </div>
        <div class="demo-row" onclick="fillDemo('buyer@example.com')">
          <span class="demo-email">buyer@example.com</span>
          <span class="demo-role">Buyer</span>
        </div>
        <p class="demo-hint">All passwords: <strong style="color:rgba(255,255,255,0.5)">password</strong></p>
      </div>

    </div>
  </aside>

  <!-- ── RIGHT ── -->
  <main class="panel-right">
    <div class="form-box">

      <h2 class="form-title">Sign In</h2>
      <p class="form-sub">Don't have an account? <a href="<?= e(base_url('index.php?page=register')) ?>">Create one</a></p>

      <?php if ($error): ?>
      <div class="error-banner" role="alert">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" flex-shrink="0">
          <circle cx="8" cy="8" r="7.5" stroke="#c0392b"/>
          <path d="M8 4.5v4M8 11h.01" stroke="#c0392b" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <?= e($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="<?= e(base_url('index.php?page=login')) ?>">

        <div class="field">
          <label for="email">Email Address</label>
          <div class="input-wrap">
            <input type="email" id="email" name="email"
                   placeholder="you@example.com"
                   value="<?= e($old_email) ?>"
                   autocomplete="email" required>
          </div>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <input type="password" id="password" name="password"
                   placeholder="Enter your password"
                   autocomplete="current-password"
                   class="has-icon" required>
            <button type="button" class="eye-btn" id="eyeBtn" aria-label="Toggle password">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                <path d="M1 10s3.5-7 9-7 9 7 9 7-3.5 7-9 7-9-7-9-7Z" stroke="currentColor" stroke-width="1.5"/>
                <circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="row-between">
          <label class="checkbox-label">
            <input type="checkbox" name="remember"> Remember me
          </label>
          <a href="#" class="forgot-link">Forgot password?</a>
        </div>

        <button type="submit" class="btn-submit">Sign In →</button>

      </form>
    </div>
  </main>

</div>

<script>
// Eye toggle
const pwInp  = document.getElementById('password');
const eyeBtn = document.getElementById('eyeBtn');
eyeBtn.addEventListener('click', () => {
  pwInp.type = pwInp.type === 'password' ? 'text' : 'password';
});

// Click demo row → fill email + password
function fillDemo(email) {
  document.getElementById('email').value    = email;
  document.getElementById('password').value = 'password';
  document.getElementById('email').focus();
}
</script>
</body>
</html>