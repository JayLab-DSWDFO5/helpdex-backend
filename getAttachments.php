<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 for production

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
    require_once 'cloud_db_connection.php'; // Updated to use cloud_db_connection.php

    // Check if the connection is established
    if (!$conn) {
        output_json('error', 'Database connection failed');
    }

    // Get the ticket ID from the GET parameters
    $ticketId = isset($_GET['ticketId']) ? intval($_GET['ticketId']) : null;

    if (!$ticketId) {
        output_json('error', 'No ticket ID provided');
    }

    // Prepare and execute the query to get attachment details
    $stmt = $conn->prepare("SELECT request_attachment, folder_id FROM requests WHERE request_id = ?");
    $stmt->bind_param("i", $ticketId); // Updated to use bind_param for MySQLi
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc(); // Updated to use get_result for MySQLi

    if (!$result) {
        output_json('error', 'Ticket not found');
    }

    $attachmentLinks = explode(', ', $result['request_attachment']);
    $folderId = $result['folder_id'];

    $attachments = [];
    foreach ($attachmentLinks as $link) {
        if (!empty($link)) {
            $attachments[] = [
                'webViewLink' => $link,
                'name' => basename(parse_url($link, PHP_URL_PATH))
            ];
        }
    }

    $data = [
        'attachments' => $attachments,
        'folderId' => $folderId
    ];

    output_json('success', 'Attachments fetched successfully', $data);

} catch (Exception $e) {
    error_log('Exception in getAttachments.php: ' . $e->getMessage());
    output_json('error', 'An unexpected error occurred: ' . $e->getMessage());
}
?>
