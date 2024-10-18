<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set the content type to JSON
header('Content-Type: application/json');

// Function to output JSON and exit
function output_json($status, $message, $data = null) {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    // Include the database connection file
    require_once 'cloud_db_connection.php'; // Updated path to the correct database connection file

    // Check if the connection is established
    if (!$conn) {
        throw new Exception("Database connection not established");
    }

    // Get the POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (!isset($input['ticketId']) || !isset($input['comment'])) {
        throw new Exception("Missing required fields");
    }

    $ticketId = intval($input['ticketId']);
    $comment = trim($input['comment']);

    if (empty($comment)) {
        throw new Exception("Comment cannot be empty");
    }

    // Get the current user ID from the session
    session_start();
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['tech_id'])) {
        throw new Exception("User not authenticated");
    }
    $userId = $_SESSION['user']['tech_id'];

    // Insert the comment
    $stmt = $conn->prepare("INSERT INTO comments (ticket_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $ticketId, $userId, $comment); // Updated to use bind_param for MySQLi
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert comment: " . $stmt->error);
    }

    $commentId = $conn->insert_id; // Updated to use insert_id for MySQLi

    // Fetch the inserted comment
    $stmt = $conn->prepare("SELECT c.*, t.tech_name as username FROM comments c JOIN technician t ON c.user_id = t.tech_id WHERE c.comment_id = ?");
    $stmt->bind_param("i", $commentId); // Updated to use bind_param for MySQLi
    $stmt->execute();
    $commentData = $stmt->get_result()->fetch_assoc(); // Updated to use get_result for MySQLi

    output_json('success', 'Comment added successfully', $commentData);

} catch (Exception $e) {
    error_log('Exception in addComment.php: ' . $e->getMessage());
    output_json('error', $e->getMessage());
}
?>
