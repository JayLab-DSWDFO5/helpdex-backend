<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connection.php';

try {
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    // Check if the request method is GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        exit;
    }

    // Check if user is logged in and has a group_desc
    if (!isset($_SESSION['user']) || empty($_SESSION['user']['group_desc'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $userGroupDesc = $_SESSION['user']['group_desc'];

    // Get the database connection
    global $conn; // Use the connection from db_connection.php

    // Prepare SQL statement to fetch technicians within the user's group_desc
    $query = "SELECT tech_id, tech_name FROM technician WHERE group_desc = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $userGroupDesc);
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $technicians = mysqli_fetch_all($result, MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $technicians]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
