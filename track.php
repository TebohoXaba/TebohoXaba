<?php
session_start();
require 'db.php'; // Database connection

if (!isset($_POST['tracking_id']) || empty($_POST['tracking_id'])) {
    header("Location: index.php");
    exit;
}

$tracking_id = $_POST['tracking_id'];

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "You must be logged in to track a shipment.";
    header("Location: login.php");
    exit;
}

// Check if the tracking number exists and is associated with the logged-in user
$stmt = $pdo->prepare("SELECT * FROM shipping_requests WHERE tracking_id = :tracking_id AND user_id = :user_id");
$stmt->execute([
    ':tracking_id' => $tracking_id,
    ':user_id' => $_SESSION['user']['id'],
]);

$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if ($appointment) {
    // Redirect to the details page if found
    header("Location: appointment_details.php?tracking_id=" . urlencode($tracking_id));
    exit;
} else {
    // Set an error message if not found
    $_SESSION['error'] = "Invalid tracking number or you do not have permission to access this shipment.";
    header("Location: index.php");
    exit;
}
