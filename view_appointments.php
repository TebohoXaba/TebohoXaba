<?php
include 'header.php';
require 'db.php';

if (isset($_SESSION['user'])) {
    $stmt = $pdo->prepare("SELECT * FROM shipping_requests WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user']['id']]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $_SESSION['error'] = "You must log in to view your appointments.";
    header('Location: login.php');
    exit;
}
?>

<div class="container py-5">
    <div class="text-center mb-4">
        <h2>Your Shipping Appointments</h2>
        <p class="text-muted">Below are the details of your scheduled shipping requests.</p>
    </div>

    <?php if (count($appointments) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Appointment ID</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Cost (ZAR)</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Requested On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['id']); ?></td>
                            <td><?= htmlspecialchars($appointment['origin']); ?></td>
                            <td><?= htmlspecialchars($appointment['destination']); ?></td>
                            <td><?= number_format($appointment['cost'], 2); ?></td>
                            <td>
    <a href="appointment_details.php?id=<?= htmlspecialchars($appointment['id']); ?>" class="btn btn-primary btn-sm">
        View Details
    </a>
</td>

                            <td>
                                <span class="badge 
                                    <?= $appointment['status'] == 'Completed' ? 'bg-success' : 
                                        ($appointment['status'] == 'Pending' ? 'bg-warning text-dark' : 'bg-danger'); ?>">
                                    <?= htmlspecialchars($appointment['status']); ?>
                                </span>
                            </td>
                            <td><?= date('F j, Y, g:i a', strtotime($appointment['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <p>No appointments found. <a href="calculate_shipping.php" class="text-decoration-none">Schedule a new shipping request</a>.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
