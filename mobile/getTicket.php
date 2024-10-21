<?php
header('Content-Type: application/json');

// Include database connection
require_once 'databaseOnMobile.php';

if (!$conn) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Check if the request_tracker parameter is present
if (!isset($_GET['request_tracker'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing request_tracker parameter']);
    exit;
}

$request_tracker = $_GET['request_tracker'];

// Prepare the SQL query to fetch ticket details
$query = "SELECT * FROM requests WHERE request_tracker = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $request_tracker);

// Execute the query
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);

    // Check if any record is found
    if ($ticket = mysqli_fetch_assoc($result)) {
        http_response_code(200);
        echo json_encode($ticket); // Send the ticket data as JSON
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Ticket not found']);
    }
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch ticket data']);
}

// Close the statement and connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
