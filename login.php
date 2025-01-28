<?php
include 'header.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['error'] = "Invalid username or password.";
    }
}
?>
<style>
<style>
/* General Styling */
body {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef); /* Smooth gradient background */
    font-family: 'Segoe UI', Tahoma, Geneva, sans-serif;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Card Styles */
.card {
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); /* Softer shadow for modern feel */
}

/* Card Header */
.card-header {
    
    border-radius: 12px 12px 0 0;
}

/* Form Inputs */
.form-control {
    border-radius: 8px;
    border: 1px solid #ced4da;
    padding: 10px;
    font-size: 16px;
    background-color: #f8f9fa;
}

.form-control:focus {
    border-color: #343a40;
    box-shadow: 0 0 5px rgba(52, 58, 64, 0.3); /* Focus glow */
}

/* Buttons */
.btn-dark {
    background-color: darkslategrey;
    border-radius: 8px;
    padding: 12px;
    font-size: 18px;
    transition: all 0.3s ease;
}

.btn-dark:hover {
    background-color: #212529;
    transform: scale(1.02); /* Slight hover zoom */
}

/* Alert Styling */
.alert {
    border-radius: 8px;
    font-size: 14px;
}

/* Links */
a {
    font-size: 14px;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

/* Responsive Layout Adjustments */
@media (min-width: 768px) {
    .card {
        max-width: 700px; /* Center the card on medium and larger screens */
    }
}

@media (max-width: 576px) {
    .card {
        margin: 20px; /* Prevent the card from touching screen edges on small devices */
    }

    .btn-dark {
        font-size: 16px; /* Slightly smaller button text for smaller screens */
    }

    .form-control {
        font-size: 14px;
        padding: 8px;
    }
}
</style>

<br>
<div class="container d-flex justify-content-center align-items-center mt-5">
    <div class="col-12 col-md-6 col-lg-5 mt-5">
        <div class="card shadow-sm border-0 rounded">
            <div class="card-header text-white text-center py-3"style="background-color: darkslategrey;">
                <h3 class="mb-0">Login</h3>
            </div>
            <div class="card-body p-4">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger text-center" role="alert">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                <form method="POST">
                    <!-- Username -->
                    <div class="form-group mb-4">
                        <label for="username" class="form-label">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-control" 
                            placeholder="Enter your username" 
                            required>
                    </div>
                    <!-- Password -->
                    <div class="form-group mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Enter your password" 
                            required>
                    </div>
                    <!-- Submit Button -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-dark btn-lg">Login</button>
                    </div>
                </form>
                <!-- Additional Links -->
                <div class="text-center mt-3">
                    <a href="forgot_password.php" class="text-muted">Forgot password?</a><br>
                    <a href="register.php" class="text-dark">Create an account</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
