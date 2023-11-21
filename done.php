<?php
// Include the database connection file
include("db_connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $appointmentId = $_POST["id"];

    // Get the pet_owner from the requested_appointments table
    $getUserQuery = "SELECT pet_owner FROM scheduled_appointments WHERE id = $appointmentId";
    $userResult = mysqli_query($conn, $getUserQuery);

    if ($userResult) {
        $row = mysqli_fetch_assoc($userResult);
        $user_name = $row['pet_owner']; // Set the pet_owner as the user_name

        // SQL query to insert a notification
        $notificationText = "Hi " . $user_name . " Your appointment has been done.";
        $insertNotificationQuery = "INSERT INTO notification (user_name, notification_text, is_read) 
                                    VALUES (?, ?, 1)";

        // Use prepared statements for the notification query
        $stmt = mysqli_prepare($conn, $insertNotificationQuery);
        mysqli_stmt_bind_param($stmt, 'ss', $user_name, $notificationText); // Bind user_name and notificationText as strings
        
        // Perform the delete and notification operations
        if (mysqli_stmt_execute($stmt)) {
            // SQL query to delete the appointment from requested_appointments
            $deleteQuery = "DELETE FROM scheduled_appointments WHERE id = $appointmentId";
            if (mysqli_query($conn, $deleteQuery)) {
                echo "success";
            } else {
                echo "error deleting appointment";
            }
        } else {
            echo "error inserting notification";
        }

        // Close the prepared statement
        mysqli_stmt_close($stmt);
    } else {
        echo "error fetching user_name";
    }
} else {
    echo "invalid request";
}
?>
