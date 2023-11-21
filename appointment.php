<?php
session_start();

// Include the database connection logic
require_once('db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php'); // Redirect to the login page if not logged in
    exit();
}

$user = $_SESSION['user'];

$query = "SELECT Image FROM users WHERE Id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user['Id']);
$stmt->execute();
$stmt->bind_result($image);
$stmt->fetch();
$stmt->close(); 
$selectedDate = isset($_POST['datepicker']) ? $_POST['datepicker'] : '';

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

    <title>Morgan&Friends</title>

    
</head>

<body style="background-image: url(image/back.jpg); background-repeat: no-repeat; background-size: cover; background-attachment:fixed;">
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="#">Morgan&Friends</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
              aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="home.php" aria-current="page">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="appointment.php">Appointment</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="userprofile.php">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="notification.php">Notification</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="messages.php">Message</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="log.php">Log-out</a>
                </li>
            </ul>
            
                <ul class="navbar-nav flex-row">
                    <li class="nav-item">
                      <a class="nav-link px-2" href="https://www.facebook.com/mfvetclinic">
                        <i class="fab fa-facebook-square"></i>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link px-2" href="#!">
                        <i class="fab fa-instagram"></i>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link ps-2" href="#!">
                        <i class="fab fa-youtube"></i>
                      </a>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link ps-2" href="userprofile.php">
                        <img src="uploads/<?php echo $image; ?>" alt="Profile Image" width="20" height="20" style="border-radius:50%;">
                      </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <br>

    <div id="appointmentForm" class="popup text-center" >
    <?php
                  
                  $petButtonTable = 'petbutton_' . $user['Id'];
                  $appointmentsTable = 'user_appointments_' . $user['Id'];
                  
                 
                  
                  // Define the available time slots
                  $timeSlots = [
                      '09:00 AM - 09:30 AM',
                      '10:00 AM - 10:30 AM',
                      '11:00 AM - 11:30 AM',
                      '11:30 AM - 12:00 PM',
                      '01:00 PM - 01:30 PM',
                      '01:30 PM - 02:00 PM',
                      '02:00 PM - 02:30 PM',
                      '02:30 PM - 03:00 PM',
                      '03:30 PM - 04:00 PM'
                  ];
                  $disabledTimeSlots = [];
                  
                  
                  if (isset($_POST['appointment_registration'])) {
                      $petSelect = $_POST['petSelect'];
                      $textInput = $_POST['textInput'];
                      $selectedDate = $_POST['datepicker'];
                      $selectedTimeSlot = $_POST['selectedTimeSlot'];

                  
                      // Check if the selected time slot has been used 3 times or more
                      $query = "SELECT COUNT(*) FROM scheduled_appointments WHERE appointment_datetime = ? ";
                      $stmt = $conn->prepare($query);
                      $appointmentDatetime = $selectedDate . " " . $selectedTimeSlot;
                      $stmt->bind_param("s", $appointmentDatetime);
                      $stmt->execute();
                      $stmt->bind_result($usedCount);
                      $stmt->fetch();
                      $stmt->close();

                      $query = "SELECT species, unique_id FROM pets WHERE pet_name = ?";
                      $stmt = $conn->prepare($query);
                      $stmt->bind_param("s", $petSelect);
                      $stmt->execute();
                      $stmt->bind_result($petSpecies, $unique_id);
                      $stmt->fetch();
                      $stmt->close();
                      
                      
                  
                      if ($usedCount >= 3) {
                        // This time slot has been used 3 times or more, handle the error or feedback to the user.
                        echo '<div class="alert alert-danger" style="z-index: 10000; transition: opacity 0.5s;" id="noSlotError">
    <button type="button"style="width: 0;"class="close" data-dismiss="alert"  aria-label="Close">
        <span aria-hidden="true" style="color: black;" >&times;</span>
    </button>
    <strong>Oops!</strong>  No slot available at this time. Please select another option.
</div>';
                    }
                     else {
                          $concatenatedDatetime = $selectedDate . " " . $selectedTimeSlot;
                  
                  $query = "INSERT INTO requested_appointments (unique_id,pet_owner, owner_id, pet_name,pet_type, reason, appointment_date, appointment_time_range, appointment_datetime) VALUES (?,?, ?, ?, ?, ?, ?,?,?)";
                  $stmt = $conn->prepare($query);
                  $stmt->bind_param("ssissssss",$unique_id, $user['Firstname'], $user['Id'], $petSelect,$petSpecies, $textInput, $selectedDate, $selectedTimeSlot, $concatenatedDatetime);
                  $stmt->execute();
                  
                          // Appointment successfully registered
                          echo '  <div class="alert alert-success">
                            <button type="button"  style="width: 0;"class="close" data-dismiss="alert"  aria-label="Close">
        <span aria-hidden="true" style="color: black;" >&times;</span>
    </button>
    <strong>Success!</strong> Succesfully requested an Appointment. Please wait for response!
  </div>';
                      }

                      
                  }
                  
                  
                  ?>
        <div class="container">
            
            <h2>Appointment Form</h2>
            <form class="form-container" method="POST">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="petSelect">Select Pet:</label>
                            <select class="form-control" id="petSelect" name="petSelect" required>
                            <?php
                            // Query the database to fetch the pet names of the logged-in user
                            $userId = $user['Id']; // Get the user's ID
                            $pettable = 'pet_data_' . $userId;
    
                            // Construct and execute the SQL query to fetch pet names
                            $query = "SELECT pet_name FROM $pettable";
                            $result = $conn->query($query);
    
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $petName = $row['pet_name'];
                                    echo "<option value='$petName'>$petName</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No pets found</option>";
                            }
                        ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="textInput">Reason for appointment:</label>
                            <input type="text" class="form-control" id="textInput" name="textInput" required>
                        </div>
                    </div>
                </div>
                <!-- Add fields for appointment data -->
                <input type="hidden" name="appointment_registration" value="register_appointment">
                <div class="form-group">
                    <label for="datepicker">Select Date:</label>
                   <!-- Modify the date input field to include the onchange attribute -->
