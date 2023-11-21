<?php
// Include the database connection logic
require_once('db_connection.php');

// Start the session
session_start();

// Get the unique ID of the sessioned user
$senderUniqueId = 2;

// Assuming you have the recipient's ID
$recipientId = $_POST['uniqueId']; // Replace with the actual recipient's ID

$query = "SELECT msg_id, msg, incoming_msg_id, outgoing_msg_id FROM messages WHERE 
          (incoming_msg_id = ? AND outgoing_msg_id = ?) OR 
          (incoming_msg_id = ? AND outgoing_msg_id = ?) ORDER BY msg_id ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $senderUniqueId, $recipientId, $recipientId, $senderUniqueId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];

while ($row = $result->fetch_assoc()) {
    $msg_id = $row['msg_id'];
    $msg = $row['msg'];
    $incoming_msg_id = $row['incoming_msg_id'];
    $outgoing_msg_id = $row['outgoing_msg_id'];

    // Determine whether the message is received or sent
    $messageClass = ($incoming_msg_id == $recipientId && $outgoing_msg_id == $senderUniqueId) ? 'sent' : 'received';

    $messages[] = ['msg_id' => $msg_id, 'msg' => $msg, 'class' => $messageClass];
}

// Close the database connection
$stmt->close();
$conn->close();

// Set the response header to indicate JSON content
header('Content-Type: application/json');

// Return messages as JSON
echo json_encode($messages);
?>