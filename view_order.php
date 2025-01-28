<?php
include 'header.php';
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please log in to access the order details.";
    header('Location: login.php');
    exit;
}

// Ensure the user has a driver role
if ($_SESSION['user']['role_id'] != 3) {
    $_SESSION['error'] = "You do not have permission to access this page.";
    header('Location: index.php');
    exit;
}

// Get the request ID from the URL
$request_id = filter_input(INPUT_GET, 'request_id', FILTER_VALIDATE_INT);

if (!$request_id) {
    $_SESSION['error'] = "Invalid request ID.";
    header('Location: driver_dashboard.php');
    exit;
}

// Fetch the shipping request details
$stmt = $pdo->prepare("SELECT * FROM shipping_requests WHERE id = :request_id");
$stmt->execute([':request_id' => $request_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    $_SESSION['error'] = "Shipping request not found.";
    header('Location: driver_dashboard.php');
    exit;
}

// Determine the contact details
$contact_name = $request['contact_name'];
$contact_email = $request['contact_email'];
$contact_phone_number = $request['contact_phone_number'];

if (empty($contact_name) || empty($contact_email) || empty($contact_phone_number)) {
    $stmt = $pdo->prepare("SELECT name, email, phone_number FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $request['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $contact_name = $contact_name ?: $user['name'];
        $contact_email = $contact_email ?: $user['email'];
        $contact_phone_number = $contact_phone_number ?: $user['phone_number'];
    }
}
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Order Details - <span class="text-primary"><?= htmlspecialchars($request['tracking_id']) ?></span></h2>

    <!-- Order Details Card -->
    <div class="card shadow-lg">
        <div class="card-body">
            <h4 class="card-title">Shipping Information</h4>
            <hr>
            <p><strong>Origin:</strong> <?= htmlspecialchars($request['origin']) ?></p>
            <p><strong>Destination:</strong> <?= htmlspecialchars($request['destination']) ?></p>
            <p><strong>Cost:</strong> <span class="badge bg-info text-dark">ZAR <?= number_format($request['cost'], 2) ?></span></p>
            <p><strong>Status:</strong> 
                <span class="badge 
                    <?= $request['status'] === 'In Progress' ? 'bg-warning' : 
                       ($request['status'] === 'Delivered' ? 'bg-success' : 'bg-danger') ?>">
                    <?= htmlspecialchars($request['status']) ?>
                </span>
            </p>

            <h4 class="mt-4">Customer Contact Details</h4>
            <hr>
            <p><strong>Name:</strong> <?= htmlspecialchars($contact_name) ?></p>
            <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($contact_email) ?>"><?= htmlspecialchars($contact_email) ?></a></p>
            <p><strong>Phone Number:</strong> <a href="tel:<?= htmlspecialchars($contact_phone_number) ?>"><?= htmlspecialchars($contact_phone_number) ?></a></p>
        </div>
    </div>

    <!-- Update Status Form -->
    <div class="card shadow-lg mt-4">
        <div class="card-body">
            <h4 class="card-title">Update Status</h4>
            <form method="POST" action="update_customer_status.php">
                <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']) ?>">
                <div class="mb-3">
                    <label for="status" class="form-label">Current Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="In Progress" <?= $request['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Delivered" <?= $request['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="Cancelled" <?= $request['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100">Update Status</button>
            </form>
        </div>
    </div>

    <!-- Routes -->
    <div class="card shadow-lg mt-4">
        <div class="card-body">
            <h4 class="card-title">Routes</h4>
            <hr>
            <div class="d-grid gap-2">
                <a href="https://www.google.com/maps/dir/?api=1&origin=<?= urlencode($request['origin']) ?>" target="_blank" class="btn btn-primary">View Route to Origin</a>
                <a href="https://www.google.com/maps/dir/?api=1&origin=<?= urlencode($request['origin']) ?>&destination=<?= urlencode($request['destination']) ?>" target="_blank" class="btn btn-primary">View Route to Destination</a>
            </div>
        </div>
    </div>
</div>
<br><br><br>
<?php include 'footer.php'; ?>
