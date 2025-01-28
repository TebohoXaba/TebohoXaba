<?php
include 'header.php';
require 'db.php';

// Validate the shipping request ID
$request_id = filter_input(INPUT_GET, 'request_id', FILTER_VALIDATE_INT);

if (!$request_id) {
    $_SESSION['error'] = "Invalid request ID.";
    header('Location: admin_dashboard.php');
    exit;
}

// Check if the request ID exists in the database
$stmt = $pdo->prepare("SELECT * FROM shipping_requests WHERE id = :request_id");
$stmt->execute([':request_id' => $request_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    $_SESSION['error'] = "Shipping request not found.";
    header('Location: admin_dashboard.php');
    exit;
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver_id = filter_input(INPUT_POST, 'driver_id', FILTER_VALIDATE_INT);

    if ($driver_id) {
        try {
            // Update the shipping request with the selected driver
            $stmt = $pdo->prepare("UPDATE shipping_requests SET driver_id = :driver_id WHERE id = :request_id");
            $stmt->execute([':driver_id' => $driver_id, ':request_id' => $request_id]);

            $_SESSION['success'] = "Driver assigned successfully.";
            header('Location: admin_dashboard.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating the request: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Please select a valid driver.";
    }
}

// Fetch all drivers and their corresponding user details, sorted alphabetically
$drivers = $pdo->query("
    SELECT d.driver_id, u.name 
    FROM drivers d
    INNER JOIN users u ON d.user_id = u.id
    ORDER BY u.name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-10">
            <h2 class="text-center mb-4">Assign Driver</h2>

            <!-- Success or error message -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" class="card shadow p-4">
                <div class="mb-3">
                    <label for="driver" class="form-label">Select Driver</label>
                    <select id="driver" name="driver_id" class="form-select" required>
                        <option value="">-- Select a Driver --</option>
                        <?php foreach ($drivers as $driver): ?>
                            <option value="<?= $driver['driver_id'] ?>" 
                                <?= isset($request['driver_id']) && $request['driver_id'] == $driver['driver_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($driver['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Assign</button>
            </form>
        </div>
    </div>
</div>

<!-- Include Select2 JS and CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<script>
    // Initialize Select2 on the driver dropdown
    document.addEventListener('DOMContentLoaded', function () {
        $('#driver').select2({
            placeholder: "Search and select a driver",
            width: '100%'
        });
    });
</script>

<?php include 'footer.php'; ?>
