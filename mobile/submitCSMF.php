<?php
// Prevent redirects and show errors
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

require_once 'databaseOnMobile.php';

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
    // Extract request ID from request_tracker
    $requestIdParts = explode('-', $data->request_tracker);
    $requestId = intval(end($requestIdParts));

    // Store all values in variables first
    $techId = $data->tech_id;
    $email = $data->client_email ?? '';
    $firstName = $data->client_first_name ?? '';
    $middleName = $data->client_middle_name ?? '';
    $lastName = $data->client_last_name ?? '';
    $gender = $data->gender;
    $ageGroup = $data->age;
    $sector = $data->sector;
    $clientType = $data->client_type;
    $requestTracker = $data->request_tracker;
    $requestName = $data->request_name ?? '';
    $dateRequested = date('Y-m-d', strtotime($data->date_requested));
    $dateResolved = date('Y-m-d', strtotime($data->date_resolved));
    $technicianName = $data->technician;
    $charterAwareness = $data->citizen_charter;
    $charterVisibility = $data->citizen_charter_visible;
    $charterHelpfulness = $data->citizen_charter_helpful;
    $promptnessRating = $data->promptness_rating;
    $reliabilityRating = $data->reliability_rating;
    $accessRating = $data->access_rating;
    $courtesyRating = $data->courtesy_rating;
    $integrityRating = $data->integrity_rating;
    $assuranceRating = $data->assurance_rating;
    $outcomeRating = $data->outcome_rating;
    $overallRating = $data->overall_rating;
    $remarks = $data->remarks ?? '';

    // Insert survey data
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
            $requestId,
            $techId,
            $email,
            $firstName,
            $middleName,
            $lastName,
            $gender,
            $ageGroup,
            $sector,
            $clientType,
            $requestTracker,
            $requestName,
            $dateRequested,
            $dateResolved,
            $technicianName,
            $charterAwareness,
            $charterVisibility,
            $charterHelpfulness,
            $promptnessRating,
            $reliabilityRating,
            $accessRating,
            $courtesyRating,
            $integrityRating,
            $assuranceRating,
            $outcomeRating,
            $overallRating,
            $remarks
        );

        if (mysqli_stmt_execute($stmt)) {
            // After successful survey submission, update the requests table
            $updateQuery = "UPDATE requests SET client_accomplished_csmf = 1 WHERE request_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);

            if ($updateStmt) {
                mysqli_stmt_bind_param($updateStmt, "i", $requestId);

                if (mysqli_stmt_execute($updateStmt)) {
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Survey submitted and request updated successfully',
                        'id' => mysqli_insert_id($conn)
                    ]);
                } else {
                    throw new Exception("Error updating request: " . mysqli_stmt_error($updateStmt));
                }

                mysqli_stmt_close($updateStmt);
            } else {
                throw new Exception("Error preparing update statement: " . mysqli_error($conn));
            }
        } else {
            throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);
    } else {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
} catch (Exception $e) {
    error_log("Error in submitCSMF.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'received_data' => $rawData
    ]);
}

mysqli_close($conn);
