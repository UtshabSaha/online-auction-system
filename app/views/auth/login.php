<?php
if (is_logged_in()) {
    redirect_to('index.php?page=home');
}

$error = '';
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $old_email = $email;

    if ($email === '' || $password === '') {

        $error = 'Please enter your email and password.';

    } else {

        $stmt = $conn->prepare('
            SELECT id, name, password_hash, role, seller_verified, is_active
            FROM users
            WHERE email = ?
            LIMIT 1
        ');

        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();

        if (!$user || !password_verify($password, $user['password_hash'])) {

            $error = 'Incorrect email or password.';

        } elseif (!$user['is_active']) {

            $error = 'Your account has been deactivated.';

        } else {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['seller_verified'] = $user['seller_verified'];

            switch ($user['role']) {

                case 'admin':
                    redirect_to('index.php?page=admin_dashboard');
                    break;

                case 'moderator':
                    redirect_to('index.php?page=moderator_dashboard');
                    break;

                case 'seller':
                    redirect_to('index.php?page=seller_dashboard');
                    break;

                default:
                    redirect_to('index.php?page=buyer_dashboard');
                    break;
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

<title>Login - AuctionHub</title>

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Poppins',sans-serif;
    background:#f5f5f5;
}

.page{
    display:grid;
    grid-template-columns:1fr 1fr;
    min-height:100vh;
}

/* LEFT */

.panel-left{
    background:#0f1b35;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:40px;
}

.left-content{
    color:white;
    max-width:350px;
}

.brand{
    font-size:32px;
    font-weight:600;
    margin-bottom:20px;
    color:#d4a84b;
}

.left-content h1{
    font-size:42px;
    line-height:1.3;
    margin-bottom:20px;
}

.left-content p{
    color:rgba(255,255,255,0.7);
    line-height:1.7;
}

/* RIGHT */

.panel-right{
    background:white;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:40px;
}

.form-box{
    width:100%;
    max-width:380px;
}

.form-title{
    font-size:34px;
    color:#0f1b35;
    margin-bottom:10px;
}

.form-sub{
    color:#666;
    margin-bottom:30px;
}

.form-sub a{
    color:#0f1b35;
    text-decoration:none;
    font-weight:600;
}

/* ERROR */

.error{
    background:#ffe5e5;
    color:#c0392b;
    padding:12px;
    border-radius:8px;
    margin-bottom:20px;
    font-size:14px;
}

/* FIELD */

.field{
    margin-bottom:20px;
}

.field label{
    display:block;
    margin-bottom:8px;
    font-size:14px;
    font-weight:600;
    color:#0f1b35;
}

.input-wrap{
    position:relative;
}

.input-wrap input{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:8px;
    font-size:14px;
    outline:none;
}

.input-wrap input:focus{
    border-color:#0f1b35;
}

.has-icon{
    padding-right:45px !important;
}

/* EYE BUTTON */

.eye-btn{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    background:none;
    border:none;
    cursor:pointer;
    color:#666;
}

/* BUTTON */

.btn-submit{
    width:100%;
    padding:13px;
    border:none;
    border-radius:8px;
    background:#0f1b35;
    color:white;
    font-size:15px;
    font-weight:600;
    cursor:pointer;
    transition:0.2s;
}

.btn-submit:hover{
    background:#1a2d52;
}

/* MOBILE */

@media(max-width:800px){

    .page{
        grid-template-columns:1fr;
    }

    .panel-left{
        display:none;
    }
}

</style>

</head>

<body>

<div class="page">

    <!-- LEFT -->

    <div class="panel-left">

        <div class="left-content">

            <div class="brand">
                AuctionHub
            </div>

            <h1>
                Welcome Back
            </h1>

            <p>
                Sign in to continue bidding,
                selling and managing your auctions.
            </p>

        </div>

    </div>

    <!-- RIGHT -->

    <div class="panel-right">

        <div class="form-box">

            <h2 class="form-title">
                Sign In
            </h2>

            <p class="form-sub">
                Don't have an account?
                <a href="<?= e(base_url('index.php?page=register')) ?>">
                    Register
                </a>
            </p>

            <?php if($error): ?>

                <div class="error">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="<?= e(base_url('index.php?page=login')) ?>">

                <div class="field">

                    <label>Email Address</label>

                    <div class="input-wrap">

                        <input
                            type="email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?= e($old_email) ?>"
                            required
                        >

                    </div>

                </div>

                <div class="field">

                    <label>Password</label>

                    <div class="input-wrap">

                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Enter your password"
                            class="has-icon"
                            required
                        >

                        <button
                            type="button"
                            class="eye-btn"
                            onclick="togglePassword()"
                        >

                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">

                                <path
                                    d="M1 10s3.5-7 9-7 9 7 9 7-3.5 7-9 7-9-7-9-7Z"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />

                                <circle
                                    cx="10"
                                    cy="10"
                                    r="2.5"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />

                            </svg>

                        </button>

                    </div>

                </div>

                <button type="submit" class="btn-submit">
                    Sign In
                </button>

            </form>

        </div>

    </div>

</div>

<script>

function togglePassword(){

    let password = document.getElementById('password');

    if(password.type === 'password'){

        password.type = 'text';

    }else{

        password.type = 'password';
    }
}

</script>

</body>

</html>