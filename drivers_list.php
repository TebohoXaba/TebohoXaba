<?php
include 'header.php';
require 'db.php';

// Initialize variables
$searchQuery = '';
$drivers = [];
$limit = 10; // Number of rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
        $searchQuery = trim($_POST['search']);
        
        // Query to search drivers by name, email, license number, or identity number
        $stmt = $pdo->prepare("
            SELECT 
                d.driver_id, 
                d.license_number, 
                d.vehicle_details, 
                d.identity_number, 
                d.driver_image, 
                u.name, 
                u.username, 
                u.email, 
                u.phone_number, 
                u.address 
            FROM drivers d
            INNER JOIN users u ON d.user_id = u.id
            WHERE u.name LIKE :search
               OR u.email LIKE :search
               OR d.license_number LIKE :search
               OR d.identity_number LIKE :search
            ORDER BY d.driver_id DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':search', "%$searchQuery%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Fetch all drivers if no search query is submitted
        $stmt = $pdo->prepare("
            SELECT 
                d.driver_id, 
                d.license_number, 
                d.vehicle_details, 
                d.identity_number, 
                d.driver_image, 
                u.name, 
                u.username, 
                u.email, 
                u.phone_number, 
                u.address 
            FROM drivers d
            INNER JOIN users u ON d.user_id = u.id
            ORDER BY d.driver_id DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    }
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total records for pagination
    $totalDrivers = $pdo->query("SELECT COUNT(*) FROM drivers")->fetchColumn();
    $totalPages = ceil($totalDrivers / $limit);
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to fetch drivers: " . $e->getMessage();
}
?>

<div class="container py-5">
    <h2>Drivers List</h2>

    <!-- Search Form -->
    <form method="POST" class="mb-4">
        <div class="input-group">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Search by name, email, license number, or identity number"
                value="<?= htmlspecialchars($searchQuery); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="drivers_list.php" class="btn btn-secondary">Clear</a>
        </div>
    </form>
    <?php if ($searchQuery): ?>
        <p class="text-muted">Showing results for "<strong><?= htmlspecialchars($searchQuery); ?></strong>"</p>
    <?php endif; ?>

    <!-- Display success or error messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Loader -->
    <div id="loader" style="display:none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function () {
            document.getElementById('loader').style.display = 'block';
        });
    </script>

    <!-- Drivers Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>License Number</th>
                    <th>Vehicle Details</th>
                    <th>Identity Number</th>
                    <th>Driver Image</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($drivers)): ?>
                    <?php foreach ($drivers as $driver): ?>
                        <tr>
                            <td><?= htmlspecialchars($driver['driver_id']); ?></td>
                            <td><?= htmlspecialchars($driver['name']); ?></td>
                            <td><?= htmlspecialchars($driver['username']); ?></td>
                            <td><?= htmlspecialchars($driver['email']); ?></td>
                            <td><?= htmlspecialchars($driver['phone_number']); ?></td>
                            <td><?= htmlspecialchars($driver['address']); ?></td>
                            <td><?= htmlspecialchars($driver['license_number']); ?></td>
                            <td><?= htmlspecialchars($driver['vehicle_details']); ?></td>
                            <td><?= htmlspecialchars($driver['identity_number']); ?></td>
                            <td>
                                <?php if (!empty($driver['driver_image'])): ?>
                                    <img src="<?= htmlspecialchars($driver['driver_image']); ?>" alt="Driver Image" class="img-thumbnail" width="100">
                                <?php else: ?>
                                    <span class="text-muted">No image available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted">No drivers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php include 'footer.php'; ?>
