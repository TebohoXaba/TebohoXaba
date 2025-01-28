<?php
require 'db.php'; // Ensure the database connection is included

function requireRole($role) {
    global $pdo; // Use the global $pdo variable

    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = "Access denied. Please log in.";
        header("Location: login.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT r.name FROM roles r 
                           INNER JOIN users u ON u.role_id = r.id 
                           WHERE u.id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user']['id']]);
    $userRole = $stmt->fetchColumn();

    if ($userRole !== $role) {
        $_SESSION['error'] = "Access denied. You do not have the required permissions.";
        header("Location: index.php");
        exit;
    }
}
?>
