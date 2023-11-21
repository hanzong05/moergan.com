<?php
// Database configuration
$host = 'localhost'; // Database host (e.g., localhost)
$username = 'root'; // Your database username
$password = ''; // Your database password
$database = 'project'; // Your database name

// Create a database connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>