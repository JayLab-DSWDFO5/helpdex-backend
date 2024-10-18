<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 for production

// Set the content type to JSON
header('Content-Type: application/json');
require_once '../../../backend/cloud_db_connection.php'; // Updated to use cloud_db_connection.php

session_start(); // Start the session to access session variables

// Check if the database connection was successful
if (!$conn) {
    http_response_code(500);
    output_json('error', 'Database connection failed', null);
}

// Use the group_desc from the session instead of the GET parameter
$workingGroup = isset($_SESSION['user']['working_group']) ? $_SESSION['user']['working_group'] : '';

if (empty($workingGroup)) {
    http_response_code(400);
    output_json('error', 'Working group is required.');
}

// Use MySQLi to prepare and execute the query
$query = "SELECT * FROM requests WHERE working_group = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    http_response_code(500);
    output_json('error', 'Query preparation failed: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 's', $workingGroup);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$tickets = mysqli_fetch_all($result, MYSQLI_ASSOC);

$response = [
    'status' => $tickets ? 'success' : 'error',
    'data' => $tickets ?: ['message' => 'No tickets found for this working group.']
];

http_response_code($tickets ? 200 : 404);
echo json_encode($response);

function output_json($status, $message, $data = null) {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}
