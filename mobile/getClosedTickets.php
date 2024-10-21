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
if (!isset($data['tech_name'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

$tech_name = $data['tech_name'];

// Prepare the SQL query to fetch completed tickets with CSMR and join with office_location
$query = "SELECT r.*, o.office_name, o.office_location 
          FROM requests r
          LEFT JOIN office_location o ON r.request_location = o.office_id
          WHERE r.assigned_it_person = ? AND r.status IN ('With CSMR', 'Completed')";


$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $tech_name); // Changed "i" to "s" to accommodate string tech_id

// Execute the query
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$completedTickets = [];
while ($row = mysqli_fetch_assoc($result)) {
    $completedTickets[] = $row;
}

// Return the completed tickets as a JSON response
http_response_code(200);
echo json_encode(['status' => 'success', 'tickets' => $completedTickets]);

// Close the statement and connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
