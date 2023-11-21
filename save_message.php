<?php
session_start();

// Include the database connection logic
require_once('db_connection.php');

if (isset($_POST['message']) && !empty($_POST['message'])) {
    $message = $_POST['message'];
    $outgoing_msg_id = $_SESSION['user']['unique_id'];
    $incoming_msg_id = 2; // Replace with the actual recipient's unique_id

    // Create a query to insert the message into your messages table
    $query = "INSERT INTO messages (outgoing_msg_id, incoming_msg_id, msg) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $outgoing_msg_id, $incoming_msg_id, $message);

    if ($stmt->execute()) {
        // Message sent and saved successfully
        echo "Message sent and saved.";
    } else {
        // Error occurred while saving the message
        echo "Message not saved. Please try again.";
    }

    $stmt->close();
}
?>
