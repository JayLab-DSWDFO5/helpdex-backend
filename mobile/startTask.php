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

// Prepare the SQL query to update the ticket status
$query = "UPDATE requests SET status = ? WHERE request_tracker = ?";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $status, $request_tracker);

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        // If status is 'In Progress', update the start_time
        if ($status === 'In Progress') {
            $updateTimeQuery = "UPDATE requests SET start_time = CURRENT_TIMESTAMP WHERE request_tracker = ?";
            $timeStmt = mysqli_prepare($conn, $updateTimeQuery);

            if ($timeStmt) {
                mysqli_stmt_bind_param($timeStmt, "s", $request_tracker);
                mysqli_stmt_execute($timeStmt);
                mysqli_stmt_close($timeStmt);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to prepare update time statement']);
                exit;
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Ticket status updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update ticket status']);
    }

    // Close the statement
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement']);
}

// Close the database connection
mysqli_close($conn);
