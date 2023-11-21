<?php
session_start();

// Include the database connection logic
require_once('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the message and the incoming_msg_id from the request
    $message = $_POST['message'];
    $incoming_msg_id = $_POST['uniqueId']; // This is the unique ID of the selected contact

    // Insert the message into the database using incoming_msg_id
    $outgoing_msg_id = 2; // Replace with the admin's actual ID
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