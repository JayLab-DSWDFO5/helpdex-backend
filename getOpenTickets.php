<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connection.php';
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


    // Check connection
    if (!$conn) {
        output_json('error', 'Database connection failed');
    }

    $query = "SELECT * FROM requests WHERE status = 'Open' AND working_group='' ORDER BY request_createdatetime DESC";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        output_json('error', 'Query execution failed: ' . mysqli_error($conn));
    }

    $tickets = mysqli_fetch_all($result, MYSQLI_ASSOC);

    output_json('success', 'Tickets fetched successfully', $tickets);

} catch (Exception $e) {
    error_log('Database error: ' . $e->getMessage());
    output_json('error', 'An error occurred while fetching tickets: ' . $e->getMessage());
}
