<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "healthconnect";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate and sanitize input data
$first_name = $conn->real_escape_string($data['first-name']);
$last_name = $conn->real_escape_string($data['last-name']);
$birthday = $conn->real_escape_string($data['birthday']);
$emergency_contact = $conn->real_escape_string($data['emergency-contact']);
$username = $conn->real_escape_string($data['username']);
$gender = $conn->real_escape_string($data['gender']);
$email = $conn->real_escape_string($data['email']);
$phone = $conn->real_escape_string($data['phone']);
$contact_method = $conn->real_escape_string($data['contact-method']);

// Prepare and execute the update query
$sql = "UPDATE users SET first_name = ?, last_name = ?, birthday = ?, emergency_contact_name = ?, gender = ?, email = ?, phone_number = ?, communication = ? WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssss", $first_name, $last_name, $birthday, $emergency_contact, $gender, $email, $phone, $contact_method, $username);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Details updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}

// Close connection
$conn->close();
?>
