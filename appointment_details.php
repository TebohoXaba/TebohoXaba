<?php
include 'header.php';
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please log in to view your appointments.";
    header('Location: login.php');
    exit;
}

// Fetch appointment details
if (isset($_GET['id']) || isset($_GET['tracking_id'])) {
    $query = "SELECT sr.*, 
                     IFNULL(sr.contact_name, u.name) AS contact_name,
                     IFNULL(sr.contact_email, u.email) AS contact_email,
                     IFNULL(sr.contact_phone_number, u.phone_number) AS contact_phone_number
              FROM shipping_requests sr
              INNER JOIN users u ON sr.user_id = u.id 
              WHERE sr.user_id = :user_id";

    if (isset($_GET['id'])) {
        $query .= " AND sr.id = :identifier";
        $identifier = $_GET['id'];
    } elseif (isset($_GET['tracking_id'])) {
        $query .= " AND sr.tracking_id = :identifier";
        $identifier = $_GET['tracking_id'];
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $_SESSION['user']['id'], ':identifier' => $identifier]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        $_SESSION['error'] = "Appointment not found.";
        header('Location: appointments.php');
        exit;
    }
} else {
    $_SESSION['error'] = "Invalid request.";
    header('Location: appointments.php');
    exit;
}

// Handle contact details update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE shipping_requests 
                               SET contact_name = :contact_name, 
                                   contact_email = :contact_email, 
                                   contact_phone_number = :contact_phone_number
                               WHERE id = :id AND user_id = :user_id");
        $stmt->execute([
            ':contact_name' => $_POST['contact_name'],
            ':contact_email' => $_POST['contact_email'],
            ':contact_phone_number' => $_POST['contact_phone_number'],
            ':id' => $appointment['id'],
            ':user_id' => $_SESSION['user']['id'],
        ]);

        $_SESSION['success'] = "Contact information updated successfully.";
        header("Location: appointment_details.php?id=" . $appointment['id']);
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating contact information: " . $e->getMessage();
    }
}
?>

<div class="container py-5">
    <div class="mb-4">
        <h2 class="text-primary">Appointment Details</h2>
        <p class="text-muted">Review your appointment details below. Use the progress tracker to monitor the status of your request.</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="mb-4">
    <h5>Progress Tracker</h5>
    <div class="progress" style="height: 25px;">
        <?php 
        $progress = 0; 
        $progressText = '';
        
        switch ($appointment['status']) {
            case 'Approved': 
                $progress = 25; 
                $progressText = 'Approved'; 
                break;
            case 'In Progress': 
                $progress = 50; 
                $progressText = 'In Progress'; 
                break;
            case 'Delivered': 
                $progress = 100; 
                $progressText = 'Delivered'; 
                break;
            case 'Cancelled': 
                $progress = 0; 
                $progressText = 'Cancelled'; 
                break;
        }
        ?>

        <div class="progress-bar 
            <?= $appointment['status'] === 'Cancelled' ? 'bg-danger' : ($progress === 100 ? 'bg-success' : 'bg-info'); ?>" 
            role="progressbar" 
            style="width: <?= $progress; ?>%;" 
            aria-valuenow="<?= $progress; ?>" 
            aria-valuemin="0" 
            aria-valuemax="100">
            <?= htmlspecialchars($progressText); ?>
        </div>
    </div>
    <?php if ($appointment['status'] === 'Cancelled'): ?>
        <p class="text-danger mt-2"><strong>Notice:</strong> This appointment has been cancelled.</p>
    <?php endif; ?>
</div>

    <!-- Appointment Details -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Details</h5>
            <p><strong>Tracking ID:</strong> <?= htmlspecialchars($appointment['tracking_id']); ?></p>
            <p><strong>Origin:</strong> <?= htmlspecialchars($appointment['origin']); ?></p>
            <p><strong>Destination:</strong> <?= htmlspecialchars($appointment['destination']); ?></p>
            <p><strong>Call-Out Fee:</strong> R <?= number_format($appointment['additional_fee'], 2); ?> 
                (<span class="<?= $appointment['payment_status'] === 'Paid' ? 'text-success' : 'text-danger'; ?>">
                    <?= htmlspecialchars($appointment['payment_status']); ?>
                </span>)
            </p>
            <p><strong>Total Cost:</strong> R <?= number_format($appointment['cost'], 2); ?> 
                (<span class="<?= $appointment['full_payment_status'] === 'Paid' ? 'text-success' : 'text-danger'; ?>">
                    <?= htmlspecialchars($appointment['full_payment_status']); ?>
                </span>)
            </p>
        </div>
    </div>

    <!-- Payment Section -->
    <div class="mt-4">
        <h5>Payment Options</h5>
        <p class="text-muted">Complete the required payments below to ensure uninterrupted processing of your request.</p>
        <?php if ($appointment['payment_status'] !== 'Paid'): ?>
            <a href="pay_additional_fee.php?id=<?= $appointment['id']; ?>" class="btn btn-warning mb-2">
                Pay Call-Out Fee (R <?= number_format($appointment['additional_fee'], 2); ?>)
            </a>
        <?php endif; ?>
        <?php if ($appointment['full_payment_status'] !== 'Paid'): ?>
            <a href="pay_full_amount.php?id=<?= $appointment['id']; ?>" 
               class="btn btn-success <?= $appointment['payment_status'] === 'Pending' ? 'disabled' : ''; ?>" 
               <?= $appointment['payment_status'] === 'Pending' ? 'aria-disabled="true"' : ''; ?>>
                Pay Full Amount (R <?= number_format($appointment['cost'], 2); ?>)
            </a>
        <?php endif; ?>
    </div>

    <!-- Update Contact Info -->
    <form method="POST" class="mt-5">
        <h5>Update Contact Information</h5>
        <div class="mb-3">
            <label for="contact_name" class="form-label">Name</label>
            <input type="text" class="form-control" id="contact_name" name="contact_name" 
                   value="<?= htmlspecialchars($appointment['contact_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="contact_phone_number" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="contact_phone_number" name="contact_phone_number" 
                   value="<?= htmlspecialchars($appointment['contact_phone_number']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="contact_email" class="form-label">Email</label>
            <input type="email" class="form-control" id="contact_email" name="contact_email" 
                   value="<?= htmlspecialchars($appointment['contact_email']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Contact Info</button>
        <a href="view_appointments.php" class="btn btn-secondary">Back to Appointments</a>
    </form>
</div>

<?php include 'footer.php'; ?>
