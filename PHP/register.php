<?php
session_start();

// Database credentials
$host = "localhost";
$dbname = "pharmasync";
$username = "root"; // Change if using a different database user
$password = ""; // Set your database password if required

// Create the PDO connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If connection fails, display error message
    $_SESSION['error'] = "Connection failed: " . $e->getMessage();
    header("Location: register.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Hash the password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Insert user into the database
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone_number, username, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $email, $phone_number, $username, $password_hash]);

        // Redirect to login page
        $_SESSION['success'] = "Registration successful! You can now log in.";
        header("Location: login.html");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: register.html");
        exit();
    }
}
?>
