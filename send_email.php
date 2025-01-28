<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer library
require 'vendor/autoload.php';

// Error reporting for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input to prevent XSS attacks
    $clientEmail = filter_var($_POST['client_email'], FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    // Validate email address format
    if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP(); // Set mailer to use SMTP
            $mail->Host = 'da13.host-ww.net'; // SMTP server
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = 'no-reply@zxfleet.co.za'; // Admin email address
            $mail->Password = '@3108BTx'; // Admin email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SSL for secure connection
            $mail->Port = 465; // Set the port to 465 for SSL

            // Recipients
            $mail->setFrom('no-reply@zxfleet.co.za', 'Shipping Team'); // From email and name
            $mail->addAddress($clientEmail); // Add a recipient (client's email)

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = nl2br($message); // Convert newlines to <br> for HTML

            // Send the email
            $mail->send();
            $success = "Email has been sent successfully!";
        } catch (Exception $e) {
            // Improved error message with specific exception info
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Email</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4 text-center">Send Email to Client</h1>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="shadow p-4 rounded">
        <div class="mb-3">
            <label for="client_email" class="form-label">Client's Email Address</label>
            <input type="email" id="client_email" name="client_email" class="form-control" required placeholder="Enter client's email" value="<?= htmlspecialchars($clientEmail ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <input type="text" id="subject" name="subject" class="form-control" required placeholder="Enter email subject" value="<?= htmlspecialchars($subject ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea id="message" name="message" rows="6" class="form-control" required placeholder="Enter email message"><?= htmlspecialchars($message ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send Email</button>
    </form>
</div>
</body>
</html>
