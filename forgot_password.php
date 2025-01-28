<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require 'db.php';

    $email = $_POST['email'];

    // Clean up expired tokens
    $stmt = $pdo->prepare("UPDATE users SET password_reset_token = NULL, token_expiry = NULL WHERE token_expiry < NOW()");
    $stmt->execute();

    // Check if email exists in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate reset token and expiry time
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+2 hour'));

        // Store the token in the database
        $stmt = $pdo->prepare("UPDATE users SET password_reset_token = :token, token_expiry = :expiry WHERE email = :email");
        $stmt->execute([':token' => $token, ':expiry' => $expiry, ':email' => $email]);

        // Generate the reset link
        $resetLink = "http://localhost/shipping_website/reset_password.php?token=$token";

        // Send the reset link using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'zxfleetpartners@gmail.com'; // Your email zxfleetpartners@gmail.com xbewlwuwsfcyciuh
            $mail->Password = 'xbewlwuwsfcyciuh'; // App password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('zxfleetpartners@gmail.com', 'ZX Fleet Partners');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Hello,<br><br>You requested a password reset. Click the link below to reset your password:<br><br><a href='$resetLink'>$resetLink</a><br><br>If you didn't request this, please ignore this email.";

            $mail->send();
            $_SESSION['success'] = "A reset link has been sent to your email.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to send email: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['error'] = "No account found with that email.";
    }
}
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h2>Forgot Password</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php elseif (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success" role="alert">
                            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Send Reset Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?> 
