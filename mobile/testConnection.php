<?php
// Include the Database class
require_once 'databaseOnMobile.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Create a connection using the getConnection function
$conn = getConnection(); // Directly call the function instead of using a Database class

// Test the connection
if ($conn) {
    echo json_encode(["status" => "success", "message" => "Yey! Mobile database connection established successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
}
?>