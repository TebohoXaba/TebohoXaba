<?php
require 'db.php';

// Fetch and validate the data from PayFast's IPN notification
$pfData = file_get_contents('php://input'); // Raw POST data from PayFast
parse_str($pfData, $payfastData);

$merchant_id = "10033933"; // Your merchant ID
$merchant_key = "083tpc9opqqar"; // Your merchant key

// Verify payment data (ensure this is a valid request)
$signature = md5($pfData . $merchant_key);

if (isset($payfastData['signature']) && $payfastData['signature'] === $signature) {
    $payment_status = $payfastData['payment_status'] ?? '';
    $tracking_id = $payfastData['item_name'] ?? ''; // This should include the tracking ID from payment description

    if ($payment_status === 'COMPLETE') {
        try {
            // Update full_payment_status in the database
            $stmt = $pdo->prepare("UPDATE shipping_requests 
                                   SET full_payment_status = 'Paid' 
                                   WHERE tracking_id = :tracking_id");
            $stmt->execute([':tracking_id' => $tracking_id]);

            http_response_code(200); // Payment acknowledged
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            http_response_code(500); // Internal Server Error
        }
    } else {
        error_log("Payment not completed: " . $payfastData['payment_status']);
        http_response_code(400); // Bad Request
    }
} else {
    error_log("Invalid PayFast notification.");
    http_response_code(400); // Bad Request
}

exit;
?>
