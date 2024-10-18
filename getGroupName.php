<?php
session_start();
require_once 'cloud_db_connection.php'; 
header('Content-Type: application/json');

$workingGroupId = $_SESSION['user']['working_group'] ?? null;

if ($workingGroupId) {
    $query = "SELECT group_desc FROM working_group WHERE group_id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $workingGroupId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Group name retrieved successfully',
                'data' => $result['group_desc']
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No group found for the given ID'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database query preparation failed'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No working group assigned to the user'
    ]);
}

// Error handling for database exceptions
try {
    // Your database operations here
} catch (Exception $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching group name: ' . $e->getMessage()
    ]);
}
