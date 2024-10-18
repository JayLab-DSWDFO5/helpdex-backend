<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 for production

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

    // Get the ticket ID and working group from the POST parameters
    $input = json_decode(file_get_contents('php://input'), true);
    $ticketId = isset($input['ticketId']) ? intval($input['ticketId']) : null;
    $workingGroup = isset($input['workingGroup']) ? trim($input['workingGroup']) : null;

    if (!$ticketId || !$workingGroup) {
        throw new Exception("Missing ticket ID or working group");
    }

    // Prepare and execute the query to update the ticket
    $stmt = $conn->prepare("UPDATE requests SET working_group = ?, status = 'Open' WHERE request_id = ?");
    $stmt->bind_param("si", $workingGroup, $ticketId); // Updated to use bind_param for MySQLi
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to assign ticket to group: " . $stmt->error);
    }

    output_json('success', 'Ticket assigned to group successfully');

} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    output_json('error', 'Error assigning ticket to group: ' . $e->getMessage());
}
?>
