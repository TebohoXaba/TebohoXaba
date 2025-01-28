<?php
session_start();
require 'db.php'; // Ensure this initializes $pdo correctly
require 'vendor/autoload.php'; // Ensure PHPMailer and mPDF are properly autoloaded

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please log in to continue.";
    header('Location: login.php');
    exit;
}

// Validate shipping request ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid request.";
    header('Location: appointments.php');
    exit;
}

$shipping_request_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$shipping_request_id) {
    $_SESSION['error'] = "Invalid shipping request ID.";
    header('Location: appointments.php');
    exit;
}

try {
    // Fetch the current record
    $stmt = $pdo->prepare("
        SELECT id, tracking_id, full_payment_status, contact_email, contact_name, cost
        FROM shipping_requests 
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute([
        ':id' => $shipping_request_id,
        ':user_id' => $_SESSION['user']['id']
    ]);
    $shippingRequest = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the record exists
    if (!$shippingRequest) {
        $_SESSION['error'] = "No matching shipping request found or unauthorized access.";
        header('Location: appointments.php');
        exit;
    }

    // Check if the payment status is already 'Paid'
    if ($shippingRequest['full_payment_status'] !== 'Paid') {
        // Update the full_payment_status
        $updateStmt = $pdo->prepare("
            UPDATE shipping_requests 
            SET full_payment_status = 'Paid' 
            WHERE id = :id AND user_id = :user_id
        ");
        $updateStmt->execute([
            ':id' => $shipping_request_id,
            ':user_id' => $_SESSION['user']['id']
        ]);

        // Check if the update was successful
        if ($updateStmt->rowCount() === 0) {
            $_SESSION['error'] = "Failed to update payment status.";
            header('Location: appointments.php');
            exit;
        }
    }

    // Generate Invoice PDF using mPDF
    $mpdf = new \Mpdf\Mpdf();
    $html = "
<style>
    body {
        font-family: 'Arial', sans-serif;
        color: #333;
        margin: 0;
        padding: 0;
    }
    .invoice-container {
        max-width: 800px;
        margin: 30px auto;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    .header {
        background-color: darkslategrey;
        color: white;
        padding: 20px;
        text-align: center;
    }
    .header h1 {
        margin: 0;
        font-size: 26px;
        font-weight: bold;
    }
    .header p {
        margin: 5px 0;
        font-size: 16px;
    }
    .branding {
        display: flex;
        justify-content: space-between;
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 2px solid darkslategrey;
    }
    .branding img {
        height: 60px;
    }
    .branding .company-info {
        text-align: right;
    }
    .branding .company-info h3 {
        margin: 0;
        font-size: 18px;
        font-weight: bold;
    }
    .branding .company-info p {
        margin: 3px 0;
        font-size: 14px;
        color: #555;
    }
    .invoice-details {
        padding: 20px;
    }
    .invoice-details h2 {
        font-size: 20px;
        margin-bottom: 10px;
        border-bottom: 2px solid darkslategrey;
        padding-bottom: 5px;
    }
    .invoice-details p {
        margin: 5px 0;
        font-size: 14px;
    }
    .details-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    .details-table th, .details-table td {
        padding: 12px;
        border: 1px solid #ddd;
        text-align: left;
    }
    .details-table th {
        background-color: darkslategrey;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }
    .details-table td {
        font-size: 14px;
    }
    .total {
        text-align: right;
        padding: 10px;
        margin: 20px 0;
        font-size: 18px;
        font-weight: bold;
    }
    .footer {
        background: #f8f9fa;
        padding: 20px;
        text-align: center;
        font-size: 12px;
        color: #555;
    }
    .footer a {
        color: #007BFF;
        text-decoration: none;
    }
</style>

<div class='invoice-container'>
    <div class='header'>
        <h1>Invoice</h1>
        <p>Payment Confirmation</p>
        <p>Date: " . date('Y-m-d') . "</p>
    </div>
    
    <div class='branding'>
        <img src='https://i.ibb.co/B4YGVpt/ZX-logo-black.png' style='height: 60px; width: 190px;' alt='Logo'>
        <div class='company-info'>
            <h3>ZX FLEET Partners</h3>
            <p>4604 Mhandzela street</p>
            <p>Soweto, South Africa</p>
            <p>Email: zxfleetpartners@gmail.com</p>
        </div>
    </div>
    
    <div class='invoice-details'>
        <h2>Customer Information</h2>
        <p><strong>Name:</strong> {$shippingRequest['contact_name']}</p>
        <p><strong>Email:</strong> {$shippingRequest['contact_email']}</p>
    </div>
    
    <table class='details-table'>
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount (R)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Shipping Fee</td>
                <td>" . number_format($shippingRequest['cost'], 2) . "</td>
            </tr>
        </tbody>
    </table>
    
    <div class='total'>
        Total Paid: R " . number_format($shippingRequest['cost'], 2) . "
    </div>
    
    <div class='footer'>
        <p>If you have any questions, please contact our support team at <a href='mailto:zxfleetpartners@gmail.com'>zxfleetpartners@gmail.com</a>.</p>
        <p>ZX Fleet Partners, © " . date('Y') . "</p>
    </div>
</div>
";

    $mpdf->WriteHTML($html);

    // Save the PDF to a temporary file
    $invoiceFile = tempnam(sys_get_temp_dir(), 'invoice') . '.pdf';
    $mpdf->Output($invoiceFile, \Mpdf\Output\Destination::FILE);

    // Send confirmation email using PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST'); // Use environment variables for SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USERNAME'); // Use environment variables for SMTP username
        $mail->Password = getenv('SMTP_PASSWORD'); // Use environment variables for SMTP password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Email content
        $mail->setFrom(getenv('SMTP_USERNAME'), 'ZX Fleet Partners');
        $mail->addAddress($shippingRequest['contact_email']); // Send email to the contact_email
        $mail->Subject = 'Payment Confirmation for Your Shipping Appointment';

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <p style='font-size: 18px; font-weight: bold;'>Dear {$shippingRequest['contact_name']},</p>
                <p>Thank you for completing your payment for the delivery appointment.</p>
                <p>Your invoice is attached to this email.</p>
                <p>If you have any questions, feel free to contact us.</p>
                <p style='margin-top: 20px;'>Best regards,<br><strong>Shipping Team</strong></p>
            </div>
        ";
        $mail->isHTML(true);

        // Attach the invoice PDF
        $mail->addAttachment($invoiceFile, 'Invoice.pdf');

        $mail->send();

        // Clean up the temporary file
        unlink($invoiceFile);

    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        $_SESSION['error'] = "Payment was successful, but the confirmation email could not be sent. Please contact support.";
        header("Location: appointment_details.php?id=$shipping_request_id");
        exit;
    }

    // Success message
    $_SESSION['success'] = "Thank you for completing your payment! A confirmation email with your invoice has been sent.";
    header("Location: appointment_details.php?id=$shipping_request_id");
    exit;

} catch (PDOException $e) {
    // Log error and notify user
    error_log("Database Error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again.";
    header('Location: appointments.php');
    exit;
}
?>
