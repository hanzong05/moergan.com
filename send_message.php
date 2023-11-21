<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php'); // Redirect to the login page if not logged in
    exit();
}

// Include the database connection details
include_once('db_connection.php');

// Check if the connection was established successfully
if ($conn === false) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    // Retrieve and sanitize the message input (ensure proper validation and sanitation)
    $userId = $_SESSION['user']['Id']; // Replace with your actual user ID retrieval logic
    $incomingId = mysqli_real_escape_string($conn, $_POST['incoming_id']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Insert the message into the database
    $insertMessageQuery = "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertMessageQuery);
    $stmt->bind_param("iis", $incomingId, $userId, $message);

    if ($stmt->execute()) {
        // Message insertion successful
        // You can add a success message or any further actions here
        echo "Message sent successfully!";
    } else {
        // Message insertion failed
        // You can handle errors or display an error message here
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
