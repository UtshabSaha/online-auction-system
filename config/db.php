<?php
$host = getenv('AUCTION_DB_HOST') ?: ($_SERVER['AUCTION_DB_HOST'] ?? 'localhost');
$user = getenv('AUCTION_DB_USER') ?: ($_SERVER['AUCTION_DB_USER'] ?? 'root');
$password = getenv('AUCTION_DB_PASSWORD') ?: ($_SERVER['AUCTION_DB_PASSWORD'] ?? '');
$dbname = getenv('AUCTION_DB_NAME') ?: ($_SERVER['AUCTION_DB_NAME'] ?? 'auction_system');

$conn = mysqli_connect($host, $user, $password, $dbname);
if (!$conn) {
    http_response_code(500);
    die('Database connection failed. Check config/db.php settings.');
}
mysqli_set_charset($conn, 'utf8mb4');
?>
