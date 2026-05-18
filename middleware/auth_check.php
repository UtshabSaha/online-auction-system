<?php
require_once __DIR__ . '/../config/helpers.php';

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    redirect_to('index.php?page=login');
}

if (isset($allowed_roles)) {
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    if (!in_array($_SESSION['role'], $allowed_roles, true)) {
        http_response_code(403);
        echo 'Access denied';
        exit;
    }
}
?>
