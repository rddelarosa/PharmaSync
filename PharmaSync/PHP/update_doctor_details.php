<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "healthconnect";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate input data
    if (!isset($data['doctor_id'], $data['doctor_name'], $data['specialization'], $data['email'], $data['phone'], $data['hospital_name'])) {
        echo json_encode(["success" => false, "message" => "Incomplete data provided."]);
        exit;
    }

    $doctor_id = $data['doctor_id'];
    $doctor_name = $data['doctor_name'];
    $specialization = $data['specialization'];
    $email = $data['email'];
    $phone = $data['phone'];
    $hospital_name = $data['hospital_name'];

    // Update query
    $sql = "UPDATE doctors 
            SET doctor_name = ?, specialization = ?, email = ?, phone = ? 
            WHERE doctor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $doctor_name, $specialization, $email, $phone, $doctor_id);

    if ($stmt->execute()) {
        // Update hospital name if necessary
        $hospital_sql = "UPDATE hospitals SET hospital_name = ? 
                         WHERE hospital_id = (SELECT hospital_id FROM doctors WHERE doctor_id = ?)";
        $hospital_stmt = $conn->prepare($hospital_sql);
        $hospital_stmt->bind_param("si", $hospital_name, $doctor_id);
        $hospital_stmt->execute();

        echo json_encode(["success" => true, "message" => "Details updated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update details."]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
?>
