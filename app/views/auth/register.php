<?php
require_once __DIR__ . '/../../../config/helpers.php';
require_once __DIR__ . '/../../../config/db.php';

// guests only — redirect if already logged in
if (is_logged_in()) {
    redirect_to('index.php?page=home');
}
if (is_logged_in()) { redirect_to('index.php?page=home'); }

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'name'  => trim($_POST['name']  ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'bio'   => trim($_POST['bio']   ?? ''),
    ];
    $password        = $_POST['password']         ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($old['name'] === '')           $errors[] = 'Full name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if (strlen($password) < 8)        $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $old['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'An account with this email already exists.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            'INSERT INTO users (name, email, password_hash, phone, bio, role) VALUES (?, ?, ?, ?, ?, "buyer")'
        );
        $stmt->bind_param('sssss', $old['name'], $old['email'], $hash, $old['phone'], $old['bio']);
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $stmt->close();
            session_regenerate_id(true);
            $_SESSION['user_id']          = $user_id;
            $_SESSION['user_role']        = 'buyer';
            $_SESSION['user_name']        = $old['name'];
            $_SESSION['seller_verified']  = 0;
            redirect_to('index.php?page=buyer_dashboard');
        } else {
            $errors[] = 'Registration failed. Please try again.';
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account — Online Auction</title>
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
  --green:  #1a7a4a;
  --radius: 12px;
  --trans:  0.22s ease;
}

html, body {
  height: 100%;
  font-family: 'DM Sans', sans-serif;
  background: var(--cream);
}

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

/* Decorative rings */
.panel-left::before,
.panel-left::after {
  content: '';
  position: absolute;
  border-radius: 50%;
  border: 1px solid rgba(212,168,75,0.18);
}
.panel-left::before {
  width: 520px; height: 520px;
  top: -160px; right: -200px;
}
.panel-left::after {
  width: 340px; height: 340px;
  bottom: -80px; left: -120px;
}

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
.brand-icon svg { display: block; }
.brand-name {
  font-family: 'Playfair Display', serif;
  font-size: 20px;
  color: #fff;
  letter-spacing: 0.02em;
}

.panel-headline {
  font-family: 'Playfair Display', serif;
  font-size: clamp(32px, 3.2vw, 46px);
  line-height: 1.18;
  color: #fff;
  margin-bottom: 20px;
}
.panel-headline em {
  color: var(--gold);
  font-style: normal;
}

.panel-sub {
  font-size: 15px;
  color: rgba(255,255,255,0.55);
  line-height: 1.7;
  max-width: 320px;
  margin: 0 auto 48px;
}

.perks {
  list-style: none;
  text-align: left;
  display: inline-flex;
  flex-direction: column;
  gap: 14px;
}
.perks li {
  display: flex;
  align-items: center;
  gap: 12px;
  color: rgba(255,255,255,0.75);
  font-size: 14px;
}
.perk-dot {
  width: 28px; height: 28px;
  background: rgba(212,168,75,0.15);
  border: 1px solid rgba(212,168,75,0.4);
  border-radius: 50%;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  color: var(--gold);
  font-size: 13px;
}

/* ── RIGHT PANEL ── */
.panel-right {
  background: var(--cream);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 32px;
  overflow-y: auto;
}

.form-box {
  width: 100%;
  max-width: 440px;
  animation: fadeUp 0.6s 0.1s ease both;
}

.form-title {
  font-family: 'Playfair Display', serif;
  font-size: 30px;
  color: var(--navy);
  margin-bottom: 6px;
}
.form-sub {
  font-size: 14px;
  color: var(--muted);
  margin-bottom: 28px;
}
.form-sub a {
  color: var(--ink);
  font-weight: 600;
  text-decoration: none;
  border-bottom: 1px solid var(--gold);
}
.form-sub a:hover { color: var(--gold); }

/* Error banner */
.error-banner {
  background: #fef2f2;
  border: 1px solid #fca5a5;
  border-radius: var(--radius);
  padding: 14px 16px;
  margin-bottom: 20px;
  animation: shake 0.35s ease;
}
.error-banner p {
  font-size: 13px;
  color: var(--red);
  display: flex;
  align-items: center;
  gap: 8px;
  line-height: 1.5;
}
.error-banner p + p { margin-top: 6px; }

/* Field groups */
.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.field {
  margin-bottom: 16px;
}
.field label {
  display: block;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--ink);
  letter-spacing: 0.04em;
  text-transform: uppercase;
  margin-bottom: 7px;
}
.field label span.opt {
  font-weight: 400;
  color: var(--muted);
  text-transform: none;
  letter-spacing: 0;
}

