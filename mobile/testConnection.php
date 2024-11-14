<?php
// Include the Database class
require_once 'databaseOnMobile.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Establish the connection using MySQLi
$conn = mysqli_connect($host, $username, $password, $db_name);  // Use your connection details

// Check the connection
if ($conn) {
    echo json_encode(["status" => "success", "message" => "Yey! Mobile database connection established successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "....Database connection failed: " . mysqli_connect_error()]);
}

// Close the connection
mysqli_close($conn);
