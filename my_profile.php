<?php
include 'header.php';
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "You must log in to access your profile.";
    header('Location: login.php');
    exit;
}

// Fetch user details
$user_id = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header('Location: login.php');
    exit;
}

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

    if (!$name || !$email || !$phone_number || !$address) {
        $_SESSION['error'] = "Invalid input. Please check your details.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, phone_number = :phone_number, address = :address WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone_number' => $phone_number,
                ':address' => $address,
                ':id' => $user_id,
            ]);

            $_SESSION['success'] = "Profile updated successfully.";
            header('Location: my_profile.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating profile. Please try again.";
        }
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 8) {
                $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $stmt->execute([':password' => $new_password_hash, ':id' => $user_id]);

                $_SESSION['success'] = "Password changed successfully.";
                header('Location: my_profile.php');
                exit;
            } else {
                $_SESSION['error'] = "Password must be at least 8 characters.";
            }
        } else {
            $_SESSION['error'] = "New passwords do not match.";
        }
    } else {
        $_SESSION['error'] = "Current password is incorrect.";
    }
}

// Delete account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    try {
        $pdo->beginTransaction();

        // Delete associated data (example: shipping_requests)
        $pdo->prepare("DELETE FROM shipping_requests WHERE user_id = :id")->execute([':id' => $user_id]);

        // Delete user account
        $pdo->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $user_id]);

        $pdo->commit();

        session_destroy();
        $_SESSION['success'] = "Account deleted successfully.";
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error deleting account. Please try again.";
    }
}
?>

<div class="container py-5">
    <h2 class="mb-4">My Profile</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Profile Update Section -->
    <div class="card mb-4">
        <div class="card-header">Update Profile</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Username</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?= htmlspecialchars($user['phone_number']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($user['address']); ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>

    <!-- Password Change Section -->
    <div class="card mb-4">
        <div class="card-header">Change Password</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
            </form>
        </div>
    </div>

    <!-- Account Deletion Section -->
    <div class="card">
        <div class="card-header">Delete Account</div>
        <div class="card-body">
            <p class="text-danger">Warning: This action cannot be undone. All your data will be permanently deleted.</p>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action is irreversible.')">
                <button type="submit" name="delete_account" class="btn btn-danger">Delete Account</button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
