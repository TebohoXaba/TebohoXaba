<?php
require 'header.php';
require 'middleware.php';
requireRole('admin'); // Only admins can access this page

// Get the search term if provided
$search = $_GET['search'] ?? '';

// Fetch all users and their roles with optional search filtering
$query = "
    SELECT u.id, u.name, u.email, r.name AS role 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id
";
if ($search) {
    $query .= " WHERE u.name LIKE :search OR u.email LIKE :search OR r.name LIKE :search";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':search' => "%$search%"]);
} else {
    $stmt = $pdo->query($query);
}
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all available roles
$rolesStmt = $pdo->query("SELECT * FROM roles");
$roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $roleId = $_POST['role_id'];

    try {
        $updateStmt = $pdo->prepare("UPDATE users SET role_id = :role_id WHERE id = :user_id");
        $updateStmt->execute([':role_id' => $roleId, ':user_id' => $userId]);

        $_SESSION['success'] = "Role updated successfully.";
        header('Location: manage_roles.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating role: " . $e->getMessage();
    }
}
?>

<div class="container mt-5">
    <h1 class="mb-4 text-center">Manage User Roles</h1>

    <!-- Search Form -->
    <form method="GET" class="mb-4">
        <div class="input-group">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Search by name, email, or role" 
                value="<?= htmlspecialchars($search); ?>"
            >
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <!-- Display Success or Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Current Role</th>
                    <th>Change Role</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users): ?>
                    <?php foreach ($users as $index => $user): ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><?= htmlspecialchars($user['name']); ?></td>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                            <td><?= htmlspecialchars($user['role'] ?: 'N/A'); ?></td>
                            <td>
                                <form method="POST" class="d-flex align-items-center" onsubmit="return confirm('Are you sure you want to update this user\'s role?')">
                                    <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                    <select 
                                        name="role_id" 
                                        class="form-select me-2"
                                        aria-label="Select role for <?= htmlspecialchars($user['name']); ?>"
                                    >
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role['id']; ?>" <?= $user['role'] === $role['name'] ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($role['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary" aria-label="Update role for <?= htmlspecialchars($user['name']); ?>">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
