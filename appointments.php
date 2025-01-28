<?php
require 'db.php'; // Ensure this initializes the $pdo variable for PDO connection

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please log in to view your appointments.";
    header('Location: login.php');
    exit;
}

// Fetch user's appointments from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $_SESSION['user']['id']]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Error retrieving appointments: " . $e->getMessage();
    header('Location: index.php');
    exit;
}
?>

<h2>Your Appointments</h2>

<?php if (isset($_SESSION['error'])): ?>
    <p style="color: red;"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <p style="color: green;"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
<?php endif; ?>

<?php if (!empty($appointments)): ?>
    <table border="1">
        <thead>
            <tr>
                <th>Origin</th>
                <th>Destination</th>
                <th>Distance (km)</th>
                <th>Cost (ZAR)</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $appointment): ?>
                <tr>
                    <td><?= htmlspecialchars($appointment['origin']); ?></td>
                    <td><?= htmlspecialchars($appointment['destination']); ?></td>
                    <td><?= htmlspecialchars($appointment['distance_km']); ?></td>
                    <td><?= number_format($appointment['cost'], 2); ?></td>
                    <td><?= htmlspecialchars($appointment['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You have no appointments yet.</p>
<?php endif; ?>

<?php include 'footer.php'; ?>
