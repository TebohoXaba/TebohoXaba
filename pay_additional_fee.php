<?php
session_start();
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please log in to make a payment.";
    header('Location: login.php');
    exit;
}

// Validate appointment ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid appointment request.";
    header('Location: appointments.php');
    exit;
}

$appointment_id = $_GET['id'];

try {
    // Fetch the additional fee and appointment details
    $stmt = $pdo->prepare("SELECT additional_fee, tracking_id, payment_status FROM shipping_requests WHERE id = :id AND user_id = :user_id");
    $stmt->execute([':id' => $appointment_id, ':user_id' => $_SESSION['user']['id']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        $_SESSION['error'] = "Appointment not found.";
        header('Location: appointments.php');
        exit;
    }

    // Check if payment is already completed
    if ($appointment['payment_status'] === "Paid") {
        $_SESSION['error'] = "Payment has already been completed for this appointment.";
        header('Location: appointment_details.php?id=' . $appointment_id);
        exit;
    }

    // PayFast Payment Details
    $merchant_id = "10033933";
    $merchant_key = "083tpc9opqqar";
    $return_url = "https://zxfleet.co.za/payment_success.php?id=" . $appointment_id;
    $cancel_url = "https://zxfleet.co.za/payment_failed.php";
    $notify_url = "https://zxfleet.co.za/payfast_notify.php";
    $amount = number_format($appointment['additional_fee'], 2, '.', '');
    $description = "Additional Fee Payment for Appointment " . $appointment['tracking_id'];

    // Update the payment_status to 'Paid' after payment is successful
    if (isset($_GET['payment_success']) && $_GET['payment_success'] === 'true') {
        $updateStmt = $pdo->prepare("UPDATE shipping_requests SET payment_status = 'Paid' WHERE id = :id AND user_id = :user_id");
        $updateStmt->execute([':id' => $appointment_id, ':user_id' => $_SESSION['user']['id']]);

        // Confirm the update
        if ($updateStmt->rowCount() > 0) {
            $_SESSION['success'] = "Payment successful! Appointment updated.";
            header("Location: appointment_details.php?id=$appointment_id");
            exit;
        } else {
            $_SESSION['error'] = "Payment was successful, but the appointment could not be updated.";
            header("Location: appointment_details.php?id=$appointment_id");
            exit;
        }
    }

    // Redirect to PayFast
    header("Location: https://sandbox.payfast.co.za/eng/process?merchant_id=$merchant_id&merchant_key=$merchant_key&return_url=$return_url&cancel_url=$cancel_url&notify_url=$notify_url&amount=$amount&item_name=$description");
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = "Error processing payment: " . $e->getMessage();
    header('Location: appointments.php');
    exit;
}
?>
