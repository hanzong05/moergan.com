<?php
// Include the database connection file
include("db_connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $appointmentId = $_POST["id"];

    // Get the pet_owner from the requested_appointments table
    $getUserQuery = "SELECT pet_owner,owner_id FROM requested_appointments WHERE id = $appointmentId";
    $userResult = mysqli_query($conn, $getUserQuery);

    if ($userResult) {
        $row = mysqli_fetch_assoc($userResult);
        $user_name = $row['pet_owner']; // Set the pet_owner as the user_name

        // SQL query to insert the appointment into scheduled_appointments and set is_read to 1
        $insertScheduledQuery = "INSERT INTO scheduled_appointments (unique_id,pet_name, pet_owner, pet_type, reason, appointment_date, appointment_time_range,appointment_datetime,owner_id) 
                                SELECT unique_id, pet_name, pet_owner, pet_type, reason, appointment_date, appointment_time_range,appointment_datetime,owner_id
                                FROM requested_appointments 
                                WHERE id = $appointmentId";

        // SQL query to insert a notification
        $notificationText = "Hi " . $user_name . " Your appointment has been Accepted.";
        $userid = $row['owner_id'];
        $insertNotificationQuery = "INSERT INTO notification (user_name, notification_text, is_read,user_id) 
                                    VALUES (?, ?, 1,?)";

        // Use prepared statements for the notification query
        $stmt = mysqli_prepare($conn, $insertNotificationQuery);
        mysqli_stmt_bind_param($stmt, 'ssi', $user_name, $notificationText,$userid); // Bind user_name and notificationText as strings
        
        // Perform the insert and delete operations
        if (mysqli_query($conn, $insertScheduledQuery) && mysqli_stmt_execute($stmt)) {
            // SQL query to delete the appointment from requested_appointments
            $deleteQuery = "DELETE FROM requested_appointments WHERE id = $appointmentId";
            if (mysqli_query($conn, $deleteQuery)) {
                echo "success";
            } else {
                echo "error deleting appointment";
            }
        } else {
            echo "error inserting appointment or notification";
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
