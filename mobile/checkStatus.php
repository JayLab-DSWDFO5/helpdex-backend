<?php
header("Content-Type: application/json");

// Include necessary files and initialize database connection
require_once 'databaseOnMobile.php';




if (!$conn) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if required data is present
if (!isset($data['request_tracker'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameter: request_tracker']);
    exit;
}

$request_tracker = $data['request_tracker'];

// Prepare the SQL query to fetch the ticket status
$query = "SELECT status, request_status FROM requests WHERE request_tracker = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $request_tracker);

// Execute the query
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    $ticket = mysqli_fetch_assoc($result);

    if ($ticket) {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'ticket_status' => $ticket['status'],
            'request_status' => $ticket['request_status']
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Ticket not found']);
    }
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch ticket status']);
}

// Close the statement and connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
