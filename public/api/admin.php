<?php
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/functions.php';

require_role('admin');
$action = $_GET['action'] ?? '';

if ($action === 'search_users') {
    $rows = user_all($conn, $_GET['q'] ?? '');
    $html = '';
    foreach ($rows as $r) {
        $html .= '<tr><td>' . e($r['name']) . '<br>' . e($r['email']) . '</td><td>' . e($r['role']) . '</td><td>' . e($r['is_active']) . '</td><td><form method="post"><input type="hidden" name="action" value="active"><input type="hidden" name="id" value="' . $r['id'] . '"><input type="hidden" name="is_active" value="' . ($r['is_active'] ? 0 : 1) . '"><button>' . ($r['is_active'] ? 'Deactivate' : 'Reactivate') . '</button></form></td></tr>';
    }
    json_response(['success' => true, 'html' => $html]);
}

json_response(['success' => false, 'message' => 'Unknown action']);
?>
