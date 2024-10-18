<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 for production
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'cloud_db_connection.php'; // Updated to use cloud_db_connection.php

try {
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        exit;
    }

    // Check if user is logged in and has appropriate privileges
    if (!isset($_SESSION['user']) || $_SESSION['user']['privileges'] !== 'teamlead') {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    // Get the POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $ticketId = $data['ticketId'] ?? null;
    $techName = $data['techName'] ?? null;

    if (!$ticketId || !$techName) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit;
    }

    // Get the database connection
    $conn = getConnection(); // Use the existing function to get the database connection

    // Check connection
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    // Start a transaction
    mysqli_begin_transaction($conn);

    // Prepare SQL statement to update the ticket
    $query = "UPDATE requests SET assigned_it_person = ?, status = 'Assigned' WHERE request_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'si', $techName, $ticketId);

    if (mysqli_stmt_execute($stmt)) {
        // Check if any rows were affected
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            // Commit the transaction
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Ticket assigned successfully']);
        } else {
            // No rows were affected, rollback the transaction
            mysqli_rollback($conn);
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'No ticket found with the given ID']);
        }
    } else {
        // Rollback the transaction
        mysqli_rollback($conn);
        throw new Exception('Failed to assign ticket');
    }

} catch (Exception $e) {
    // Ensure rollback in case of any exception
    if (isset($conn) && mysqli_commit($conn)) {
        mysqli_rollback($conn);
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
