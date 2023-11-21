<?php
// Include your database connection code here, or require it from another file
require 'db_connection.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the selected year and disease from the URL parameters
$selectedYear = $_GET['year'];
$selectedDisease = $_GET['disease'];

// Query the database to retrieve the data for the selected year and disease
$sql = "SELECT Year, Disease, Seasonality, Forecast FROM catdiseases WHERE Year = '$selectedYear' AND Disease = '$selectedDisease'";
$result = $conn->query($sql);

$data = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = array(
            "Year" => $row["Year"],
            "Disease" => $row["Disease"],
            "Seasonality" => $row["Seasonality"],
            "Forecast" => $row["Forecast"]
        );
    }
}

// Close the database connection
$conn->close();

// Output the data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
