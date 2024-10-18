<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set the content type to JSON
header('Content-Type: application/json');

// Function to output JSON and exit
function output_json($status, $message, $data = null) {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    // Include the database connection file
    require_once 'cloud_db_connection.php'; // Updated to use cloud_db_connection.php

    // Check if the connection is established
    if (!$conn) {
        throw new Exception("Database connection not established");
    }

    // Get the ticket ID from the GET parameters
    $ticketId = isset($_GET['ticketId']) ? intval($_GET['ticketId']) : null;

    if (!$ticketId) {
        throw new Exception("No ticket ID provided");
    }

    // Prepare and execute the query to get comments with commenter's name
    $stmt = $conn->prepare("SELECT c.*, t.tech_name AS commenter_name 
                            FROM comments c 
                            JOIN technician t ON c.user_id = t.tech_id 
                            WHERE c.ticket_id = ? 
                            ORDER BY c.created_at DESC");
    $stmt->bind_param("i", $ticketId); // Updated to use bind_param for MySQLi
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = $result->fetch_all(MYSQLI_ASSOC); // Updated to use fetch_all for MySQLi

    output_json('success', 'Comments fetched successfully', $comments);

} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    output_json('error', 'An error occurred while fetching comments');
}
?>
