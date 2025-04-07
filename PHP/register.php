<?php
session_start();

// Database configuration
$host = "localhost";
$dbname = "pharmasync";
$dbuser = "root";
$dbpass = "";

// Create database connection
$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Your passwords did not match!";
        header("Location: register.html");
        exit();
    }

    // Check if email already exists
    $stmt_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt_email->bind_param("s", $email);
    $stmt_email->execute();
    $stmt_email->store_result();

    if ($stmt_email->num_rows > 0) {
        $_SESSION['error'] = "This email is already registered. Please use a different email.";
        header("Location: register.html");
        exit();
    }
    $stmt_email->close();

    // Check if username already exists
    $stmt_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt_username->bind_param("s", $username);
    $stmt_username->execute();
    $stmt_username->store_result();

    if ($stmt_username->num_rows > 0) {
        $_SESSION['error'] = "This username is already taken. Please choose another one.";
        header("Location: register.html");
        exit();
    }
    $stmt_username->close();

    // Check if phone number already exists
    $stmt_phone = $conn->prepare("SELECT id FROM users WHERE phone_number = ?");
    $stmt_phone->bind_param("s", $phone_number);
    $stmt_phone->execute();
    $stmt_phone->store_result();

    if ($stmt_phone->num_rows > 0) {
        // Display modal for duplicate phone number
        echo '
        <html>
        <head>
        <style>
            .modal {
                display: block;
                position: fixed;
                z-index: 5;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                padding-top: 100px;
                font-family: Arial, sans-serif;
            }
            .modal-content {
                background-color: white;
                margin: 5% auto;
                padding: 20px;
                border-radius: 10px;
                width: 60%;
                text-align: center;
            }
            .btn-dashboard {
                padding: 10px 15px;
                border: none;
                cursor: pointer;
                font-size: 20px;
                background-color: #005ab9;
                color: white;
            }
            .btn-dashboard:hover {
                background-color: #03426a;
            }
        </style>
        </head>
        <body>
        <div class="modal">
            <div class="modal-content">
                <h2>This phone number is already registered!</h2>
                <p>Redirecting to login page...</p><br>
                <button onclick="window.location.href=\'http://localhost/pharmasync/login.html\'" class="btn-dashboard">OK</button>
            </div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = "http://localhost/pharmasync/login.html";
            }, 3000);
        </script>
        </body>
        </html>';
        exit();
    }
    $stmt_phone->close();

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone_number, username, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $first_name, $last_name, $email, $phone_number, $username, $password_hash);

    if ($stmt->execute()) {
        // Success modal and redirect
        echo '
        <html>
        <head>
        <style>
            .modal {
                display: block;
                position: fixed;
                z-index: 5;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                padding-top: 100px;
                font-family: Arial, sans-serif;
            }
            .modal-content {
                background-color: white;
                margin: 5% auto;
                padding: 20px;
                border-radius: 10px;
                width: 60%;
                text-align: center;
            }
            .btn-dashboard {
                padding: 10px 15px;
                border: none;
                cursor: pointer;
                font-size: 20px;
                background-color: #005ab9;
                color: white;
            }
            .btn-dashboard:hover {
                background-color: #03426a;
            }
        </style>
        </head>
        <body>
        <div class="modal">
            <div class="modal-content">
                <h2>Your account was successfully created!</h2>
                <p>Redirecting to login page...</p><br>
                <button onclick="window.location.href=\'http://localhost/pharmasync/login.html\'" class="btn-dashboard">OK</button>
            </div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = "http://localhost/pharmasync/login.html";
            }, 3000);
        </script>
        </body>
        </html>';
    } else {
        $_SESSION['error'] = "An error occurred while creating your account.";
        header("Location: register.html");
    }

    $stmt->close();
    $conn->close();
}
?>
