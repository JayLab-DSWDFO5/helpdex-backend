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

// Get the assigned IT person from the request
$data = json_decode(file_get_contents('php://input'), true);
$assigned_it_person = $data['assigned_it_person'] ?? null;

if (!$assigned_it_person) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Assigned IT person is required']);
    exit;
}

// Prepare the SQL query
$query = "SELECT r.*, o.office_name, o.office_location, o.office_type 
          FROM requests r
          LEFT JOIN office_location o ON r.request_location = o.office_id
          WHERE r.assigned_it_person = ? AND r.status NOT IN ('Completed', 'With CSMR')";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $assigned_it_person);

// Execute the query
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    $tickets = mysqli_fetch_all($result, MYSQLI_ASSOC);

    http_response_code(200);
    echo json_encode(['status' => 'success', 'tickets' => $tickets]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch assigned tickets']);
}

// Close the statement and connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
