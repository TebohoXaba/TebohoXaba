<?php
include 'header.php';
require 'db.php';

if (isset($_SESSION['user'])) {
    // Fetch active shipping appointments for the logged-in user
    $stmt = $pdo->prepare("
        SELECT * 
        FROM shipping_requests 
        WHERE user_id = :user_id 
          AND status NOT IN ('Delivered', 'Cancelled') 
        ORDER BY created_at DESC
    ");
    $stmt->execute([':user_id' => $_SESSION['user']['id']]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger text-center">
        <?= $_SESSION['error']; ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<!-- Main Container -->
<div class="container mt-5">
    <!-- Welcome Section -->
    <div class="text-center mb-5">
        <h2 class="fw-bold">
            Welcome, <?= isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['name']) : 'Guest'; ?>!
        </h2>
        <p class="text-muted">
            <?= isset($_SESSION['user']) ? 'Track your shipments or book a new service below.' : 'Log in to access your appointments and tracking.'; ?>
        </p>
    </div>

    <!-- Hero Section -->
    <div class="jumbotron jumbotron-fluid text-white text-center rounded-3 shadow-sm mb-5" style="background-color: darkslategrey;">
        <div class="container py-5">
            <h1 class="display-4 fw-bold text-white">Logistics Services</h1>
            <p class="lead mb-4">Fast, Reliable, and Affordable Shipping Solutions Tailored for You.</p>
            <form class="d-flex justify-content-center" method="POST" action="track.php">
                <div class="input-group" style="max-width: 600px;">
                    <input type="text" 
                           class="form-control form-control-lg border-light shadow-sm" 
                           placeholder="Enter Tracking ID" 
                           name="tracking_id" 
                           required>
                    <button type="submit" class="btn btn-light btn-lg shadow-sm">Track & Trace</button>
                </div>
            </form>
            <div class="mt-4">
                <a href="calculate_shipping.php" class="btn btn-outline-light btn-lg shadow-sm">Make an Appointment</a>
            </div>
        </div>
    </div>

    <!-- Active Appointments Section -->
    <?php if (isset($_SESSION['user']) && !empty($appointments)): ?>
        <div class="mt-5">
            <h3 class="text-center mb-4">Your Active Shipping Appointments</h3>
            <div class="row">
                <?php foreach ($appointments as $appointment): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body" style="background-color: lightgrey;">
                                <h5 class="card-title text-primary"><?= htmlspecialchars($appointment['origin']); ?> → <?= htmlspecialchars($appointment['destination']); ?></h5>
                                <p class="card-text">
                                    <strong>Status:</strong> <span class="badge bg-info text-dark"><?= htmlspecialchars($appointment['status']); ?></span><br>
                                    <strong>Cost:</strong> ZAR <?= number_format($appointment['cost'], 2); ?>
                                </p>
                                <a href="appointment_details.php?id=<?= $appointment['id']; ?>" class="btn btn-dark btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php elseif (isset($_SESSION['user'])): ?>
        <div class="text-center text-muted mt-5">
            <p>You have no active appointments at the moment.</p>
            <a href="calculate_shipping.php" class="btn btn-dark">Book a Shipment</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
