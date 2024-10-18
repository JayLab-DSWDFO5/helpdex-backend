<?php
header('Content-Type: application/json');

// Use the POST method for RESTful API
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? null;
$password = $data['password'] ?? null;

if (!$username || !$password) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Username and password are required']);
    exit;
}

// Include database connection
require_once '../cloud_db_connection.php.php';

// Create a connection using the getConnection function
$conn = getConnection();

if (!$conn) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

function authenticateUser($username, $password, $conn)
{
    $query = "SELECT * FROM `technician` WHERE tech_username = ? AND tech_password = ?"; // Corrected quotes for column names
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password); // Bind parameters
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Check for errors in the query execution
    if (!$result) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Query execution failed']);
        exit;
    }

    return mysqli_fetch_assoc($result);
}

// Authenticate user
$user = authenticateUser($username, $password, $conn);

if ($user) {
    http_response_code(200); // OK
    echo json_encode(['status' => 'success', 'user' => $user]);
} else {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Invalid username or password', 'username' => $username, 'password' => $password]);
}
