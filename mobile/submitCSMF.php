<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

require_once '../db_connect.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method is allowed'
    ]);
    exit();
}

// Get posted data
$data = json_decode(file_get_contents("php://input"));

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
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);
