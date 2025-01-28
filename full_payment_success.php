<?php
include "header.php";
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please log in to continue.";
    header('Location: login.php');
    exit;
}

// Validate appointment ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid appointment request.";
    header('Location: appointments.php');
    exit;
}

$appointment_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$appointment_id) {
    $_SESSION['error'] = "Invalid appointment ID.";
    header('Location: appointments.php');
    exit;
}

try {
    // Debugging: Check if user and appointment exist before update
    $checkStmt = $pdo->prepare("
        SELECT id, full_payment_status 
        FROM shipping_requests 
        WHERE id = :id AND user_id = :user_id
    ");
    $checkStmt->execute([
        ':id' => $appointment_id,
        ':user_id' => $_SESSION['user']['id']
    ]);

    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $_SESSION['error'] = "No matching appointment found or unauthorized access.";
        header('Location: appointments.php');
        exit;
    }

    // Debugging: Check if status is already 'Paid'
    if ($result['full_payment_status'] === 'Paid') {
        $_SESSION['error'] = "Payment is already marked as 'Paid'.";
        header("Location: appointment_details.php?id=$appointment_id");
        exit;
    }

    // Update the payment status
    $stmt = $pdo->prepare("
        UPDATE shipping_requests 
        SET full_payment_status = 'Paid' 
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute([
        ':id' => $appointment_id,
        ':user_id' => $_SESSION['user']['id']
    ]);

    // Verify the update
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Payment status could not be updated.";
        header('Location: appointments.php');
        exit;
    }

    // Set success message
    $_SESSION['success'] = "Payment was successful. Appointment updated.";
    header("Location: appointment_details.php?id=$appointment_id");
    exit;

} catch (PDOException $e) {
    // Log the error for debugging
    error_log("Database Error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while updating your payment status. Please contact support.";
    header('Location: appointments.php');
    exit;
}
?>
<?php include "footer.php"; ?>
