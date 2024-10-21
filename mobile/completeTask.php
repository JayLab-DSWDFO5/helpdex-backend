<?php
header('Content-Type: application/json');

// Include the database connection
require_once 'databaseOnMobile.php';

// Check if the connection is established
if (!$conn) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    error_log('Database connection failed: ' . mysqli_connect_error());
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if required data is present
if (!isset($data['request_tracker']) || !isset($data['status']) || !isset($data['completion_time']) || !isset($data['resolution_notes'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    error_log('Missing parameters: ' . json_encode($data));
    exit;
}

// Assign values from the POST data
$request_tracker = $data['request_tracker'];
$status = $data['status'];
$completion_time = $data['completion_time'];
$resolution_notes = $data['resolution_notes'];

// Prepare the SQL query to update the ticket status and completion time
$query = "UPDATE requests SET `status` = ?, `date_resolved` = ?, `resolution_notes` = ? WHERE `request_tracker` = ?";
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
    // If preparation of the statement fails, log the error
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare the SQL statement']);
    error_log('SQL Preparation Error: ' . mysqli_error($conn) . ' | Query: ' . $query);
}

// Close the database connection
mysqli_close($conn);
