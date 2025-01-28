<?php
session_start();
require 'db.php'; // Ensure this file initializes the $pdo connection correctly
require 'vendor/autoload.php'; // Include PHPMailer autoload if installed via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please log in to access this page.";
    header('Location: login.php');
    exit;
}

// Only handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Validate and sanitize input parameters
    $origin = filter_input(INPUT_GET, 'origin', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $destination = filter_input(INPUT_GET, 'destination', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $distance_km = filter_input(INPUT_GET, 'distance_km', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $cost = filter_input(INPUT_GET, 'cost', FILTER_VALIDATE_FLOAT);
    $additional_fee = filter_input(INPUT_GET, 'additional_fee', FILTER_VALIDATE_FLOAT);

    // Ensure all required parameters are present and valid
    if (!$origin || !$destination || $distance_km === false || $cost === false || $additional_fee === false) {
        $_SESSION['error'] = "Invalid or missing shipping details. Please calculate the cost again.";
        header('Location: calculate_shipping.php');
        exit;
    }

    try {
        // Generate a unique tracking ID
        $tracking_id = 'TRK-' . strtoupper(bin2hex(random_bytes(2))) . '-' . $_SESSION['user']['id'];

        // Fetch the user's details
        $stmt = $pdo->prepare("SELECT name, email, phone_number FROM users WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user']['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "Could not retrieve your details. Please try again.";
            header('Location: calculate_shipping.php');
            exit;
        }

        // Insert shipping request into the database
        $stmt = $pdo->prepare(" 
            INSERT INTO shipping_requests (user_id, origin, destination, distance_km, cost, additional_fee, tracking_id, contact_name, contact_email, contact_phone_number) 
            VALUES (:user_id, :origin, :destination, :distance_km, :cost, :additional_fee, :tracking_id, :contact_name, :contact_email, :contact_phone_number)
        ");
        $stmt->execute([
            ':user_id' => $_SESSION['user']['id'],
            ':origin' => $origin,
            ':destination' => $destination,
            ':distance_km' => $distance_km,
            ':cost' => $cost,
            ':additional_fee' => $additional_fee,
            ':tracking_id' => $tracking_id,
            ':contact_name' => $user['name'],
            ':contact_email' => $user['email'],
            ':contact_phone_number' => $user['phone_number']
        ]);

        // Get the ID of the newly inserted shipping request
        $appointment_id = $pdo->lastInsertId();

        // Send confirmation email using PHPMailer
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
            $mail->setFrom('no-reply@zxfleet.co.za', 'ZX Fleet Update'); // From email and name
            $mail->addAddress($user['email']); // Add a recipient (client's email)

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Shipping Appointment Confirmation';
            $mail->Body = "
<div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;'>
    <div style='text-align: center; margin-bottom: 20px;'>
        <h2 style='color: darkslategrey;'>ZX Fleet Partners</h2>
        <p style='color: #555; font-size: 16px;'>Fast, Reliable, and Affordable Shipping Solutions</p>
    </div>
    <hr style='border: none; border-top: 2px solid darkslategrey;'>
    
    <p style='font-size: 18px; font-weight: bold;'>Dear {$user['name']},</p>
    <p>Your shipping appointment has been successfully created. Please find the details below:</p>
    
    <div style='background-color: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;'>
        <table style='width: 100%; border-collapse: collapse;'>
            <tr>
                <td style='padding: 10px; font-weight: bold; border-bottom: 1px solid #ddd;'>Tracking ID:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$tracking_id</td>
            </tr>
            <tr>
                <td style='padding: 10px; font-weight: bold; border-bottom: 1px solid #ddd;'>Origin:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$origin</td>
            </tr>
            <tr>
                <td style='padding: 10px; font-weight: bold; border-bottom: 1px solid #ddd;'>Destination:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$destination</td>
            </tr>
            <tr>
                <td style='padding: 10px; font-weight: bold; border-bottom: 1px solid #ddd;'>Distance:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$distance_km km</td>
            </tr>
            <tr>
                <td style='padding: 10px; font-weight: bold; border-bottom: 1px solid #ddd;'>Call-out Fee:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>R " . number_format($additional_fee, 2) . "</td>
            </tr>
            <tr>
                <td style='padding: 10px; font-weight: bold;'>Total Cost:</td>
                <td style='padding: 10px;'>R " . number_format($cost, 2) . "</td>
            </tr>
        </table>
    </div>
    
    <p style='margin-top: 20px;'>Please note: The call-out fee ensures that your shipment is delivered safely and on time by covering costs such as tolls, fuel adjustments, or specific handling requirements.</p>
    
    <p>Thank you for choosing ZX Fleet Partners. If you have any questions, feel free to contact us.</p>
    
    <div style='text-align: center; margin-top: 30px;'>
        <p style='color: darkslategrey; font-size: 14px;'>Best regards,</p>
        <p style='color: #333; font-size: 16px; font-weight: bold;'>ZX Fleet Partners Team</p>
    </div>
    
    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
    
    <footer style='text-align: center; color: #777; font-size: 14px;'>
        <p>© 2025 ZX Fleet Partners. All rights reserved.</p>
        <p>Email: customer.service@zxfleet.co.za | Phone: +2764 008 5277</p>
    </footer>
</div>
";

            // Send the email
            $mail->send();
            $_SESSION['success'] = "Your shipping appointment has been booked successfully! A confirmation email has been sent to <strong>{$user['email']}</strong>.";
        } catch (Exception $e) {
            error_log("Email Error: " . $mail->ErrorInfo);
            $_SESSION['error'] = "Appointment created, but we could not send the confirmation email.";
        }

        // Redirect to the appointment details page
        header("Location: appointment_details.php?id=$appointment_id");
        exit;
    } catch (PDOException $e) {
        // Log the error and provide a user-friendly message
        error_log("Database Error: " . $e->getMessage()); // Log error details
        $_SESSION['error'] = "There was an issue booking your appointment. Please try again.";
        header('Location: calculate_shipping.php');
        exit;
    }
}
?>
