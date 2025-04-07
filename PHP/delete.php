<?php
session_start();

$host = "localhost";
$dbname = "pharmasync";
$username = "root"; // Change if using a different database user
$password = ""; // Set your database password if required

$conn = new mysqli($servername, $username, $password, $dbname);

$message = "";

// Ensure the user is logged in by checking the session
if (!isset($_SESSION['username'])) {
    die("Please log in first.");
}

$username = $_SESSION['username'];

// Handle delete account
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_account'])) {
    $password = $_POST['password'];

    // Retrieve the user's current password and user_id from the database
    $sql = "SELECT password, user_id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);  // Bind username as a string
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify the current password
    if ($user && password_verify($password, $user['password'])) {
        // Get the user ID
        $user_id = $user['user_id']; // Store user_id

        $delete_appointments_sql = "DELETE FROM appointments WHERE user_id = ?";
        $delete_appointments_stmt = $conn->prepare($delete_appointments_sql);
        $delete_appointments_stmt->bind_param("i", $user_id); 
        $delete_appointments_stmt->execute();

        $delete_allergies_sql = "DELETE FROM allergies WHERE username = ?";
        $delete_allergies_stmt = $conn->prepare($delete_allergies_sql);
        $delete_allergies_stmt->bind_param("s", $username); 
        $delete_allergies_stmt->execute();

        $delete_medications_sql = "DELETE FROM current_medications WHERE username = ?";
        $delete_medications_stmt = $conn->prepare($delete_medications_sql);
        $delete_medications_stmt->bind_param("s", $username);
        $delete_medications_stmt->execute();

        $delete_history_sql = "DELETE FROM medical_history WHERE username = ?";
        $delete_history_stmt = $conn->prepare($delete_history_sql);
        $delete_history_stmt->bind_param("s", $username); 
        $delete_history_stmt->execute();

        $delete_user_sql = "DELETE FROM users WHERE username = ?";
        $delete_user_stmt = $conn->prepare($delete_user_sql);
        $delete_user_stmt->bind_param("s", $username);  
        $delete_user_stmt->execute();

        session_destroy();
        $message = "<p style='color: green;'>Account deleted successfully!</p>";
        echo "<script>
                alert('Account deleted successfully!');
                window.location.href = 'login.html';
              </script>";

    
    } else {
        $message = "<p style='color: red;'>Incorrect password. Please try again!</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account</title>
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
        p {
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
            color: rgb(255, 255, 255); 
        }
        button:hover {
            background-color: #c9302c;
        }
        .message {
            text-align: center;
            margin-top: 15px;
        }
 
        .cancel-btn {
            background-color: #005ab9;
            color: white;
            margin-top: 10px;
        }
        .cancel-btn:hover {
            background-color: #03426a;
        }
    </style>
    <script>
        function confirmDeletion(event) {
            const confirmation = confirm("Are you sure you want to delete your account? This action cannot be undone.");
            if (!confirmation) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body><br><br><br>
    <div class="container">
        <h1>Delete Account</h1>
        <p>Please confirm your password to delete your account.</p>
        <form method="POST" action="" onsubmit="confirmDeletion(event)">
            <div class="form-group">
                <label for="password">Enter Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="delete_account">Delete Account</button>
        </form>
            <button type="submit" onclick="window.location.href='dashboard.html'" class="cancel-btn">Cancel</button>
        <div class="message">
            <?php echo $message; ?>
        </div>
    </div>
</body>
</html>
