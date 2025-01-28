<?php
include 'header.php';
require 'db.php';

// Initialize variables for search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Fetch filtered shipping requests based on search query
    $stmt = $pdo->prepare("
        SELECT sr.*, u.name AS driver_name 
        FROM shipping_requests sr 
        LEFT JOIN drivers d ON sr.driver_id = d.driver_id
        LEFT JOIN users u ON d.user_id = u.id
        WHERE sr.status != 'Pending' 
        AND (sr.tracking_id LIKE :search OR u.name LIKE :search)
        ORDER BY sr.created_at DESC
    ");
    $stmt->execute(['search' => "%$search%"]);
    $shipping_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching shipping requests: " . $e->getMessage());
}
?>

<div class="container mt-5">
    <!-- Admin Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Admin Dashboard - Shipping Requests</h2>
        <div class="text-muted">
            Welcome, <strong><?= htmlspecialchars($_SESSION['user']['name']) ?></strong>
        </div>
    </div>

    <!-- Search Form -->
    <form method="get" class="mb-4">
        <div class="input-group">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Search by Tracking ID or Driver Name" 
                value="<?= htmlspecialchars($search) ?>"
            >
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    <!-- No shipping requests message -->
    <?php if (empty($shipping_requests)): ?>
        <p class="text-center text-muted">No shipping requests found.</p>
    <?php else: ?>
        <!-- Shipping Requests Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Tracking ID</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Cost</th>
                        <th>Status</th>
                        <th>Driver</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shipping_requests as $index => $request): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($request['tracking_id']) ?></td>
                            <td><?= htmlspecialchars($request['origin']) ?></td>
                            <td><?= htmlspecialchars($request['destination']) ?></td>
                            <td>ZAR <?= number_format($request['cost'], 2) ?></td>
                            <td>
                                <?php
                                    // Status Badge with Color
                                    $status = htmlspecialchars($request['status']);
                                    if ($status === 'Delivered') {
                                        echo '<span class="badge bg-success">' . $status . '</span>';
                                    } elseif ($status === 'In Progress') {
                                        echo '<span class="badge bg-primary">' . $status . '</span>';
                                    } else {
                                        echo '<span class="badge bg-warning text-dark">' . $status . '</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <?= $request['driver_name'] 
                                    ? htmlspecialchars($request['driver_name']) 
                                    : '<span class="text-danger">Unassigned</span>' 
                                ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="assign_driver.php?request_id=<?= htmlspecialchars($request['id']) ?>" 
                                       class="btn btn-sm btn-primary" 
                                       data-bs-toggle="tooltip" 
                                       title="Assign a driver to this request">
                                        Assign Driver
                                    </a>
                                    <a href="update_status.php?request_id=<?= htmlspecialchars($request['id']) ?>" 
                                       class="btn btn-sm btn-secondary" 
                                       data-bs-toggle="tooltip" 
                                       title="Update the status of this request">
                                        Update Status
                                    </a>
                                    <a href="view_request.php?request_id=<?= htmlspecialchars($request['id']) ?>" 
                                       class="btn btn-sm btn-info" 
                                       data-bs-toggle="tooltip" 
                                       title="View full details of this request">
                                        View Request
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    // Enable Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip']'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>

<?php include 'footer.php'; ?>
