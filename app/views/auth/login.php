<?php

if (is_logged_in()) {

    switch ($_SESSION['user_role']) {

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

$error = '';
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $old_email = $email;

    if ($email === '' || $password === '') {

        $error = 'Please enter email and password.';

    } else {

        $stmt = $conn->prepare("
            SELECT id, name, password_hash, role, seller_verified, is_active
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();

        if (!$user || !password_verify($password, $user['password_hash'])) {

            $error = 'Incorrect email or password.';

        } elseif (!$user['is_active']) {

            $error = 'Account has been deactivated.';

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

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Poppins',sans-serif;
    background:#f3f4f6;
}

.page{
    min-height:100vh;
    display:grid;
    grid-template-columns:1fr 1fr;
}

/* LEFT SIDE */

.left{
    background:#0f172a;
    color:white;

    display:flex;
    align-items:center;
    justify-content:center;

    padding:60px;
}

.left-content{
    max-width:420px;
}

.logo{
    font-size:38px;
    font-weight:700;
    color:#facc15;

    margin-bottom:20px;
}

.left h1{
    font-size:52px;
    line-height:1.2;

    margin-bottom:20px;
}

.left p{
    color:#cbd5e1;
    line-height:1.8;
    font-size:15px;
}

/* RIGHT SIDE */

.right{
    background:white;

    display:flex;
    align-items:center;
    justify-content:center;

    padding:40px;
}

.form-box{
    width:100%;
    max-width:420px;
}

.form-box h2{
    font-size:40px;
    color:#111827;

    margin-bottom:10px;
}

.form-box p{
    color:#666;
    margin-bottom:30px;
}

.form-box p a{
    color:#2563eb;
    text-decoration:none;
    font-weight:600;
}

/* ERROR */

.error{
    background:#fee2e2;
    color:#dc2626;

    padding:14px;
    border-radius:10px;

    margin-bottom:20px;

    font-size:14px;
}

/* FIELD */

.field{
    margin-bottom:22px;
}

.field label{
    display:block;

    margin-bottom:8px;

    font-size:14px;
    font-weight:600;

    color:#111827;
}

.input-wrap{
    position:relative;
}

.input-wrap input{
    width:100%;

    padding:15px 52px 15px 16px;

    border:1.5px solid #dcdcdc;
    border-radius:12px;

    font-size:16px;

    outline:none;

    transition:0.2s;
}

.input-wrap input:focus{
    border-color:#2563eb;
}

/* EYE BUTTON */

.eye-btn{
    position:absolute;

    right:16px;
    top:50%;

    transform:translateY(-50%);

    width:22px;
    height:22px;

    display:flex;
    align-items:center;
    justify-content:center;

    background:none;
    border:none;

    cursor:pointer;

    color:#666;

    padding:0;
}

.eye-btn svg{
    width:20px;
    height:20px;
    display:block;
}

/* BUTTON */

.btn{
    width:100%;

    padding:15px;

    border:none;
    border-radius:12px;

    background:#2563eb;
    color:white;

    font-size:16px;
    font-weight:600;

    cursor:pointer;

    transition:0.2s;
}

.btn:hover{
    background:#1d4ed8;
}

/* MOBILE */

@media(max-width:900px){

    .page{
        grid-template-columns:1fr;
    }

    .left{
        display:none;
    }

}

</style>

</head>

<body>

<div class="page">

    <!-- LEFT -->

    <div class="left">

        <div class="left-content">

            <div class="logo">
                AuctionHub
            </div>

            <h1>
                Online<br>
                Auction<br>
                Platform
            </h1>

            <p>
                Buy and sell products through live bidding.
                Secure and modern auction experience for buyers and sellers.
            </p>

        </div>

    </div>

    <!-- RIGHT -->

    <div class="right">

        <div class="form-box">

            <h2>Sign In</h2>

            <p>
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

            <form method="POST">

                <!-- EMAIL -->

                <div class="field">

                    <label>Email</label>

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

                <!-- PASSWORD -->

                <div class="field">

                    <label>Password</label>

                    <div class="input-wrap">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >

                        <button
                            type="button"
                            class="eye-btn"
                            id="eyeBtn"
                        >

                            <svg viewBox="0 0 20 20" fill="none">

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

                <button type="submit" class="btn">
                    Login
                </button>

            </form>

        </div>

    </div>

</div>

<script>

const pwInp = document.getElementById('password');
const eyeBtn = document.getElementById('eyeBtn');

eyeBtn.addEventListener('click', () => {

    if(pwInp.type === 'password'){

        pwInp.type = 'text';

    }else{

        pwInp.type = 'password';

    }

});

</script>

</body>
</html>