<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'auction_system';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>
