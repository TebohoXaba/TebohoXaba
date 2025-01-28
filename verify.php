<?php
session_start(); 
require 'db.php';

if (isset($_GET['token'])) {
    $token = filter_var($_GET['token'], FILTER_SANITIZE_STRING);

    try {
        // Check if the token exists, is not verified, and is not expired
        $stmt = $pdo->prepare("
            SELECT id, token_expiry 
            FROM users 
            WHERE verification_token = :token AND is_verified = 0
        ");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();

        if ($user) {
            $current_time = new DateTime();
            $expiry_time = new DateTime($user['token_expiry']);

            if ($expiry_time >= $current_time) {
                // Update user to verified
                $update = $pdo->prepare("
                    UPDATE users 
                    SET is_verified = 1, verification_token = NULL, token_expiry = NULL 
                    WHERE id = :id
                ");
                $update->execute([':id' => $user['id']]);

                $_SESSION['success'] = "Your email has been verified! You can now log in.";
                header('Location: login.php');
                exit;
            } else {
                $_SESSION['error'] = "This verification token has expired.";
            }
        } else {
            $_SESSION['error'] = "Invalid or already used token.";
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = "An unexpected error occurred. Please try again.";
    }
} else {
    $_SESSION['error'] = "No token provided.";
}

header('Location: login.php');
exit;

?>
