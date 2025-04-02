<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "healthconnect";

$conn = new mysqli($servername, $username, $password, $dbname);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['doctor_id'])) {
    echo json_encode(["error" => "Doctor ID not found in session. Please log in first."]);
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

$doctor_sql = "SELECT doctor_name, specialization, doctor_id, email, phone, hospital_id FROM doctors WHERE doctor_id = ?";
$doctor_stmt = $conn->prepare($doctor_sql);
$doctor_stmt->bind_param("i", $doctor_id);
$doctor_stmt->execute();
$doctor_result = $doctor_stmt->get_result();
$doctor = $doctor_result->fetch_assoc();

if (!$doctor) {
    echo json_encode(["error" => "Doctor not found."]);
    exit;
}

$hospital_id = $doctor['hospital_id'];

$hospital_sql = "SELECT hospital_name FROM hospitals WHERE hospital_id = ?";
$hospital_stmt = $conn->prepare($hospital_sql);
$hospital_stmt->bind_param("i", $hospital_id);
$hospital_stmt->execute();
$hospital_result = $hospital_stmt->get_result();
$hospital = $hospital_result->fetch_assoc();

$appointment_sql = "SELECT appointment_date, appointment_time, user_id, chief_complaint, diagnostic_lab, gmeet_link FROM appointments WHERE doctor_id = ?";
$appointment_stmt = $conn->prepare($appointment_sql);
$appointment_stmt->bind_param("i", $doctor_id);
$appointment_stmt->execute();
$appointments_result = $appointment_stmt->get_result();

$appointments = [];
while ($appointment = $appointments_result->fetch_assoc()) {
    $patient_sql = "SELECT first_name, last_name FROM users WHERE user_id = ?";
    $patient_stmt = $conn->prepare($patient_sql);
    $patient_stmt->bind_param("i", $appointment['user_id']);
    $patient_stmt->execute();
    $patient_result = $patient_stmt->get_result();
    $patient = $patient_result->fetch_assoc();

    $appointments[] = [
        'appointment_date' => $appointment['appointment_date'],
        'appointment_time' => $appointment['appointment_time'],
        'user_id' => $patient ? $patient['first_name'] . ' ' . $patient['last_name'] : 'Unknown',
        'chief_complaint' => $appointment['chief_complaint'],
        'diagnostic_lab' => $appointment['diagnostic_lab'],
        'gmeet_link' => $appointment['gmeet_link']
    ];
}

$schedule_sql = "SELECT date, time FROM doctor_schedule WHERE doctor_id = ? AND available = 1";
$schedule_stmt = $conn->prepare($schedule_sql);
$schedule_stmt->bind_param("i", $doctor_id);
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();

$schedules = [];
while ($schedule = $schedule_result->fetch_assoc()) {
    $schedules[] = [
        'date' => $schedule['date'],
        'time' => $schedule['time']
    ];
}

$response = [
    "doctor_name" => $doctor['doctor_name'],
    "specialization" => $doctor['specialization'],
    "doctor_id" => $doctor['doctor_id'],
    "email" => $doctor['email'],
    "phone" => $doctor['phone'],
    "hospital_name" => $hospital ? $hospital['hospital_name'] : "Hospital not found",
    "appointments" => $appointments,
    "schedules" => $schedules
];

echo json_encode($response);

$conn->close();
?>
