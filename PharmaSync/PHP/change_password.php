<?php
// Start the session to access the session data
session_start();

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

// Initialize message variable
$message = "";


// Ensure only the relevant session data is set for each user type
if (isset($_SESSION['doctor_id'])) {
    // If logged in as a doctor, set doctor-related session variables
    $is_doctor = true;
    $user_id = $_SESSION['doctor_id'];
} elseif (isset($_SESSION['user_id'])) {
    // If logged in as a regular user, set user-related session variables
    $is_doctor = false;
    $user_id = $_SESSION['user_id'];
} else {
    die("No valid user session found. Please log in.");
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    // Get the input values
    $current_password = trim($_POST['current_password']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "<p style='color: red;'>Please fill in all fields.</p>";
    } else {
        // Check if the user is a doctor or a regular user
        if ($is_doctor) {
            // Retrieve the doctor's current password from the database (plain text)
            $sql = "SELECT password FROM doctors WHERE doctor_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $doctor = $result->fetch_assoc();

            // Check if the current password matches (no hash needed for doctors)
            if ($doctor && $doctor['password'] === $current_password) {
                // Check if new password and confirmation match
                if ($new_password === $confirm_password) {
                    // Update the password in the database (no hash for doctors)
                    $update_sql = "UPDATE doctors SET password = ? WHERE doctor_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ss", $new_password, $user_id);
                    $update_stmt->execute();

                    if ($update_stmt->affected_rows > 0) {
                        $message = "<p style='color: green;'>Password changed successfully!</p>";
                        // JavaScript to show the popup and then reload the page
                         echo '
                        <style> 
                    
                            @font-face {
                              font-family: "Etna";
                              src: url("Gmeet-PHP/public/GOTHIC.TTF") format("opentype");
                            }
                    
                    
                          .modal {
                            display: none; /* Hidden by default */
                            position: fixed;
                            z-index: 5; /* Sit on top */
                            left: 0;
                            top: 0;
                            width: 100%;
                            height: 100%;
                            background-color: rgba(0, 0, 0, 0.5); /* Black background with transparency */
                            overflow: auto; /* Enable scroll if needed */
                            padding-top: 100px;
                            font-family: Etna, sans-serif;
                    
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
                            padding: 10px 15px; /* Padding inside buttons */
                            border: none; /* Remove default border */
                            cursor: pointer; /* Change cursor on hover */
                            transition: background-color 0.3s; /* Smooth background transition */
                            font-size: 20px; /* Font size for buttons */
                            flex: 1; /* Allow buttons to grow equally */
                            margin: 0 5px; /* Space between buttons */
                            font-family: Etna, sans-serif;
                            background-color: #005ab9; /* Medical blue background */
                            color: rgb(255, 255, 255); /* White text */
                          }
                    
                          .btn-dashboard:hover {
                            background-color: #03426a; /* Darker blue on hover */
                          }
                    
                    
                          .btn-dashboard:hover:before {
                            left: 0;
                          }
                    
                        </style>
                        <!-- Logout Confirmation Modal -->
                        <div id="logoutModal" class="modal">
                          <div class="modal-content">
                            <h2>Password changed successfully!</h2><br>
                                <button onclick=goToLogin() class="btn-dashboard">OK</button>
                          </div>
                        </div>
                    
                        <script>
                          // Automatically show the modal on page load
                          window.onload = function() {
                            document.getElementById("logoutModal").style.display = "block";
                          }
                          
                          function goToLogin() {
                          window.location.href = "doctor_dashboard.html"; // Change to your login page URL
                          }
                    
                          
                        </script>
                        ';     

                    }
                } else {
                    $message = "<p style='color: red;'>New password and confirmation do not match!</p>";
                }
            } else {
                $message = "<p style='color: red;'>Incorrect current password!</p>";
            }
        } else {
            // Retrieve the user's current hashed password from the database (regular user)
            $sql = "SELECT password FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            // Check if the current password matches (using hashing for regular users)
            if ($user && password_verify($current_password, $user['password'])) {
                // Check if new password and confirmation match
                if ($new_password === $confirm_password) {
                    // Hash the new password before storing it
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update the password in the database (hashed for regular users)
                    $update_sql = "UPDATE users SET password = ? WHERE user_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ss", $hashed_password, $user_id);
                    $update_stmt->execute();

                    if ($update_stmt->affected_rows > 0) {
                        echo '
                        <style> 
                    
                            @font-face {
                              font-family: "Etna";
                              src: url("Gmeet-PHP/public/GOTHIC.TTF") format("opentype");
                            }
                    
                    
                          .modal {
                            display: none; /* Hidden by default */
                            position: fixed;
                            z-index: 5; /* Sit on top */
                            left: 0;
                            top: 0;
                            width: 100%;
                            height: 100%;
                            background-color: rgba(0, 0, 0, 0.5); /* Black background with transparency */
                            overflow: auto; /* Enable scroll if needed */
                            padding-top: 100px;
                            font-family: Etna, sans-serif;
                    
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
                            padding: 10px 15px; /* Padding inside buttons */
                            border: none; /* Remove default border */
                            cursor: pointer; /* Change cursor on hover */
                            transition: background-color 0.3s; /* Smooth background transition */
                            font-size: 20px; /* Font size for buttons */
                            flex: 1; /* Allow buttons to grow equally */
                            margin: 0 5px; /* Space between buttons */
                            font-family: Etna, sans-serif;
                            background-color: #005ab9; /* Medical blue background */
                            color: rgb(255, 255, 255); /* White text */
                          }
                    
                          .btn-dashboard:hover {
                            background-color: #03426a; /* Darker blue on hover */
                          }
                    
                    
                          .btn-dashboard:hover:before {
                            left: 0;
                          }
                    
                        </style>
                        <!-- Logout Confirmation Modal -->
                        <div id="logoutModal" class="modal">
                          <div class="modal-content">
                            <h2>Password changed successfully!</h2><br>
                                <button onclick=goToLogin() class="btn-dashboard">OK</button>
                          </div>
                        </div>
                    
                        <script>
                          // Automatically show the modal on page load
                          window.onload = function() {
                            document.getElementById("logoutModal").style.display = "block";
                          }
                          
                          function goToLogin() {
                          window.location.href = "dashboard.html"; // Change to your login page URL
                          }
                    
                          
                        </script>
                        ';     
                    }
                } else {
                    $message = "<p style='color: red;'>New password and confirmation do not match!</p>";
                }
            } else {
                $message = "<p style='color: red;'>Incorrect current password!</p>";
            }
        }
    }
}
$is_doctor = isset($_SESSION['doctor_id']); 
$redirect_url = $is_doctor ? 'doctor_dashboard.html' : 'dashboard.html'; 


$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <style>
        body {
            font-family: 'century Gothic';
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            align-items: center;
        }
        h1 {
            color: #03426a;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="password"] {
            width: 95%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            border-color: #03426a;
        }
        button {
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 20px;
            flex: 1;
            margin: 0 5px;
            font-family: 'century Gothic';
            align-items: center;
            width: 96%;
            background-color: #005ab9;
            color: #fff;
        }
        button:hover {
            background-color: #03426a;
        }
        .message {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Change Password</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div><br>
            <button type="submit" name="change_password">Change Password</button><br><br>
            <button type="button" onclick="window.location.href='<?php echo $redirect_url; ?>'">Cancel</button>
            </form>
        <div class="message">
            <?php echo $message; ?>
        </div>
    </div>
</body>
</html>
