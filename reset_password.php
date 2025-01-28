<?php
require 'db.php';
include "header.php";
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists and is valid
    $stmt = $pdo->prepare("SELECT * FROM users WHERE password_reset_token = :token AND token_expiry > NOW()");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Token is valid
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Update the password and clear the token
            $stmt = $pdo->prepare("UPDATE users SET password = :password, password_reset_token = NULL, token_expiry = NULL WHERE id = :id");
            $stmt->execute([':password' => $newPassword, ':id' => $user['id']]);

            echo "<div class='alert alert-success text-center'>Password reset successful. You can now <a href='login.php'>login</a>.</div>";
        } else {
            // Show the password reset form
            ?>
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white text-center">
                                <h2>Reset Password</h2>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">New Password</label>
                                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your new password" required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Reset Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo "<div class='alert alert-danger text-center'>Invalid or expired token.</div>";
    }
} else {
    echo "<div class='alert alert-danger text-center'>Invalid request.</div>";
}
include 'footer.php';
?>