.input-wrap {
  position: relative;
}
.input-wrap input,
.input-wrap textarea {
  width: 100%;
  padding: 11px 14px;
  background: #fff;
  border: 1.5px solid var(--border);
  border-radius: 8px;
  font: 14px/1.5 'DM Sans', sans-serif;
  color: var(--navy);
  transition: border-color var(--trans), box-shadow var(--trans);
  outline: none;
  -webkit-appearance: none;
}
.input-wrap textarea {
  resize: vertical;
  min-height: 80px;
}
.input-wrap input:focus,
.input-wrap textarea:focus {
  border-color: var(--ink);
  box-shadow: 0 0 0 3px rgba(26,45,82,0.08);
}
.input-wrap input.valid   { border-color: #22c55e; }
.input-wrap input.invalid { border-color: var(--red); }

.input-wrap input.has-icon { padding-right: 42px; }

/* Toggle password eye */
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

/* Inline field hint */
.field-hint {
  font-size: 11.5px;
  color: var(--muted);
  margin-top: 5px;
  min-height: 16px;
  transition: color var(--trans);
}
.field-hint.ok  { color: var(--green); }
.field-hint.err { color: var(--red); }

/* Password strength */
.strength-track {
  height: 4px;
  background: var(--border);
  border-radius: 99px;
  margin-top: 8px;
  overflow: hidden;
}
.strength-fill {
  height: 100%;
  border-radius: 99px;
  width: 0;
  transition: width 0.3s ease, background 0.3s ease;
}

/* Divider */
.divider {
  height: 1px;
  background: var(--border);
  margin: 20px 0;
}

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
  transition: background var(--trans), transform 0.15s ease, box-shadow var(--trans);
  margin-top: 4px;
  position: relative;
  overflow: hidden;
}
.btn-submit::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(212,168,75,0.18), transparent);
  opacity: 0;
  transition: opacity var(--trans);
}
.btn-submit:hover { background: var(--ink); box-shadow: 0 6px 20px rgba(15,27,53,0.22); }
.btn-submit:hover::after { opacity: 1; }
.btn-submit:active { transform: scale(0.99); }

.terms {
  font-size: 12px;
  color: var(--muted);
  text-align: center;
  margin-top: 14px;
  line-height: 1.6;
}
.terms a { color: var(--ink); text-decoration: none; border-bottom: 1px solid var(--border); }

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

/* ── RESPONSIVE ── */
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

      <h1 class="panel-headline">Discover &amp;<br>Win <em>Unique</em><br>Auctions</h1>
      <p class="panel-sub">Join thousands of buyers and sellers on a platform built for trust, transparency, and great deals.</p>

      <ul class="perks">
        <li><span class="perk-dot">✓</span> Browse hundreds of live auctions</li>
        <li><span class="perk-dot">✓</span> Auto-bid so you never miss a win</li>
        <li><span class="perk-dot">✓</span> Verified sellers, secure platform</li>
        <li><span class="perk-dot">✓</span> Track bids &amp; spending in real-time</li>
      </ul>

    </div>
  </aside>

  <!-- ── RIGHT ── -->
  <main class="panel-right">
    <div class="form-box">

      <h2 class="form-title">Create Account</h2>
      <p class="form-sub">Already have one? <a href="<?= e(base_url('index.php?page=login')) ?>">Sign in</a></p>

      <?php if (!empty($errors)): ?>
      <div class="error-banner" role="alert">
        <?php foreach ($errors as $err): ?>
          <p>
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="6.5" stroke="#c0392b"/><path d="M7 4v3.5M7 10h.01" stroke="#c0392b" stroke-width="1.4" stroke-linecap="round"/></svg>
            <?= e($err) ?>
          </p>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="<?= e(base_url('index.php?page=register')) ?>" novalidate id="regForm">

        <div class="field-row">
          <div class="field">
            <label for="name">Full Name</label>
            <div class="input-wrap">
              <input type="text" id="name" name="name" placeholder="Jane Doe"
                     value="<?= e($old['name'] ?? '') ?>" autocomplete="name" required>
            </div>
            <div class="field-hint" id="name-hint"></div>
          </div>
          <div class="field">
            <label for="phone">Phone </span></label>
            <div class="input-wrap">
              <input type="tel" id="phone" name="phone" placeholder="+880 1700 000000"
                     value="<?= e($old['phone'] ?? '') ?>" autocomplete="tel">
            </div>
          </div>
        </div>

        <div class="field">
          <label for="email">Email Address</label>
          <div class="input-wrap">
            <input type="email" id="email" name="email" placeholder="jane@example.com"
                   value="<?= e($old['email'] ?? '') ?>" autocomplete="email" required>
          </div>
          <div class="field-hint" id="email-hint"></div>
        </div>

        <div class="field">
          <label for="bio">Bio </span></label>
          <div class="input-wrap">
            <textarea id="bio" name="bio" placeholder="Tell sellers a bit about yourself…"><?= e($old['bio'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="divider"></div>

        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <input type="password" id="password" name="password" placeholder="Min. 8 characters"
                   autocomplete="new-password" class="has-icon" required>
            <button type="button" class="eye-btn" data-target="password" aria-label="Toggle password">
              <?= eyeSVG() ?>
            </button>
          </div>
          <div class="strength-track"><div class="strength-fill" id="strength-fill"></div></div>
          <div class="field-hint" id="pw-hint"></div>
        </div>

        <div class="field">
          <label for="confirm_password">Confirm Password</label>
          <div class="input-wrap">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password"
                   autocomplete="new-password" class="has-icon" required>
            <button type="button" class="eye-btn" data-target="confirm_password" aria-label="Toggle confirm password">
              <?= eyeSVG() ?>
            </button>
          </div>
          <div class="field-hint" id="confirm-hint"></div>
        </div>

        <button type="submit" class="btn-submit">Create My Account →</button>
        <p class="terms">By registering you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</p>

      </form>
    </div>
  </main>

</div>

<script>
// ── Eye toggle ──
document.querySelectorAll('.eye-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const inp = document.getElementById(btn.dataset.target);
    inp.type = inp.type === 'password' ? 'text' : 'password';
  });
});

