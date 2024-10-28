<?php
// Prevent redirects
ini_set('display_errors', 1);
error_reporting(E_ALL);

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db_connect.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method is allowed'
    ]);
    exit();
}

// Get and log raw data
$rawData = file_get_contents("php://input");
error_log("Received data: " . $rawData);

// Decode JSON
$data = json_decode($rawData);

// Check JSON validity
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON: ' . json_last_error_msg(),
        'received' => $rawData
    ]);
    exit();
}

try {
    $query = "INSERT INTO customer_satisfaction_surveys (
        request_id, tech_id, email, first_name, middle_name, last_name,
        gender, age_group, sector, client_type, request_tracker,
        request_name, date_requested, date_resolved, technician_name,
        citizen_charter_awareness, citizen_charter_visibility,
        citizen_charter_helpfulness, promptness_rating, reliability_rating,
        access_rating, courtesy_rating, integrity_rating, assurance_rating,
        outcome_rating, overall_rating, remarks
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            "iissssssssssssssssiiiiiiiis",
            $data->request_id,
            $data->tech_id,
            $data->email,
            $data->first_name,
            $data->middle_name ?? '',
            $data->last_name,
            $data->gender,
            $data->age_group,
            $data->sector,
            $data->client_type,
            $data->request_tracker,
            $data->request_name,
            $data->date_requested,
            $data->date_resolved,
            $data->technician_name,
            $data->citizen_charter_awareness,
            $data->citizen_charter_visibility,
            $data->citizen_charter_helpfulness,
            $data->promptness_rating,
            $data->reliability_rating,
            $data->access_rating,
            $data->courtesy_rating,
            $data->integrity_rating,
            $data->assurance_rating,
            $data->outcome_rating,
            $data->overall_rating,
            $data->remarks ?? ''
        );

        if (mysqli_stmt_execute($stmt)) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Survey submitted successfully',
                'id' => mysqli_insert_id($conn)
            ]);
        } else {
            throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);
    } else {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
} catch (Exception $e) {
    error_log("Error in submit.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'received_data' => $rawData
    ]);
}

mysqli_close($conn);
