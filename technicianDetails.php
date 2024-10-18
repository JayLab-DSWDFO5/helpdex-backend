<?php
header('Content-Type: application/json');

// Check for authentication token
$headers = getallheaders();
// $authToken = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

// if (!$authToken) {
//     echo json_encode(['error' => 'Unauthorized access']);
//     exit;
// }

// // Verify the token (implement your own token verification logic here)
// if (!verifyToken($authToken)) {
//     echo json_encode(['error' => 'Invalid token']);
//     exit;
// }

// If we get here, the token is valid
$techId = $_GET['tech_id'] ?? null;

if (!$techId) {
    echo json_encode(['error' => 'Tech ID is required']);
    exit;
}

// Include database connection
require_once 'db_connection.php';

// Fetch technician details
$technicianDetails = fetchTechnicianDetails($techId, $conn);

if ($technicianDetails) {
    echo json_encode(['success' => true, 'technician' => $technicianDetails]);
} else {
    echo json_encode(['error' => 'Technician not found']);
}

function verifyToken($token) {
    // Implement your token verification logic here
    // Return true if the token is valid, false otherwise
    // For now, we'll just return true as a placeholder
    return true;
}

function fetchTechnicianDetails($techId, $conn) {
    $query = "SELECT * FROM technician WHERE tech_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $techId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $technician = mysqli_fetch_assoc($result);

    if ($technician) {
        return [
            'tech_id' => $technician['tech_id'],
            'tech_username' => $technician['tech_username'],
            'tech_name' => $technician['tech_name'],
            'group_id' => $technician['group_id'],
            'group_desc' => $technician['group_desc'],
            'tech_designation' => $technician['tech_designation'],
            'tech_priv' => $technician['tech_priv']
        ];
    }

    return null;
}
?>
