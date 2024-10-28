<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

require_once '../db_connect.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Method not allowed: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method is allowed'
    ]);
    exit();
}

// Get posted data
$rawData = file_get_contents("php://input");
error_log("Raw request data: " . $rawData);

$data = json_decode($rawData);

// Check if JSON is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON decode error: " . json_last_error_msg());
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data: ' . json_last_error_msg()
    ]);
    exit();
}

try {
    // Log the decoded data
    error_log("Decoded data: " . print_r($data, true));

    $query = "INSERT INTO customer_satisfaction_surveys (
        request_id, tech_id, email, first_name, middle_name, last_name,
        gender, age_group, sector, client_type, request_tracker,
        request_name, date_requested, date_resolved, technician_name,
        citizen_charter_awareness, citizen_charter_visibility,
        citizen_charter_helpfulness, promptness_rating, reliability_rating,
        access_rating, courtesy_rating, integrity_rating, assurance_rating,
        outcome_rating, overall_rating, remarks
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    error_log("Preparing query: " . $query);

    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // Log all values being bound
        error_log("Binding parameters with values: " . print_r([
            'request_id' => $data->request_id,
            'tech_id' => $data->tech_id,
            'email' => $data->email,
            'first_name' => $data->first_name,
            'middle_name' => $data->middle_name ?? '',
            'last_name' => $data->last_name,
            'gender' => $data->gender,
            'age_group' => $data->age_group,
            'sector' => $data->sector,
            'client_type' => $data->client_type,
            'request_tracker' => $data->request_tracker,
            'request_name' => $data->request_name,
            'date_requested' => $data->date_requested,
            'date_resolved' => $data->date_resolved,
            'technician_name' => $data->technician_name,
            'citizen_charter_awareness' => $data->citizen_charter_awareness,
            'citizen_charter_visibility' => $data->citizen_charter_visibility,
            'citizen_charter_helpfulness' => $data->citizen_charter_helpfulness,
            'promptness_rating' => $data->promptness_rating,
            'reliability_rating' => $data->reliability_rating,
            'access_rating' => $data->access_rating,
            'courtesy_rating' => $data->courtesy_rating,
            'integrity_rating' => $data->integrity_rating,
            'assurance_rating' => $data->assurance_rating,
            'outcome_rating' => $data->outcome_rating,
            'overall_rating' => $data->overall_rating,
            'remarks' => $data->remarks ?? ''
        ], true));

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
            $insert_id = mysqli_insert_id($conn);
            error_log("Survey inserted successfully with ID: " . $insert_id);
            echo json_encode([
                'success' => true,
                'message' => 'Survey submitted successfully',
                'id' => $insert_id
            ]);
        } else {
            $error = mysqli_stmt_error($stmt);
            error_log("Error executing statement: " . $error);
            throw new Exception("Error executing statement: " . $error);
        }

        mysqli_stmt_close($stmt);
    } else {
        $error = mysqli_error($conn);
        error_log("Error preparing statement: " . $error);
        throw new Exception("Error preparing statement: " . $error);
    }
} catch (Exception $e) {
    error_log("Exception caught: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

mysqli_close($conn);
