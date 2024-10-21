<?php
header('Content-Type: application/json');

// Include database connection
require_once 'databaseOnMobile.php';

if (!$conn) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if required data is present
if (!isset($data['request_tracker']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

$request_tracker = $data['request_tracker'];
$status = $data['status'];

// Log input values for debugging
error_log("Updating ticket with request_tracker: $request_tracker, status: $status");

// Prepare the SQL query to update the ticket status
$query = "UPDATE requests SET status = ? WHERE request_tracker = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $status, $request_tracker);

// Execute the query
if (mysqli_stmt_execute($stmt)) {
    // Check if rows were actually affected
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        // If status is 'In Progress', update the start_time
        if ($status == 'In Progress') {
            $updateTimeQuery = "UPDATE requests SET start_time = CURRENT_TIMESTAMP WHERE request_tracker = ?";
            $timeStmt = mysqli_prepare($conn, $updateTimeQuery);
            mysqli_stmt_bind_param($timeStmt, "s", $request_tracker);
            if (mysqli_stmt_execute($timeStmt)) {
                mysqli_stmt_close($timeStmt);
            } else {
                error_log("Failed to update start_time for request_tracker: $request_tracker");
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Ticket status updated successfully']);
    } else {
        // No rows were affected, meaning the request_tracker might not exist
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'No ticket found with the provided request_tracker']);
    }
} else {
    error_log("Failed to execute query: " . mysqli_error($conn));
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update ticket status']);
}

// Close the statement and connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
