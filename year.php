<?php
require_once('db_connection.php'); // Include your database connection script

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$year = isset($_GET['year']) ? $_GET['year'] : date('Y'); // Set the year to the provided value or the current year

// Prepare and execute a SQL query to fetch data for the specified year.
$query = "SELECT Month, Disease, NumberOfCases	 
          FROM catdiseases
          WHERE Year = '$year'
          GROUP BY Month, Disease
          ORDER BY Month";

$result = $conn->query($query);

// Check for query success and fetch data into an array.
if ($result) {
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    // Close the database connection.
    $conn->close();

    // Return the data as JSON.
    header('Content-Type: application/json');
    echo json_encode($data);
} else {
    // Handle the query error.
    echo "Error: " . $query . "<br>" . $conn->error;
}
?>
