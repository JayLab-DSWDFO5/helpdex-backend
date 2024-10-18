<?php
header('Content-Type: application/json');

// Include database connection
require_once 'databaseOnMobile.php';

// Create a connection using the getConnection function
$conn = getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if required data is present
if (!isset($data['request_tracker']) || !isset($data['status']) || !isset($data['completion_time']) || !isset($data['resolution_notes'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

$request_tracker = $data['request_tracker'];
$status = $data['status'];
$completion_time = $data['completion_time'];
$resolved_notes = $data['resolution_notes'];

// Prepare the SQL query to update the ticket status and completion time
$query = "UPDATE requests SET `status` = ?, `date_resolved` = ?, `resolution_notes` = ? WHERE `request_tracker` = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ssss", $status, $completion_time, $resolved_notes, $request_tracker);

// Execute the query
if (mysqli_stmt_execute($stmt)) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Task completed successfully']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to complete task']);
}

// Close the statement and connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
