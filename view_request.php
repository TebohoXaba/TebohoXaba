<?php
include 'header.php';
require 'db.php';
require 'email_function.php'; // Include the reusable email sending function

// Validate and fetch the request ID
$request_id = filter_input(INPUT_GET, 'request_id', FILTER_VALIDATE_INT);

if (!$request_id) {
    $_SESSION['error'] = "Invalid request ID.";
    header('Location: admin_dashboard.php');
    exit;
}

try {
    // Fetch shipping request details
    $stmt = $pdo->prepare("
        SELECT sr.*, u.name AS driver_name, u.email AS customer_email
        FROM shipping_requests sr
        LEFT JOIN drivers d ON sr.driver_id = d.driver_id
        LEFT JOIN users u ON d.user_id = u.id
        WHERE sr.id = :request_id
    ");
    $stmt->execute([':request_id' => $request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        $_SESSION['error'] = "Shipping request not found.";
        header('Location: admin_dashboard.php');
        exit;
    }

    // Fetch customer details
    $customer_name = $request['customer_name'] ?? null;
    $customer_email = $request['customer_email'] ?? null;
    $customer_phone_number = $request['customer_phone_number'] ?? null;

    if (empty($customer_name) || empty($customer_email) || empty($customer_phone_number)) {
        $stmt = $pdo->prepare("
            SELECT name, email, phone_number 
            FROM users 
            WHERE id = :user_id
        ");
        $stmt->execute([':user_id' => $request['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $customer_name = $customer_name ?: $user['name'];
            $customer_email = $customer_email ?: $user['email'];
            $customer_phone_number = $customer_phone_number ?: $user['phone_number'];
        }
    }

    // Fetch all available drivers
    $drivers_stmt = $pdo->query("
        SELECT d.driver_id, u.name 
        FROM drivers d
        JOIN users u ON d.user_id = u.id
    ");
    $drivers = $drivers_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle driver assignment and status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['driver_id'])) {
            $new_driver_id = filter_input(INPUT_POST, 'driver_id', FILTER_VALIDATE_INT);

            if ($new_driver_id) {
                $assign_stmt = $pdo->prepare("
                    UPDATE shipping_requests 
                    SET driver_id = :driver_id 
                    WHERE id = :request_id
                ");
                $assign_stmt->execute([
                    ':driver_id' => $new_driver_id,
                    ':request_id' => $request_id,
                ]);
                $_SESSION['success'] = "Driver assigned successfully.";
            } else {
                $_SESSION['error'] = "Invalid driver selection.";
            }
        }

        if (isset($_POST['status'])) {
            $new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($new_status) {
                $status_stmt = $pdo->prepare("
                    UPDATE shipping_requests 
                    SET status = :status 
                    WHERE id = :request_id
                ");
                $status_stmt->execute([
                    ':status' => $new_status,
                    ':request_id' => $request_id,
                ]);
                $_SESSION['success'] = "Status updated successfully.";

                // Send an email to the user about the status update
                sendStatusUpdateEmail($customer_email, $customer_name, $new_status, $request['tracking_id']);
            } else {
                $_SESSION['error'] = "Invalid status selection.";
            }
        }

        header("Location: view_request.php?request_id=$request_id");
        exit;
    }
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

?>


<div class="container mt-5">
    <h2 class="mb-4">Shipping Request Details</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Shipping Request Details -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">Shipping Details</h4>
                    <p><strong>Tracking ID:</strong> <?= htmlspecialchars($request['tracking_id']) ?></p>
                    <p><strong>Origin:</strong> <?= htmlspecialchars($request['origin']) ?></p>
                    <p><strong>Destination:</strong> <?= htmlspecialchars($request['destination']) ?></p>
                    <p><strong>Cost:</strong> ZAR <?= number_format($request['cost'], 2) ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-info"><?= htmlspecialchars($request['status']) ?></span></p>
                    <p><strong>Created At:</strong> <?= htmlspecialchars($request['created_at']) ?></p>
                    <p><strong>Updated At:</strong> <?= htmlspecialchars($request['updated_at']) ?></p>
                </div>
            </div>
        </div>
        <!-- Customer Details -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">Customer Details</h4>
                    <p><strong>Name:</strong> <?= htmlspecialchars($customer_name ?? 'N/A') ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($customer_email ?? 'N/A') ?></p>
                    <p><strong>Phone Number:</strong> <?= htmlspecialchars($customer_phone_number ?? 'N/A') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Driver Assignment -->
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Assign a Driver</h4>
            <form method="POST">
                <div class="mb-3">
                    <label for="driver_id" class="form-label">Driver</label>
                    <select name="driver_id" id="driver_id" class="form-select" required>
                        <option value="">-- Select a Driver --</option>
                        <?php foreach ($drivers as $driver): ?>
                            <option value="<?= htmlspecialchars($driver['driver_id']) ?>" 
                                <?= $driver['driver_id'] === $request['driver_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($driver['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Assign Driver</button>
            </form>
        </div>
    </div>

    <!-- Status Update -->
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Update Request Status</h4>
            <form method="POST">
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="">-- Select Status --</option>
                        <option value="Pending" <?= $request['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="In Progress" <?= $request['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Completed" <?= $request['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Cancelled" <?= $request['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </form>
        </div>
    </div>

    <!-- Back Button -->
    <div class="text-end">
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>


<?php include 'footer.php'; ?>
