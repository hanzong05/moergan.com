<?php
session_start();

// Include the database connection logic
require_once('db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['position'])) {
    // Redirect to the login page if the user is not logged in
    header('Location: adminlogin.php');
    exit();
}


// Check if the user is logged in (add your login check logic here)

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accept_appointment'])) {
    // Get the appointment ID from the POST data
    $appointmentId = $_POST['id'];

    // Check if the new appointment time is valid
    $newAppointmentTime = $_POST['new_appointment_time'];
    if (!isValidDateTime($newAppointmentTime)) {
        echo "Invalid appointment time format.";
        exit; // Stop execution if the time is invalid
    }

    // Construct and execute the SQL query to update the appointment_time
    $updateAppointmentTimeQuery = "UPDATE scheduled_appointments SET appointment_time = ? WHERE id = ?";

    // Use prepared statements to update the appointment_time
    $stmt = $conn->prepare($updateAppointmentTimeQuery);
    $stmt->bind_param("si", $newAppointmentTime, $appointmentId);

    if ($stmt->execute()) {
        echo "Appointment time updated successfully.";
    } else {
        echo "Error updating appointment time: " . $stmt->error;
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the appointment ID from the POST data
        $appointmentId = $_POST['id'];
    
        // Fetch the timeslot from the database using the appointment ID
        $query = "SELECT appointment_time_range FROM scheduled_appointments WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $appointmentId);
        $stmt->execute();
        $stmt->bind_result($appointmentTimeRange);
        $stmt->fetch();
        $stmt->close();
    
        if ($appointmentTimeRange) {
            // Count appointments with the same timeslot
            $countQuery = "SELECT COUNT(*) FROM scheduled_appointments WHERE appointment_time_range = ?";
            $countStmt = $conn->prepare($countQuery);
            $countStmt->bind_param("s", $appointmentTimeRange);
            $countStmt->execute();
            $countStmt->bind_result($count);
            $countStmt->fetch();
            $countStmt->close();
    
            echo $count; // Return the count as a response
        } else {
            echo "0"; // Return 0 if the timeslot is not found
        }
    }

    // Close the prepared statement
    $stmt->close();
}

// Function to check if a date is in valid DateTime format
function isValidDateTime($dateTimeString) {
    $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString);
    return $dateTime && $dateTime->format('Y-m-d H:i:s') === $dateTimeString;
}


// Include the database connection file
require_once('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST['Id'];

    // Query to fetch pet data for the selected user
    $query = "SELECT * FROM pet_data_ .$userId WHERE user_id = ?";
    
    // Use prepared statements to fetch the data
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        $petsData = array();

        while ($row = $result->fetch_assoc()) {
            $petsData[] = $row;
        }

        echo json_encode($petsData); // Return the pet data as JSON
    } else {
        echo "error";
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Include custom CSS for styling -->
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<script src="https://kit.fontawesome.com/b99e675b6e.js"></script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<div class="wrapper">
    <div class="sidebar">
        <h2>Sidebar</h2>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a></li>
            <li><a href="analytics.php"><i class="fas fa-user"></i>Analytics</a></li>
            <li><a href="scheduled_apointments.php"><i class="fas fa-address-card"></i>Scheduled Apointments</a></li>
            <li><a href="requested_apointments.php"><i class="fas fa-project-diagram"></i>Requested Apointments</a></li>
            <li><a href="registered_clients.php"><i class="fas fa-blog"></i>Registered Clients</a></li>
            <li><a href="out.php"><i class="fas fa-address-book"></i>Logout</a></li>
        </ul> 
        <div class="social_media">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
    <div class="main_content">
    <div class="header">Welcome!! Have a nice day. <div class="toggle_btn" onclick="toggleSidebar()">☰</div></div>  
        <div class="info">
        <div class="report-container" id="req">
                <div class="report-header">
                    <h1 class="recent-Articles">Requested Appointments</h1>
                </div>
                <div class="report-body">
                    <table class="table table-custom table-responsive">
                        <thead>
                            <tr>
                                <th>Pet Name</th>
                                <th>Pet Owner</th>
                                <th>Pet Type</th>
                                <th>Reason</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
// Include the database connection file
include("db_connection.php");

// Assuming you have a query for scheduled appointments in your db_connection.php file
// Change the query accordingly
$scheduled_appointments = "SELECT * FROM requested_appointments";
$scheduled_appointments_run = mysqli_query($conn, $scheduled_appointments);

if (mysqli_num_rows($scheduled_appointments_run) > 0) {
    while ($row = mysqli_fetch_assoc($scheduled_appointments_run)) {
        echo "<tr>";
        echo "<td>" . $row['pet_name'] . "</td>";
        echo "<td>" . $row['pet_owner'] . "</td>";
        echo "<td>" . $row['pet_type'] . "</td>";
        echo "<td>" . $row['reason'] . "</td>";
        echo "<td>" . $row['appointment_date'] . "</td>";
        echo "<td>" . $row['appointment_time_range'] . "</td>";

        // Check if the user's position is not a doctor before displaying action buttons
        if ($_SESSION['position'] !== 'doctor') {
            echo '<td>
                <button class="accept-button" data-appointment-id="' . $row['id'] . '">&#10003;</button>
                <button class="reject-button" data-appointment-id="' . $row['id'] . '">&#10007;</button>
                </td>';
        } else {
            echo '<td>View Only</td>';
        }

        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>No scheduled appointments found</td></tr>";
}
?>

                        </tbody>
                    </table>
                </div>
            </div>
      </div>
    </div>
</div>
    <script src="dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
    // Function to handle the click of the accept button
    $(".accept-button").click(function() {
        var appointmentId = $(this).data("appointment-id");
        var buttonElement = $(this);

        $.ajax({
            type: "POST",
            url: "get_pet_info.php", // Replace with the actual PHP file name
            data: { id: appointmentId },
            success: function(response) {
                if (response === "success") {
                    buttonElement.closest("tr").remove();
                }
            }
        });
    });

    // Function to handle the click of the reject button
    $(".reject-button").click(function() {
        var appointmentId = $(this).data("appointment-id");
        var buttonElement = $(this);

        $.ajax({
            type: "POST",
            url: "delete_appointment.php",
            data: { id: appointmentId },
            success: function(response) {
                if (response === "success") {
                    buttonElement.closest("tr").remove();
                }
            }
        });
    });
});

// Function to handle the click of the accept button
$(".accept-button").click(function() {
    var appointmentId = $(this).data("appointment-id"); // Get the appointment ID from the button's data attribute
    var buttonElement = $(this); // Store a reference to the button

    // Make an AJAX request to count the duplicated appointments
    $.ajax({
        type: "POST",
        url: "update_usage_count.php", // Correct endpoint for counting duplicated appointments
        data: { id: appointmentId }, // Pass the id as a parameter
        success: function(response) {
            if (response !== "error") {
                // Display the count of duplicated appointments
                console.log("Number of duplicated appointments: " + response);
                // You can use this count for further processing or display it as needed
            } else {
                console.log("Error counting duplicated appointments.");
            }
        }
    });

});
</script>
</body>
</html>
