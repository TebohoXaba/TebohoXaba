<?php
// Database configuration
$host = "localhost";
$username = "zxfleetc_shopping"; // Replace with your database username
$password = "Pass1475**"; // Replace with your database password
$dbname = "zxfleetc_shopping"; // Replace with your database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]));
}

// Query to fetch top products
$sql = "SELECT id, name, price, image 
        FROM products 
        ORDER BY popularity DESC 
        LIMIT 5";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch all rows as an associative array
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    // Respond with product data
    echo json_encode([
        "success" => true,
        "data" => $products
    ]);
} else {
    // No products found
    echo json_encode([
        "success" => false,
        "message" => "No products found."
    ]);
}

// Close the connection
$conn->close();
?>
