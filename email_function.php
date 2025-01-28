<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // PHPMailer autoload

function sendStatusUpdateEmail($customer_email, $customer_name, $new_status, $tracking_id) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'host'; // Set to the recommended mail server
        $mail->SMTPAuth = true;
        $mail->Username = 'no-reply@zxfleet.co.za';
        $mail->Password = 'password'; // Use the correct email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SSL for secure connection
        $mail->Port = 465; // Set the port to 465 for SSL
        $mail->isSMTP();

        // Recipients
        $mail->setFrom('no-reply@zxfleet.co.za', 'ZX Fleet Partners');
        $mail->addAddress($customer_email, $customer_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Appointment for Shipping Update';
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;'>
            <div style='background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                <h2 style='margin: 0; font-size: 24px;'>Shipping Request Status Update</h2>
            </div>
            
            <div style='padding: 20px;'>
                <p style='font-size: 18px; font-weight: bold;'>Dear $customer_name,</p>
                <p>We are writing to inform you that the status of your shipping request has been updated. Please find the updated details below:</p>
                
                <div style='margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9; font-weight: bold;'>Tracking ID:</td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>$tracking_id</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9; font-weight: bold;'>New Status:</td>
                            <td style='padding: 10px; border: 1px solid #ddd;'>$new_status</td>
                        </tr>
                    </table>
                </div>
                
                <p style='text-align: center; margin: 20px 0;'>
                    <a href='https://zxfleet.co.za/track?tracking_id=$tracking_id' 
                       style='text-decoration: none; background-color: #4CAF50; color: white; padding: 10px 20px; font-size: 16px; border-radius: 5px;'>
                       Track Your Order
                    </a>
                </p>
                
                <p>Thank you for choosing ZX Fleet Partners. If you have any questions or need further assistance, please don't hesitate to contact our support team.</p>
                
                <p style='margin-top: 20px;'>Best regards,<br><strong>ZX Fleet Partners Shipping Team</strong></p>
            </div>
            
            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
            
            <footer style='text-align: center; color: #777; font-size: 14px;'>
                <p style='margin: 0;'>© " . date('Y') . " ZX Fleet Partners. All rights reserved.</p>
                <p style='margin: 0;'>Visit our website at <a href='https://zxfleet.co.za' style='color: #4CAF50; text-decoration: none;'>zxfleet.co.za</a></p>
            </footer>
        </div>
        ";

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
    }
}
?>
