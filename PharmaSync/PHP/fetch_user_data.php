<?php
session_start(); 

if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    echo json_encode(['error' => 'User not logged in or user_id is missing']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "healthconnect";

$conn = new mysqli($servername, $username, $password, $dbname);


$userIdentifier = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['username'];

$sqlUser = "SELECT user_id, first_name, last_name, birthday, gender, email, username, phone_number, emergency_contact_name, communication 
            FROM users 
            WHERE user_id = ? OR username = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param('ss', $userIdentifier, $userIdentifier); 
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

$response = [];

// Fetch user details
if ($resultUser->num_rows === 1) {
    $user = $resultUser->fetch_assoc();
    $response['user'] = [
        'user_id' => $user['user_id'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'birthday' => $user['birthday'],
        'gender' => $user['gender'],
        'email' => $user['email'],
        'username' => $user['username'],
        'phone_number' => $user['phone_number'],
        'emergency_contact_name' => $user['emergency_contact_name'],
        'communication' => $user['communication'],
    ];
} else {
    echo json_encode(['error' => 'User not found']);
    exit;
}
$stmtUser->close();

// Fetch appointments for the user
$sqlAppointments = "SELECT appointment_date, appointment_time, doctor_id, chief_complaint, diagnostic_lab, gmeet_link
                    FROM appointments 
                    WHERE user_id = ?";
$stmtAppointments = $conn->prepare($sqlAppointments);
$stmtAppointments->bind_param('s', $user['user_id']);
$stmtAppointments->execute();
$resultAppointments = $stmtAppointments->get_result();

$appointments = [];
while ($appointment = $resultAppointments->fetch_assoc()) {
    // Fetch doctor name for each appointment
    $sqlDoctor = "SELECT doctor_name FROM doctors WHERE doctor_id = ?";
    $stmtDoctor = $conn->prepare($sqlDoctor);
    $stmtDoctor->bind_param('s', $appointment['doctor_id']);
    $stmtDoctor->execute();
    $resultDoctor = $stmtDoctor->get_result();
    $doctorName = $resultDoctor->num_rows === 1 ? $resultDoctor->fetch_assoc()['doctor_name'] : '';

    $appointments[] = [
        'appointment_date' => $appointment['appointment_date'],
        'appointment_time' => $appointment['appointment_time'],
        'doctor_id' => $appointment['doctor_id'],
        'doctor_name' => $doctorName,
        'chief_complaint' => $appointment['chief_complaint'],
        'diagnostic_lab' => $appointment['diagnostic_lab'],
        'gmeet_link' => $appointment['gmeet_link'],
    ];
    $stmtDoctor->close();
}
$stmtAppointments->close();
$response['appointments'] = $appointments;

// Fetch medical details, allergies, and medications
$details = [];

$sqlMedicalHistory = "SELECT medical_history, medical_details FROM medical_history WHERE username = ?";
$stmtMedicalHistory = $conn->prepare($sqlMedicalHistory);
$stmtMedicalHistory->bind_param('s', $response['user']['username']);
$stmtMedicalHistory->execute();
$resultMedicalHistory = $stmtMedicalHistory->get_result();

$medicalHistory = [];
while ($row = $resultMedicalHistory->fetch_assoc()) {
    $medicalHistory[] = $row;
}
$stmtMedicalHistory->close();

$sqlAllergies = "SELECT allergy, allergy_details FROM allergies WHERE username = ?";
$stmtAllergies = $conn->prepare($sqlAllergies);
$stmtAllergies->bind_param('s', $response['user']['username']);
$stmtAllergies->execute();
$resultAllergies = $stmtAllergies->get_result();

$allergies = [];
while ($row = $resultAllergies->fetch_assoc()) {
    $allergies[] = $row;
}
$stmtAllergies->close();

$sqlMedications = "SELECT medication, medication_details FROM current_medications WHERE username = ?";
$stmtMedications = $conn->prepare($sqlMedications);
$stmtMedications->bind_param('s', $response['user']['username']);
$stmtMedications->execute();
$resultMedications = $stmtMedications->get_result();

$medications = [];
while ($row = $resultMedications->fetch_assoc()) {
    $medications[] = $row;
}
$stmtMedications->close();

$details['medical_history'] = $medicalHistory;
$details['allergies'] = $allergies;
$details['medications'] = $medications;
$response['details'] = $details;

$conn->close();

echo json_encode($response);
?>
