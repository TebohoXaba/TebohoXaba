<?php
include "header.php";
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // PHPMailer autoload

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $username = filter_var(trim($_POST['username']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $phone_number = filter_var(trim($_POST['phone_number']), FILTER_SANITIZE_STRING);
    $address = filter_var(trim($_POST['address']), FILTER_SANITIZE_STRING);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header('Location: register.php');
        exit;
    }

    // Validate password strength (at least 8 characters, one uppercase, one number, one special character)
    if (!preg_match("/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        $_SESSION['error'] = "Password must be at least 8 characters long, with one uppercase letter, one number, and one special character.";
        header('Location: register.php');
        exit;
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Generate a unique verification token
    $verification_token = bin2hex(random_bytes(16));

    try {
        // Generate token expiry timestamp (24 hours from now)
        $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $stmt = $pdo->prepare("
            INSERT INTO users (name, username, email, password, phone_number, address, role_id, is_verified, verification_token, token_expiry) 
            VALUES (:name, :username, :email, :password, :phone_number, :address, 1, 0, :verification_token, :token_expiry)
        ");
        
        $stmt->execute([
            ':name' => $name,
            ':username' => $username,
            ':email' => $email,
            ':password' => $password_hash,
            ':phone_number' => $phone_number,
            ':address' => $address,
            ':verification_token' => $verification_token,
            ':token_expiry' => $token_expiry,
        ]);

        if ($stmt->rowCount() > 0) {
            // Send a verification email
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'da13.host-ww.net'; // Set to the recommended mail server
                $mail->SMTPAuth = true;
                $mail->Username = 'no-reply@zxfleet.co.za';
                $mail->Password = '@3108BTx'; // Use the correct email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SSL for secure connection
                $mail->Port = 465; // Set the port to 465 for SSL

                $mail->setFrom('no-reply@zxfleet.co.za', 'ZX Fleet Partners');
                $mail->addAddress($email);

                // Verification link
                $verification_link = "https://zxfleet.co.za/verify.php?token=$verification_token";

                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Email Address';
                $mail->Body = "
                    <p>Hi $name,</p>
                    <p>Thank you for registering. Please click the link below to verify your email address:</p>
                    <p><a href='$verification_link'>$verification_link</a></p>
                    <p>If you did not sign up, please ignore this email.</p>
                ";

                $mail->send();
                $_SESSION['success'] = "Registration successful! A verification email has been sent to $email.";
                header('Location: login.php');
                exit;

            } catch (Exception $e) {
                $_SESSION['error'] = "Mailer Error: " . $mail->ErrorInfo;
            }
        } else {
            $_SESSION['error'] = "Error: Could not insert user into the database.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
    }
}
?>
<script>
    // Password visibility toggle
    function togglePasswordVisibility() {
        var passwordField = document.getElementById('password');
        var toggleBtn = document.getElementById('password-toggle');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleBtn.innerHTML = 'Hide';
        } else {
            passwordField.type = 'password';
            toggleBtn.innerHTML = 'Show';
        }
    }

    function validateForm(event) {
        const termsCheckbox = document.getElementById('terms');
        if (!termsCheckbox.checked) {
            event.preventDefault();
            alert('You must agree to the Terms and Conditions and Privacy Policy before registering.');
        }
    }
</script>
<style>
    body {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    }
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .card-header {
        border-radius: 15px 15px 0 0;
    }
    .btn-dark {
        background-color: darkslategrey;
        border: none;
    }
    .btn-dark:hover {
        background: #212529;
    }
    a {
        color: #0d6efd;
    }
    .form-check-input {
        background:rgb(118, 128, 138);
    }
</style>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-white text-center" style="background-color: darkslategrey;">
                    <h2>Register</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" onsubmit="validateForm(event)">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control" placeholder="Choose a unique username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Create a password" required>
                            <button type="button" id="password-toggle" onclick="togglePasswordVisibility()">Show</button>
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" id="phone_number" name="phone_number" class="form-control" placeholder="Enter your phone number" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea id="address" name="address" class="form-control" rows="3" placeholder="Enter your address" required></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>.
                            </label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark">Register</button>
             
