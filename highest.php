<?php
require_once('db_connection.php'); // Include your database connection script

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch data with the highest seasonality for each disease and month
$sql = "
SELECT
    c.Disease,
    c.Year,
    c.Month,
    c.Seasonality AS TotalSeasonality
FROM catdiseases c
JOIN (
    SELECT Disease, Month, MAX(Seasonality) AS MaxSeasonality
    FROM catdiseases
    GROUP BY Disease, Month
) max_seasonality
ON c.Disease = max_seasonality.Disease
AND c.Month = max_seasonality.Month
AND c.Seasonality = max_seasonality.MaxSeasonality
";

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
