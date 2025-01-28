<?php
$host = 'localhost';
$db = 'zxfleetc_shipping';
$user = 'zxfleetc_Thabo'; // Default0 MySQL username
$password = 'Pass1475**'; // Default MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