// ── Name validation ──
const nameInp = document.getElementById('name');
const nameHint = document.getElementById('name-hint');
nameInp.addEventListener('blur', () => {
  if (nameInp.value.trim().length < 2) {
    nameInp.classList.add('invalid'); nameInp.classList.remove('valid');
    nameHint.textContent = 'Please enter your full name.'; nameHint.className = 'field-hint err';
  } else {
    nameInp.classList.add('valid'); nameInp.classList.remove('invalid');
    nameHint.textContent = ''; nameHint.className = 'field-hint';
  }
});

// ── Email validation ──
const emailInp = document.getElementById('email');
const emailHint = document.getElementById('email-hint');
emailInp.addEventListener('blur', () => {
  const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInp.value.trim());
  emailInp.classList.toggle('valid', ok); emailInp.classList.toggle('invalid', !ok);
  emailHint.textContent = ok ? '' : 'Enter a valid email address.';
  emailHint.className = 'field-hint' + (ok ? '' : ' err');
});

// ── Password strength ──
const pwInp = document.getElementById('password');
const fill  = document.getElementById('strength-fill');
const pwHint = document.getElementById('pw-hint');
const levels = [
  { min: 0,  label: '',             color: 'transparent', w: '0%'  },
  { min: 1,  label: 'Too short',    color: '#ef4444',     w: '20%' },
  { min: 6,  label: 'Weak',         color: '#f97316',     w: '40%' },
  { min: 8,  label: 'Fair',         color: '#eab308',     w: '60%' },
  { min: 10, label: 'Good',         color: '#22c55e',     w: '80%' },
  { min: 14, label: 'Strong 💪',    color: '#16a34a',     w: '100%'},
];

pwInp.addEventListener('input', () => {
  const v = pwInp.value;
  let score = 0;
  if (v.length >= 8)  score++;
  if (v.length >= 10) score++;
  if (v.length >= 14) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;

  let lvl;
  if (v.length === 0)      lvl = levels[0];
  else if (v.length < 6)   lvl = levels[1];
  else if (v.length < 8)   lvl = levels[2];
  else if (score < 3)      lvl = levels[3];
  else if (score < 5)      lvl = levels[4];
  else                     lvl = levels[5];

  fill.style.width      = lvl.w;
  fill.style.background = lvl.color;
  pwHint.textContent    = lvl.label;
  pwHint.className      = 'field-hint' + (score >= 3 ? ' ok' : (v.length > 0 ? ' err' : ''));

  if (confirmInp.value) checkConfirm();
});

// ── Confirm match ──
const confirmInp = document.getElementById('confirm_password');
const confirmHint = document.getElementById('confirm-hint');

function checkConfirm() {
  const match = pwInp.value === confirmInp.value && confirmInp.value.length > 0;
  const empty = confirmInp.value.length === 0;
  confirmInp.classList.toggle('valid',   match);
  confirmInp.classList.toggle('invalid', !match && !empty);
  confirmHint.textContent = empty ? '' : (match ? '✓ Passwords match' : 'Passwords do not match');
  confirmHint.className   = 'field-hint' + (empty ? '' : (match ? ' ok' : ' err'));
}
confirmInp.addEventListener('input', checkConfirm);

// ── Client-side gate (server still validates) ──
document.getElementById('regForm').addEventListener('submit', e => {
  let ok = true;
  if (nameInp.value.trim().length < 2) { nameInp.focus(); ok = false; }
  if (pwInp.value.length < 8) { if (ok) pwInp.focus(); ok = false; }
  if (pwInp.value !== confirmInp.value) { if (ok) confirmInp.focus(); ok = false; }
  if (!ok) e.preventDefault();
});
</script>
</body>
</html>

<?php
function eyeSVG() {
    return '<svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M1 10s3.5-7 9-7 9 7 9 7-3.5 7-9 7-9-7-9-7Z" stroke="currentColor" stroke-width="1.5"/>
      <circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5"/>
    </svg>';
}
?>