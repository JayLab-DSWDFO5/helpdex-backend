<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    require_once 'db_connection.php';
    
    // Check if the connection is established
    if (!$conn) {
        throw new Exception("Database connection not established");
    }

    // Prepare and execute the query to fetch groups from the working_group table
    $query = "SELECT group_id, group_desc FROM working_group";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception("Query execution failed: " . mysqli_error($conn));
    }

    $groups = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $groups[] = [
            'id' => $row['group_id'],
            'description' => $row['group_desc']
        ];
    }

    output_json('success', 'Groups fetched successfully', $groups);

} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    output_json('error', 'An error occurred while fetching groups.');
}
?>
