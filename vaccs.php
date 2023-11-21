vaccs.php<?php
session_start();

// Include the database connection logic
require_once('db_connection.php');

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['petId'])) {
    $petId = $_GET['petId'];

    // Use prepared statement to fetch vaccination records for the pet
    $vaccinationQuery = "SELECT vaccination_date, vaccination_type, vaccination_due_date FROM vaccinations WHERE pet_id = ?";
    $stmtVaccination = $conn->prepare($vaccinationQuery);
    $stmtVaccination->bind_param("i", $petId);
    $stmtVaccination->execute();
    $vaccinationResult = $stmtVaccination->get_result();

    $vaccinationData = [];

    if ($vaccinationResult->num_rows > 0) {
        // Fetch and return the vaccination data as an array
        while ($row = $vaccinationResult->fetch_assoc()) {
            $vaccinationData[] = $row;
        }
    }

    echo json_encode($vaccinationData);
    exit;
}
?>
