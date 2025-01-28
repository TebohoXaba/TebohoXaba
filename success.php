<?php
session_start();
require 'config.php'; // Include your constants and configurations
require 'db.php';     // Include the database connection

// Verify that the database connection is initialized
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Assume you're processing data sent back from PayFast
// Example: capturing a successful payment and saving details to the database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'] ?? null;
    $transactionId = $_POST['pf_payment_id'] ?? null; // Assuming PayFast sends this
    $itemName = $_POST['item_name'] ?? null;

    if ($amount && $transactionId && $itemName) {
        // Insert payment details into the database
        $stmt = $conn->prepare("INSERT INTO payments (amount, transaction_id, item_name, payment_date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("dss", $amount, $transactionId, $itemName);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Payment was successful! Thank you.";
        } else {
            $_SESSION['error'] = "Failed to save payment details. Please contact support.";
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Invalid payment details received.";
    }
}

// Redirect the user to a confirmation page
header('Location: confirmation.php');
exit;
