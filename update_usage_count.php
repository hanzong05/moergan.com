<?php
// Include the database connection logic
require_once('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    // Get the appointment ID from the POST data
    $appointmentId = $_POST['id'];

    // Get the appointment time and date for the given ID
    $getAppointmentInfoQuery = "SELECT appointment_time_range, appointment_date FROM scheduled_appointments WHERE id = ?";
    $stmt = $conn->prepare($getAppointmentInfoQuery);
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $stmt->bind_result($appointmentTime, $appointmentDate);
    $stmt->fetch();
    $stmt->close();

    // Count the number of appointments with the same time slot and date
    $countDuplicateAppointmentsQuery = "SELECT COUNT(*) FROM scheduled_appointments WHERE appointment_time_range = ? AND appointment_date = ?";
    $stmt = $conn->prepare($countDuplicateAppointmentsQuery);
    $stmt->bind_param("ss", $appointmentTime, $appointmentDate);
    $stmt->execute();
    $stmt->bind_result($duplicateCount);
    $stmt->fetch();
    $stmt->close();

    echo $duplicateCount; // Send the count as a response
}

// Close the database connection
$conn->close();
?>
