<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function base_url($path = '') {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    if ($base === '/' || $base === '\\') { $base = ''; }
    return $base . '/' . ltrim($path, '/');
}

function redirect_to($path) {
    header('Location: ' . base_url($path));
    exit;
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_role() {
    return $_SESSION['role'] ?? 'guest';
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect_to('index.php?page=login');
    }
}

function require_role($roles) {
    require_login();
    if (!is_array($roles)) { $roles = [$roles]; }
    if (!in_array(current_role(), $roles, true)) {
        http_response_code(403);
        echo 'Access denied';
        exit;
    }
}

function require_seller_verified() {
    require_role('seller');
    if (empty($_SESSION['seller_verified'])) {
        redirect_to('index.php?page=seller_verification');
    }
}

function json_response($data) {
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function upload_file($field, $folder, $allowed = ['jpg','jpeg','png','webp','pdf']) {
    if (empty($_FILES[$field]['name'])) { return null; }
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) { return null; }
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) { return null; }
    if ($_FILES[$field]['size'] > 40 * 1024 * 1024) { return null; }
    $name = uniqid('upload_', true) . '.' . $ext;
    $relative = 'uploads/' . trim($folder, '/') . '/' . $name;
    $target = __DIR__ . '/../' . $relative;
    if (!is_dir(dirname($target))) { mkdir(dirname($target), 0777, true); }
    if (move_uploaded_file($_FILES[$field]['tmp_name'], $target)) { return $relative; }
    return null;
}
?>