<input type="date" class="form-control" id="datepicker" name="datepicker" required onchange="setMinDate()">

    
                </div>
                <input type="text" id="selectedTimeSlot" name="selectedTimeSlot" value="">
                <div class="form-group" id="timeSlotButtons">
                    <label>Select Time Slot:</label>
                    <!-- Add buttons for time slots here -->
                    
                    <?php
                    
      foreach ($timeSlots as $timeSlot) {
        $isDisabled = in_array($timeSlot, $disabledTimeSlots) ? 'disabled' : '';
        echo '<button type="button" class="btn btn-secondary time-slot-button" ' . $isDisabled . ' onclick="selectTimeSlot(\'' . $timeSlot . '\')">' . $timeSlot . '</button>';
    }
        ?>
                   </div>
      
                        <div>
                            <button type="submit" class="submit-button">Submit</button>
                <button type="button" class="cancel-button" onclick="cancelAppointment()">Cancel</button>
                        </div>
            </form>
        </div>
    </div>
    
                    
    <script src="index.js"></script>
    <script>
         function selectTimeSlot(timeSlot) {
    // Update the hidden input field with the selected time slot
    document.getElementById('selectedTimeSlot').value = timeSlot;
    
    // Remove the "active" class from all buttons
    const buttons = document.querySelectorAll('.time-slot-button');
    buttons.forEach(button => {
        button.classList.remove('active');
    });

    // Add the "active" class to the clicked button
    const clickedButton = event.target;
    clickedButton.classList.add('active');
    hideErrorMessage();
}


function cancelAppointment() {
    // Clear the selected time slot when canceling
    document.getElementById('selectedTimeSlot').value = '';
    hideErrorMessage();
}

function hideErrorMessage() {
    // Hide the error message
    document.getElementById('noSlotError').style.display = 'none';
}
function setMinDate() {
        var tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);

        var tomorrowString = tomorrow.toISOString().split('T')[0];
        document.getElementById('datepicker').min = tomorrowString;
    }

    // Call the setMinDate function when the page is loaded
    window.onload = setMinDate;
  
    </script>
    </body>
    </html>
    