<?php
include 'header.php';
require 'db.php';
require 'email_function.php'; // Ensure this is the correct path to your email function

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please log in to access the order details.";
    header('Location: login.php');
    exit;
}

// Ensure the user has a driver role
if ($_SESSION['user']['role_id'] != 3) {
    $_SESSION['error'] = "You do not have permission to access this page.";
    header('Location: index.php');
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
    $new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    if (!$request_id || !$new_status) {
        $_SESSION['error'] = "Invalid input.";
        header('Location: driver_dashboard.php');
        exit;
    }

    // Fetch the shipping request details
    $stmt = $pdo->prepare("SELECT * FROM shipping_requests WHERE id = :request_id");
    $stmt->execute([':request_id' => $request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        $_SESSION['error'] = "Shipping request not found.";
        header('Location: driver_dashboard.php');
        exit;
    }

    // Update the status in the database
    $update_stmt = $pdo->prepare("UPDATE shipping_requests SET status = :status WHERE id = :id");
    $update_stmt->execute([':status' => $new_status, ':id' => $request_id]);

    // Send status update email
    $contact_email = $request['contact_email'];
    $contact_name = $request['contact_name'];
    $tracking_id = $request['tracking_id'];
    $email_sent = false;

    if ($contact_email && $contact_name) {
        try {
            sendStatusUpdateEmail($contact_email, $contact_name, $new_status, $tracking_id);
            $email_sent = true;
        } catch (Exception $e) {
            error_log("Email Error: " . $e->getMessage());
        }
    }

    if ($email_sent) {
        $_SESSION['success'] = "Status updated successfully, and the email notification was sent.";
    } else {
        $_SESSION['warning'] = "Status updated successfully, but the email notification could not be sent.";
    }

    header('Location: driver_dashboard.php');
    exit;
}

// If not a POST request, redirect back
header('Location: driver_dashboard.php');
exit;
?>
