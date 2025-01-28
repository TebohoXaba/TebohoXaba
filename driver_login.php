<?php
include 'header.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND role_id = 3");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Fetch driver-specific data
            $stmt = $pdo->prepare("SELECT * FROM drivers WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $user['id']]);
            $driver = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($driver) {
                $_SESSION['driver'] = $user;
                $_SESSION['driver_details'] = $driver;
                header('Location: driver_dashboard.php');
                exit;
            }
        }
        $_SESSION['error'] = "Invalid login credentials!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}
?>

<div class="container py-5">
    <h2>Driver Login</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
<?php include 'footer.php'; ?>
