<?php
require 'db.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in and has a driver role
if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 3) {
    $_SESSION['error'] = "Unauthorized access.";
    header('Location: login.php');
    exit;
}

// Get the request data
$request_id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
$new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

if (!$request_id || !$new_status) {
    $_SESSION['error'] = "Invalid request or status.";
    header('Location: driver_dashboard.php');
    exit;
}

try {
    // Fetch the shipping request and associated user details
    $stmt = $pdo->prepare("
        SELECT sr.*, u.name AS user_name, u.email AS user_email 
        FROM shipping_requests sr
        JOIN users u ON sr.user_id = u.id
        WHERE sr.id = :request_id
    ");
    $stmt->execute([':request_id' => $request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        $_SESSION['error'] = "Shipping request not found.";
        header('Location: driver_dashboard.php');
        exit;
    }

    // Update the status
    $update_stmt = $pdo->prepare("UPDATE shipping_requests SET status = :status WHERE id = :request_id");
    $update_stmt->execute([
        ':status' => $new_status,
        ':request_id' => $request_id,
    ]);

    // Send an email notification to the user
    $to = $request['user_email'];
    $subject = "Shipping Request Status Update";
    $message = "
        <h3>Hello {$request['user_name']},</h3>
        <p>Your shipping request with Tracking ID: <strong>{$request['tracking_id']}</strong> has been updated to:</p>
        <p><strong>Status:</strong> {$new_status}</p>
        <p>Thank you for using our service!</p>
        <br>
        <p>Best Regards,</p>
        <p>Your Shipping Company</p>
    ";
    $headers = [
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8",
        "From: no-reply@shippingcompany.com",
    ];

    if (mail($to, $subject, $message, implode("\r\n", $headers))) {
        $_SESSION['success'] = "Status updated successfully, and the user has been notified.";
    } else {
        $_SESSION['error'] = "Status updated, but email notification failed.";
    }

    // Redirect back to the view page
    header("Location: view_request.php?request_id=$request_id");
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = "Error updating status: " . $e->getMessage();
    header('Location: driver_dashboard.php');
    exit;
}
?>
