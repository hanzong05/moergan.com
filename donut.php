<?php
require_once('db_connection.php'); // Include your database connection script

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch data from the database and sum the cases bxy year and disease
$sql = "SELECT Disease,Year , SUM(NumberOfCases) as TotalCases FROM catdiseases WHERE Year != '2024' GROUP BY Disease, Year";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Convert the data to a JSON format
    $json_data = json_encode($data);

    // Close the database connection
    $conn->close();

    // Output the JSON data
    echo $json_data;
} else {
    echo "No data found";
}
?>
