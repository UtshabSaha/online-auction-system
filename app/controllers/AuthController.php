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

    // PROFILE UPDATE

    if (!isset($_POST['change_password'])) {

        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $bio = trim($_POST['bio']);

        $profilePic = $user['profile_pic'];

        if (!empty($_FILES['profile_pic']['name'])) {

    $fileName = time() . '_' . basename($_FILES['profile_pic']['name']);

    $uploadPath = __DIR__ . '/../../uploads/' . $fileName;

    move_uploaded_file(
        $_FILES['profile_pic']['tmp_name'],
        $uploadPath
    );

    $profilePic = $fileName;
}

        $userModel->updateProfile(
            current_user_id(),
            $name,
            $phone,
            $bio,
            $profilePic
        );

        $message = 'Profile updated successfully.';

        $user = $userModel->find(current_user_id());
    }

    // PASSWORD CHANGE

    if (isset($_POST['change_password'])) {

        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($newPassword !== $confirmPassword) {

            $message = 'New passwords and confirm passwords must be same.';

        } elseif (strlen($newPassword) < 6) {

            $message = 'Password must be at least 6 characters.';

        } else {

            $userModel->changePassword(
                current_user_id(),
                password_hash($newPassword, PASSWORD_DEFAULT)
            );

            $message = 'Password changed successfully.';
        }
    }
}


        $this->view('auth/register', compact('error'));
    }
    public function logout() { session_unset(); session_destroy(); redirect_to('index.php?page=login'); }
    public function profile() {
        require_login();
        $userModel = new User($this->conn); $user = $userModel->find(current_user_id()); $message='';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['change_password'])) {

    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirmPassword) {

        $message = 'New passwords and confirm passwords must be same.';

    } elseif (strlen($newPassword) < 6) {

        $message = 'Password must be at least 6 characters.';

    } else {

        $userModel->changePassword(
            current_user_id(),
            password_hash($newPassword, PASSWORD_DEFAULT)
        );

        $message = 'Password changed.';
    }
}
        }
        $this->view('auth/profile', compact('user','message'));
    }
}
?>
