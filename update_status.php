<?php
// Include the database connection logic
require_once('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['petId']) && isset($_POST['totalGoodCount']) && isset($_POST['comment'])) {
    // Get the pet ID, total good count, and comment from the POST data
    $petId = $_POST['petId'];
    $totalGoodCount = $_POST['totalGoodCount'];
    $comment = $_POST['comment'];

    // Determine the status based on the total good count
    $status = ($totalGoodCount >= 5) ? 'Good' : 'Bad';

    // Update the status and comment in the database
    $updateStatusQuery = "UPDATE pets SET status = ?, docComment = ? WHERE unique_id = ?";
    $stmt = $conn->prepare($updateStatusQuery);
    $stmt->bind_param("ssi", $status, $comment, $petId);

    if ($stmt->execute()) {
        echo "Status and comment updated successfully.";
    } else {
        echo "Error updating status and comment: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
