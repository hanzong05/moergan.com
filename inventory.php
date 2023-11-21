<?php
 session_start();

 if (!isset($_SESSION['id'])) {
   // Redirect to the login page if the user is not logged in
   header('Location: adminlogin.php');
   exit();
 }
// Include the database connection logic
require_once('db_connection.php');

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

<div class="wrapper">
    <div class="sidebar">
        <h2>Sidebar</h2>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a></li>
            <li><a href="analytics.php"><i class="fas fa-user"></i>Analytics</a></li>
            <li><a href="scheduled_apointments.php"><i class="fas fa-address-card"></i>Scheduled Apointments</a></li>
            <li><a href="requested_apointments.php"><i class="fas fa-project-diagram"></i>Requested Apointments</a></li>
            <li><a href="registered_clients.php"><i class="fas fa-blog"></i>Registered Clients</a></li>
            <li><a href="inventory.php"><i class="fas fa-address-book"></i>inventory</a></li>
        </ul> 
        <div class="social_media">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
    <div class="main_content">
        <div class="header">Welcome!! Have a nice day.</div>  
        <div class="info">
            <div class="buttoncontainer">
                <button class="btn btn-primary">Medecine For Cats</button>
                <button class="btn btn-primary">Medecine For Dogs</button>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <!-- Inventory for General Items -->
                    <div class="report-container">
                        <div class="report-body">
                            <div class="report-header">
                                <h4 class="recent-Articles">Inventory</h4>
                            </div>
                            <table class="table table-custom" >
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Image</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Replace the placeholders with your general inventory data -->
                                    <tr>
                                        <td class="product-name">Product 1 (General)</td>
                                        <td><img class="product-image" src="product1.jpg" alt="Product 1"></td>
                                        <td class="quantity">10</td>
                                        <td class="price">$19.99</td>
                                    </tr>
                                    <tr>
                                        <td class="product-name">Product 2 (General)</td>
                                        <td><img class="product-image" src="product2.jpg" alt="Product 2"></td>
                                        <td class="quantity">5</td>
                                        <td class="price">$29.99</td>
                                    </tr>
                                    <!-- Add more rows as needed -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- Inventory for Pets -->
                    <div class="report-container">
                        <div class="report-body">
                            <div class="report-header">
                                <h4 class="recent-Articles">Medecine for Cats</h4>
                            </div>
                            <table class="table table-custom">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <!-- Add more headers as needed for pet inventory -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Replace the placeholders with your pet inventory data -->
                                    <tr>
                                        <td class="product-name">Product 1 (Pet)</td>
                                        <!-- Add more columns as needed for pet inventory -->
                                    </tr>
                                    <tr>
                                        <td class="product-name">Product 2 (Pet)</td>
                                        <!-- Add more columns as needed for pet inventory -->
                                    </tr>
                                    <!-- Add more rows as needed -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         </div>
        </div>

    <!-- Page content -->
   
    <!-- Include Bootstrap and jQuery JavaScript -->
    
    <script src="dashboard.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script> $(document).ready(function () {
            // Toggle sidebar
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
        
document.addEventListener('DOMContentLoaded', function () {
    // Listen for click events on "Edit" buttons
    const editButtons = document.querySelectorAll('.edit-button');
    
    editButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            // Hide clients and pets tables
            document.getElementById('clients').style.display = 'none';
            
            // Show the edit table
            document.getElementById('edit').style.display = 'block';
        });
    });
});</script>

</body>
</html>
