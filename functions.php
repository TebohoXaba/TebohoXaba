<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendConfirmationEmail($user_email, $tracking_id, $origin, $destination, $distance_km, $cost) {
    // Create an instance of PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'zxfleetpartners@gmail.com'; // Replace with your Gmail address 
        $mail->Password = 'xbewlwuwsfcyciuh'; // Replace with your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('zxfleetpartners@gmail.com', 'ZX Fleet Partners'); // Replace with your email
        $mail->addAddress($user_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Shipping Appointment Confirmation';
        $mail->Body = "
            <p>Dear user,</p>
            <p>Your shipping appointment has been successfully booked.</p>
            <p><strong>Tracking ID:</strong> $tracking_id</p>
            <p><strong>Origin:</strong> $origin</p>
            <p><strong>Destination:</strong> $destination</p>
            <p><strong>Distance:</strong> $distance_km km</p>
            <p><strong>Cost:</strong> $" . number_format($cost, 2) . "</p>
            <p>Thank you for using our service.</p>
            <p>Best regards,<br>Shipping Team</p>
        ";

        // Send the email
        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        // Log the error and return false
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
