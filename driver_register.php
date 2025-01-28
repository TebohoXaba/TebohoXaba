<?php
include 'header.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $license_number = $_POST['license_number'];
    $vehicle_details = $_POST['vehicle_details'];
    $identity_number = $_POST['identity_number'];

    // Handle the uploaded image
    $target_dir = "uploads/drivers/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Create the directory if it doesn't exist
    }

    $target_file = $target_dir . basename($_FILES["driver_image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate the uploaded file
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        $_SESSION['error'] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        header('Location: driver_register.php');
        exit;
    }

    if ($_FILES["driver_image"]["size"] > 5000000) { // 5MB size limit
        $_SESSION['error'] = "File size exceeds the maximum allowed size (5MB).";
        header('Location: driver_register.php');
        exit;
    }

    if (!move_uploaded_file($_FILES["driver_image"]["tmp_name"], $target_file)) {
        $_SESSION['error'] = "Failed to upload the image.";
        header('Location: driver_register.php');
        exit;
    }

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Insert user data into `users` table
        $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, phone_number, address, role_id)
                               VALUES (:name, :username, :email, :password, :phone_number, :address, 3)");
        $stmt->execute([
            ':name' => $name,
            ':username' => $username,
            ':email' => $email,
            ':password' => $password,
            ':phone_number' => $phone_number,
            ':address' => $address,
        ]);

        $user_id = $pdo->lastInsertId(); // Get the user ID for the driver

        // Insert driver-specific data into `drivers` table
        $stmt = $pdo->prepare("INSERT INTO drivers (user_id, license_number, vehicle_details, identity_number, driver_image)
                               VALUES (:user_id, :license_number, :vehicle_details, :identity_number, :driver_image)");
        $stmt->execute([
            ':user_id' => $user_id,
            ':license_number' => $license_number,
            ':vehicle_details' => $vehicle_details,
            ':identity_number' => $identity_number,
            ':driver_image' => $target_file,
        ]);

        // Commit transaction
        $pdo->commit();

        $_SESSION['success'] = "Driver registered successfully!";
        header('Location: drivers_list.php');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
    }
}
?>

<div class="container py-5">
    <h2>Driver Registration</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="phone_number" class="form-label">Phone Number</label>
            <input type="text" id="phone_number" name="phone_number" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea id="address" name="address" class="form-control" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="license_number" class="form-label">License Number</label>
            <input type="text" id="license_number" name="license_number" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="vehicle_details" class="form-label">Vehicle Details</label>
            <input type="text" id="vehicle_details" name="vehicle_details" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="identity_number" class="form-label">Identity Number</label>
            <input type="text" id="identity_number" name="identity_number" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="driver_image" class="form-label">Driver Image</label>
            <input type="file" id="driver_image" name="driver_image" class="form-control" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>
<?php include 'footer.php'; ?>
