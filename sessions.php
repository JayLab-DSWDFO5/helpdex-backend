<?php
session_start();
error_reporting(E_ALL);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'cloud_db_connection.php';

try {
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit;
    }

    // Get the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if username and password are provided
    if (empty($data['username']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password are required']);
        exit;
    }

    // Get username and password
    $username = trim($data['username']);
    $password = $data['password'];

    // Use the existing database connection
    global $conn; // Use the connection from db_connection.php

    // Prepare SQL statement to prevent SQL injection
    $query = "SELECT * FROM technician WHERE tech_username = ? AND tech_password = ?";
    $stmt = mysqli_prepare($conn, $query);

    // Bind parameters
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Password is correct, create session
        $_SESSION['user'] = [
            'tech_id' => $user['tech_id'],
            'tech_name' => $user['tech_name'],
            'working_group' => $user['group_desc'],
            'group_desc' => $user['group_desc'],
            'privileges' => $user['tech_priv'],
        ];
        echo json_encode(['success' => true, 'message' => 'Login successful', 'user' => $user]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
