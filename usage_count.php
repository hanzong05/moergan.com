<?php
// Include the database connection logic
require_once('db_connection.php');

// Your database connection code should be here.

// SQL query to update the usage_count
$updateUsageCountQuery = "
    UPDATE scheduled_appointments AS a
    JOIN (
        SELECT appointment_date, appointment_time_range, COUNT(*) AS count_usage
        FROM scheduled_appointments
        WHERE appointment_date IS NOT NULL
        GROUP BY appointment_date, appointment_time_range
        HAVING COUNT(*) > 1
    ) AS b
    ON a.appointment_date = b.appointment_date
    AND a.appointment_time_range = b.appointment_time_range
    SET a.usage_count = b.count_usage;
";

// Execute the query
if ($conn->query($updateUsageCountQuery) === TRUE) {
    echo "Usage count updated successfully.";
} else {
    echo "Error updating usage count: " . $conn->error;
}

// Close the database connection
$conn->close();
?>
