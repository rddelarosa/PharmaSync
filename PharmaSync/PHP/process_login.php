<?php
session_start();  // Start session to handle user login state

// Database configuration
$host = 'localhost'; // Your database host
$db = 'healthconnect'; // Your database name
$user = 'root'; // Your database username
$pass = ''; // Your database password

// Create a database connection
$conn = new mysqli($host, $user, $pass, $db);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error message
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve user inputs
    $identifier = trim($_POST['username']); // This can be username, email, or doctor_id
    $password = trim($_POST['password']); // Password input

    // Check if identifier is a doctor_id (assuming it's numeric)
    if (is_numeric($identifier)) {
        // Check in doctors table for doctor_id
        $sql = "SELECT * FROM doctors WHERE doctor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $identifier);  // 'i' for integer parameter (doctor_id)

        // Execute the query
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if doctor is found
        if ($result->num_rows === 1) {
            $doctor = $result->fetch_assoc();

            // Compare password directly for doctors (no hashing)
            if ($password === $doctor['password']) {
                // Store doctor data in session
                $_SESSION['doctor_id'] = $doctor['doctor_id'];  // Store doctor_id in session
                $_SESSION['doctor_name'] = $doctor['doctor_name'];  // Store doctor's name (if applicable)
                unset($_SESSION['user_id']); // Clear user_id

                // Redirect to the doctor dashboard
                header("Location: doctor_dashboard.html");  // Redirect to your doctor dashboard
                exit();
            } else {
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
                    <h2>Incorrect Password!</h2><br>
                    <button onclick="window.history.back()" class="btn-dashboard">OK</button>
                  </div>
                </div>
          
                <script>
                  // Automatically show the modal on page load
                  window.onload = function() {
                    document.getElementById("logoutModal").style.display = "block";
                  };


                </script>
                ';     
            }
        } else {
            echo "No doctor found with the provided doctor ID.";
        }

        $stmt->close();
    } else {
        // If not a doctor_id, treat it as email or username
        $sql = "SELECT * FROM users WHERE email = ? OR username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $identifier, $identifier);  // 'ss' for two string parameters (email/username)

        // Execute the query
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a matching user is found
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check if the password matches for users (using hashed password comparison)
            if (password_verify($password, $user['password'])) {
                // Store user data in session
                $_SESSION['user_id'] = $user['user_id'];  // Store user ID
                $_SESSION['username'] = $user['username'];  // Store username
                unset($_SESSION['doctor_id']); // Clear doctor_id

                // Redirect to the user dashboard
                header("Location: dashboard.html");  // Redirect to your user dashboard
                exit();
            } else {
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
                    <h2>Incorrect Password!</h2><br>
                    <button onclick="window.history.back()" class="btn-dashboard">OK</button>
                  </div>
                </div>
          
                <script>
                  // Automatically show the modal on page load
                  window.onload = function() {
                    document.getElementById("logoutModal").style.display = "block";
                  };
                </script>
                ';     
            }
        } else {
            echo "<script>
            alert('No user found with the provided username or email.');
            window.location.href = 'login.html';
          </script>";
        }

        $stmt->close();
    }
}

$conn->close();
?>
