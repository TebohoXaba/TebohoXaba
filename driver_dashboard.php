<?php
include 'header.php';
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please log in to access the driver dashboard.";
    header('Location: login.php');
    exit;
}

// Ensure the user has a driver role
if ($_SESSION['user']['role_id'] != 3) {
    $_SESSION['error'] = "You do not have permission to access this page.";
    header('Location: index.php');
    exit;
}

// Fetch the driver's ID from the drivers table using the logged-in user's ID
$stmt = $pdo->prepare("SELECT driver_id FROM drivers WHERE user_id = :user_id");
$stmt->execute([':user_id' => $_SESSION['user']['id']]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$driver) {
    $_SESSION['error'] = "You are not registered as a driver.";
    header('Location: index.php');
    exit;
}

$driver_id = $driver['driver_id'];

// Fetch shipping requests assigned to the logged-in driver, excluding those with status 'Delivered'
$stmt = $pdo->prepare("SELECT * FROM shipping_requests WHERE driver_id = :driver_id AND status != 'Delivered' ORDER BY created_at DESC");
$stmt->execute([':driver_id' => $driver_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="container mt-5">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Pages</a></li>
            <li class="breadcrumb-item active" aria-current="page">Driver Dashboard</li>
        </ol>
    </nav>

    <!-- Header and Greeting -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Driver Dashboard</h2>
        <div class="text-muted">
            Welcome, <strong><?= htmlspecialchars($_SESSION['user']['name']) ?></strong>
        </div>
    </div>

    <!-- Error Message -->
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Active Shipping Appointments -->
    <?php if (empty($appointments)): ?>
        <p class="text-center text-muted">No shipping requests assigned to you at the moment.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Tracking ID</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['tracking_id']) ?></td>
                            <td><?= htmlspecialchars($appointment['origin']) ?></td>
                            <td><?= htmlspecialchars($appointment['destination']) ?></td>
                            <td>
                                <?php
                                    // Status Badge with Color
                                    $status = htmlspecialchars($appointment['status']);
                                    if ($status == 'Pending') {
                                        echo '<span class="badge bg-warning text-dark">' . $status . '</span>';
                                    } elseif ($status == 'In Transit') {
                                        echo '<span class="badge bg-primary">' . $status . '</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary">' . $status . '</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <a href="view_order.php?request_id=<?= htmlspecialchars($appointment['id']) ?>" class="btn btn-sm btn-info shadow-sm">View Order</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
