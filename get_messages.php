<?php
// Include the database connection logic
require_once('db_connection.php');

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    // Handle the case when the user is not logged in, e.g., redirect to the login page
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Get the unique ID of the sessioned user
$senderUniqueId = $_SESSION['user']['unique_id'];

// Assuming you have the recipient's ID
$recipientId = 2; // Replace with the actual recipient's ID

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
