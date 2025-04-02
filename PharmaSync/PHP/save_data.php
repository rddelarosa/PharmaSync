<?php
// Start session
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Update with your DB username
$password = ""; // Update with your DB password
$dbname = "healthconnect"; // Update with your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve and decode POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = isset($_POST['table']) ? $_POST['table'] : null;
    $data = isset($_POST['data']) ? json_decode($_POST['data'], true) : null;

    // Use session username or a default value
    $username  = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['username'];


    if ($table && $data) {
        // Clear the existing data for the table
        $deleteQuery = "DELETE FROM $table WHERE username = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();

        // Insert new rows
        $insertQuery = "";
        switch ($table) {
            case 'medical_history':
                $insertQuery = "INSERT INTO medical_history (username, medical_history, medical_details) VALUES (?, ?, ?)";
                break;
            case 'current_medications':
                $insertQuery = "INSERT INTO current_medications (username, medication, medication_details) VALUES (?, ?, ?)";
                break;
            case 'allergies':
                $insertQuery = "INSERT INTO allergies (username, allergy, allergy_details) VALUES (?, ?, ?)";
                break;
            default:
                echo "Invalid table";
                exit;
        }

        $stmt = $conn->prepare($insertQuery);
        foreach ($data as $row) {
            if ($table == 'medical_history') {
                $stmt->bind_param("sss", $username, $row['condition'], $row['details']);
            } elseif ($table == 'current_medications') {
                $stmt->bind_param("sss", $username, $row['medication'], $row['details']);
            } elseif ($table == 'allergies') {
                $stmt->bind_param("sss", $username, $row['allergy'], $row['details']);
            }
            $stmt->execute();
        }

        echo "Data saved successfully.";
    } else {
        echo "Invalid input.";
    }
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
