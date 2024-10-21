<?php
header('Content-Type: application/json');

// Include database connection
require_once 'databaseOnMobile.php';

// Create a connection using the getConnection function


if (!$conn) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if required data is present
if (!isset($data['request_tracker']) || !isset($data['action']) || !isset($data['elapsed_time'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

$request_tracker = $data['request_tracker'];
$action = $data['action'];
$elapsed_time = $data['elapsed_time'];

// Prepare the SQL query to update the ticket status and elapsed time
$query = "UPDATE requests SET status = ?, elapsed_time = ? WHERE request_tracker = ?";
$status = ($action == 'pause') ? 'Paused' : 'In Progress';

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sis", $status, $elapsed_time, $request_tracker);

// Execute the query
if (mysqli_stmt_execute($stmt)) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Task ' . $action . 'd successfully']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update task status']);
}

// Close the statement and connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
