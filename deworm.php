<?php
session_start();

// Include the database connection logic
require_once('db_connection.php');

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['petId'])) {
    $petId = $_GET['petId'];

    // Use prepared statement to fetch deworming records for the pet
    $dewormingQuery = "SELECT deworming_date, deworming_medication, deworming_due_ate FROM deworming_records WHERE pet_id = ?";
    $stmtDeworming = $conn->prepare($dewormingQuery);
    $stmtDeworming->bind_param("i", $petId);
    $stmtDeworming->execute();
    $dewormingResult = $stmtDeworming->get_result();

    $dewormingData = [];

    if ($dewormingResult->num_rows > 0) {
        // Fetch and return the deworming data as an array
        while ($row = $dewormingResult->fetch_assoc()) {
            $dewormingData[] = $row;
        }
    }

    echo json_encode($dewormingData);
    exit;
}
?>
