<?php
session_start();  // Start session to handle user login state

// Database configuration
$host = "localhost";
$dbname = "pharmasync";
$dbuser = "root";
$dbpass = "";

// Create a database connection
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error message
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['username']);  // Could be email or username
    $password = trim($_POST['password']);

    // Check users table by email or username
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // If your database stores hashed passwords, use password_verify
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            // Redirect to dashboard
            header("Location: http://localhost/pharmasync/dashboard.html");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No user found with that email or username.";
    }
    $stmt->close();
}

$conn->close();

// Redirect back to login page with error
if (!empty($error)) {
    header("Location: ../login.html?error=" . urlencode($error));
    exit();
}
?>
