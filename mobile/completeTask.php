<?php
header('Content-Type: application/json');

// Include database connection
require_once 'databaseOnMobile.php';

if (!$conn) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed', 'error' => mysqli_connect_error()]);
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
$resolution_notes = $data['resolution_notes'];


// Prepare the SQL query to update the ticket status and completion details
$query = "UPDATE requests SET `status` = ?, `completion_time` = ?, `resolution_notes` = ? WHERE `request_tracker` = ?";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    // Bind parameters and execute the statement
    mysqli_stmt_bind_param($stmt, "ssss", $status, $completion_time, $resolution_notes, $request_tracker);

    if (mysqli_stmt_execute($stmt)) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Task completed successfully']);
    } else {
        // If execution fails, log the error and provide an error message
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to complete task', 'error' => mysqli_error($conn)]);
        error_log('SQL Execution Error: ' . mysqli_error($conn) . ' | Query: ' . $query . ' | Parameters: ' . json_encode([$status, $completion_time, $resolution_notes, $request_tracker]));
    }

    // Close the statement
    mysqli_stmt_close($stmt);
} else {
    // If statement preparation fails, provide detailed error info
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare SQL statement', 'error' => mysqli_error($conn)]);
    error_log('SQL Preparation Error: ' . mysqli_error($conn) . ' | Query: ' . $query . ' | Parameters: ' . json_encode([$status, $completion_time, $resolution_notes, $request_tracker]));
}

// Close the database connection
mysqli_close($conn);
