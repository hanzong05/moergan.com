<?php
// Include the database connection logic
require_once('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get the "unique_id" of the selected contact from the GET request
    $uniqueId = $_GET['uniqueId'];

    // Define an array to store the messages for the selected contact
    $contactMessages = array();

    // Fetch messages from the database for the selected contact
    $sql = "SELECT msg FROM messages WHERE (outgoing_msg_id = $userId AND incoming_msg_id = $uniqueId) OR (outgoing_msg_id = $uniqueId AND incoming_msg_id = $userId) ORDER BY msg_id";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $contactMessages[] = $row['msg'];
        }
    }

    // Return the messages as JSON
    echo json_encode($contactMessages);
}
?>
