<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    require_once 'db_connection.php';

    // Check if the connection is successful
    if (!$conn) {
        output_json('error', 'Database connection failed');
    }

    // Get the ticket ID from the GET parameters
    $ticketId = filter_input(INPUT_GET, 'ticketId', FILTER_VALIDATE_INT);

    if (!$ticketId) {
        output_json('error', 'No valid ticket ID provided');
    }

    // Prepare and execute the query to get ticket details
    $query = "
        SELECT r.*, t.tech_name, t.group_desc, t.tech_designation, t.tech_priv
        FROM requests r
        LEFT JOIN technician t ON r.assigned_it_person = t.tech_id
        WHERE r.request_id = ?
    ";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $ticketId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ticketDetails = mysqli_fetch_assoc($result);

    if (!$ticketDetails) {
        output_json('error', 'Ticket not found');
    }

    output_json('success', 'Ticket details fetched successfully', $ticketDetails);

} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    output_json('error', 'An unexpected error occurred: ' . $e->getMessage());
}
?>
