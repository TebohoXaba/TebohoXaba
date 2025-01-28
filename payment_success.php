<?php
session_start();
require 'db.php';
require 'vendor/autoload.php'; // Ensure PHPMailer is properly autoloaded

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please log in to view this page.";
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
    // Fetch the appointment details, including contact_email
    $stmt = $pdo->prepare("SELECT id, tracking_id, payment_status, additional_fee, cost, contact_email FROM shipping_requests WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        ':id' => $appointment_id,
        ':user_id' => $_SESSION['user']['id']
    ]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        $_SESSION['error'] = "Appointment not found.";
        header('Location: appointments.php');
        exit;
    }

    // Check if the payment is already marked as "Paid"
    if ($appointment['payment_status'] === "Paid") {
        $_SESSION['success'] = "Payment is already marked as completed.";
        header("Location: appointment_details.php?id=$appointment_id");
        exit;
    }

    // Start a transaction to ensure atomic updates
    $pdo->beginTransaction();

    // Update the payment status to "Paid"
    $updateStmt = $pdo->prepare("UPDATE shipping_requests SET payment_status = 'Paid', status = 'Approved' WHERE id = :id AND user_id = :user_id");
    $updateStmt->execute([
        ':id' => $appointment_id,
        ':user_id' => $_SESSION['user']['id']
    ]);

    // Confirm the update and deduct the additional_fee from cost
    if ($updateStmt->rowCount() > 0) {
        // Deduct additional_fee from cost
        $deductStmt = $pdo->prepare("UPDATE shipping_requests SET cost = cost - additional_fee WHERE id = :id AND user_id = :user_id");
        $deductStmt->execute([
            ':id' => $appointment_id,
            ':user_id' => $_SESSION['user']['id']
        ]);

        // Check if the cost was updated
        if ($deductStmt->rowCount() > 0) {
            $pdo->commit();
            $_SESSION['success'] = "Payment successful! The additional fee has been deducted from the total cost.";

            // Send the success email
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'host'; // Set to the recommended mail server
                $mail->SMTPAuth = true;
                $mail->Username = 'no-reply@zxfleet.co.za';
                $mail->Password = 'password'; // Use the correct email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SSL for secure connection
                $mail->Port = 465; // Set the port to 465 for SSL

                // Email content
                $mail->setFrom('no-reply@zxfleet.co.za', 'Shipping Team');
                $mail->addAddress($appointment['contact_email']); // Send email to contact_email
                $mail->Subject = 'Payment Successful for Shipping Appointment';

                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                        <p style='font-size: 18px; font-weight: bold;'>Dear Customer,</p>
                        <p>Your payment for the shipping appointment has been successfully processed.</p>
                        <table style='width: 100%; max-width: 600px; margin: 20px 0; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px; border: 1px solid #ddd;'><strong>Tracking ID:</strong></td>
                                <td style='padding: 8px; border: 1px solid #ddd;'>{$appointment['tracking_id']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px; border: 1px solid #ddd;'><strong>Total Cost:</strong></td>
                                <td style='padding: 8px; border: 1px solid #ddd;'>R " . number_format($appointment['cost'] - $appointment['additional_fee'], 2) . "</td>
                            </tr>
                        </table>
                        <p>Thank you for using our service. If you have any questions, feel free to contact us.</p>
                        <p style='margin-top: 20px;'>Best regards,<br><strong>Shipping Team</strong></p>
                    </div>
                ";
                $mail->isHTML(true);
                $mail->send();
            } catch (Exception $e) {
                error_log("Email Error: " . $mail->ErrorInfo);
                $_SESSION['error'] = "Payment was successful, but the email could not be sent. Please check your email settings.";
            }
        } else {
            $pdo->rollBack();
            $_SESSION['error'] = "Payment processed, but the cost could not be updated.";
        }
    } else {
        $pdo->rollBack();
        $_SESSION['error'] = "Payment was processed, but the appointment status could not be updated.";
    }

    // Redirect to the appointment details page
    header("Location: appointment_details.php?id=$appointment_id");
    exit;

} catch (PDOException $e) {
    // Roll back any changes if an error occurs
    $pdo->rollBack();
    error_log("Database Error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while updating the payment status. Please contact support.";
    header('Location: appointments.php');
    exit;
}
?>
