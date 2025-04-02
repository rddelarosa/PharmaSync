<?php
$host = "localhost";
$dbname = "pharmasync";
$username = "root"; // Change if using a different database user
$password = ""; // Set your database password if required

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
