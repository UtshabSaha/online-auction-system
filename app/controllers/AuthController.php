<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
class AuthController extends BaseController {
    public function login() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $userModel = new User($this->conn);
            $user = $userModel->findByEmail($email);
            if (!$user || !$user['is_active'] || !password_verify($password, $user['password_hash'])) {
                $error = 'Invalid login or inactive account.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['seller_verified'] = $user['seller_verified'];
                $dash = $user['role'] . '_dashboard';
                redirect_to('index.php?page=' . $dash);
            }
        }
        $this->view('auth/login', compact('error'));
    }
    public function register() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $phone = trim($_POST['phone'] ?? ''); $bio = trim($_POST['bio'] ?? ''); $password = $_POST['password'] ?? '';
            $errors = [];
            if ($name === '') $errors[] = 'Name is required';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
            if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
            if ($phone === '') $errors[] = 'Phone is required';
            $userModel = new User($this->conn);
            if ($userModel->findByEmail($email)) $errors[] = 'Email already exists';
            if (!$errors) {
                $userModel->create($name,$email,password_hash($password,PASSWORD_DEFAULT),$phone,$bio);
                redirect_to('index.php?page=login');
            }
            $error = $this->errors($errors);
        }
        $this->view('auth/register', compact('error'));
    }
    public function logout() { session_unset(); session_destroy(); redirect_to('index.php?page=login'); }
    public function profile() {
        require_login();
        $userModel = new User($this->conn); $user = $userModel->find(current_user_id()); $message='';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['change_password'])) {
                if (strlen($_POST['new_password'] ?? '') >= 6) { $userModel->changePassword(current_user_id(), password_hash($_POST['new_password'], PASSWORD_DEFAULT)); $message='Password changed.'; }
                else $message='Password must be at least 6 characters.';
            } else {
                $pic = upload_file('profile_pic','profiles',['jpg','jpeg','png','gif']);
                $userModel->updateProfile(current_user_id(), trim($_POST['name']), trim($_POST['phone']), trim($_POST['bio']), $pic);
                $_SESSION['user_name'] = trim($_POST['name']);
                $message='Profile updated.'; $user = $userModel->find(current_user_id());
            }
        }
        $this->view('auth/profile', compact('user','message'));
    }
}
?>
