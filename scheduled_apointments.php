<?php
 session_start();
// Check if the user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['position'])) {
    // Redirect to the login page if the user is not logged in
    header('Location: adminlogin.php');
    exit();
}
// Include the database connection logic
require_once('db_connection.php');

// Close the database connection



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
}if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_deworming'])) {
    // Get the deworming data from the form
    $dewormingDate = $_POST['deworming_date'];
    $dewormingMedication = $_POST['deworming_medication'];
    $dewormingDueDate = $_POST['deworming_due_date'];
    $petId = $_POST['pet_id'];
    // Get the pet ID from the form
    // Insert the deworming record into the deworming_records table
    $insertDewormingQuery = "INSERT INTO deworming_records (pet_id, deworming_date, deworming_medication, deworming_due_date) VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($insertDewormingQuery);
    $stmt->bind_param("isss", $petId, $dewormingDate, $dewormingMedication, $dewormingDueDate);

    if ($stmt->execute()) {
       
    } else {
        echo "Error adding deworming record: " . $stmt->error;
    }

    $stmt->close();
    $vaccinationType = $_POST['vaccination_type'];
    $vaccinationDate = $_POST['vaccination_date'];
    $vaccinationDueDate = $_POST['vaccination_due_date'];
    $weight = $_POST['vaccination_weight'];
    $petId = $_POST['pet_id'];

    // Insert the vaccination record into the vaccinations table
    $insertVaccinationQuery = "INSERT INTO vaccinations (pet_id, vaccination_type, vaccination_date, vaccination_due_date, weight) VALUES (?, ?, ?,?, ?)";

    $stmt2 = $conn->prepare($insertVaccinationQuery);
    $stmt2->bind_param("issss", $petId, $vaccinationType, $vaccinationDate, $vaccinationDueDate,$weight);

    if ($stmt2->execute()) {
        header("Location: scheduled_apointments.php");
        exit; 
    } else {
        echo "Error adding vaccination record: " . $stmt2->error;
    }

    $stmt2->close();
}
$conn->close();

?>  <!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-wvmzv6MI7Sk63jQ+15fjgBIEtj0B1F2PLD9j/5lWbCjsYQLxuStQlGQB9qm8b+2v/fVL5smIicTkbr4wDlQvIw==" crossorigin="anonymous" />
  <script src="https://kit.fontawesome.com/b99e675b6e.js"></script>
</head>

<body>
 

  <div class="wrapper">
      <div class="sidebar">
          <h2>Sidebar</h2>
          <ul>
              <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>
</i>Dashboard</a></li>
              <li><a href="analytics.php"><i class="fas fa-chart-bar"></i>
</i>Analytics</a></li>
              <li><a href="scheduled_apointments.php"><i class="far fa-calendar"></i>
</i>Scheduled Appointments</a></li>
              <li><a href="requested_apointments.php"><i class="far fa-calendar"></i>
</i>Requested Appointments</a></li>
              <li><a href="registered_clients.php"> <i class="fas fa-users"></i></i>Registered Clients</a></li>
              <li><a href="out.php"><i class="fas fa-sign-out-alt"></i></i>Logout</a></li>
          </ul>
         
      </div>
      <div class="main_content">
      <div class="header">Welcome!! Have a nice day. <div class="toggle_btn" onclick="toggleSidebar()">☰</div></div>  
      <div class="info">
        <div class="container mt-5">
            <div class="report-container" id="sched">
                <div class="report-header">
                    <h1 class="recent-Articles">Scheduled Appointments</h1>
                </div>
                <div class="report-body">
                    <table class="table table-custom table-responsive" id="scheduled-appointments-table">
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
                                $scheduled_appointments = "SELECT * FROM scheduled_appointments";
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
                                        if ($_SESSION['position'] !== 'staff') {
                                            echo '<td style="display:none">
                                                <button class="accept-button" data-appointment-id="' . $row['id'] . '">&#10003;</button>
                                            </td>';
                                            echo '<td>
                                                <button class="edit-button" id="Edit" data-petid="' . $row['unique_id'] . '" onclick="setPetId(this)">Edit</button>
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
    <div class="report-container" style="display:none;" id="edit">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>Body Inspection</th>
                                    <th colspan="2">Good or Bad</th>
                                </tr>
                                
                                <tr>
                                    <td><strong>1. Eyes -</strong> clear & bright</td>
                                    <td>
                                        <button class="btn-success" onclick="rate('good', this)">Good</button>
                                    </td>
                                    <td>
                                        <button class="btn-danger" onclick="rate('bad', this)">Bad</button>
                                    </td>
                                    <td style="display: none;" id="good-count-1">0</td>
                                </tr>
                                <tr>
                                    <td><strong>2. Ears -</strong> pink & nice smelling</td>
                                    <td>
                                        <button class="btn-success" onclick="rate('good', this)">Good</button>
                                    </td>
                                    <td>
                                        <button class="btn-danger" onclick="rate('bad', this)">Bad</button>
                                    </td>
                                    <td style="display: none;" id="good-count-2">0</td>
                                </tr>
                                <tr>
                                    <td><strong>3. Mouth -</strong> breath, teeth & gums</td>
                                    <td>
                                        <button class="btn-success" onclick="rate('good', this)">Good</button>
                                    </td>
                                    <td>
                                        <button class="btn-danger" onclick="rate('bad', this)">Bad</button>
                                    </td>
                                    <td style="display: none;" id="good-count-3">0</td>
                                </tr>
                                <tr>
                                    <td><strong>4. Nose -</strong> shiny & moist</td>
                                    <td>
                                        <button class="btn-success" onclick="rate('good', this)">Good</button>
                                    </td>
                                    <td>
                                        <button class="btn-danger" onclick="rate('bad', this)">Bad</button>
                                    </td>
                                    <td style="display: none;" id="good-count-4">0</td>
                                </tr>
                                <tr>
                                    <td><strong>5. Coat -</strong> lustrous</td>
                                    <td>
                                        <button class="btn-success" onclick="rate('good', this)">Good</button>
                                    </td>
                                    <td>
                                        <button class="btn-danger" onclick="rate('bad', this)">Bad</button>
                                    </td>
                                    <td style="display: none;" id="good-count-5">0</td>
                                </tr>
                                <tr>
                                    <td><strong>6. Skin -</strong> flea-free, no sore spots</td>
                                    <td>
                                        <button class="btn-success" onclick="rate('good', this)">Good</button>
                                    </td>
                                    <td>
                                        <button class="btn-danger" onclick="rate('bad', this)">Bad</button>
                                    </td>
                                    <td style="display: none;" id="good-count-6">0</td>
                                </tr>
                                <tr>
                                    <td><strong>7. Paws -</strong> check pads & nails</td>
                                    <td>
                                        <button class="btn-success" onclick="rate('good', this)">Good</button>
                                    </td>
                                    <td>
                                        <button class="btn-danger" onclick="rate('bad', this)">Bad</button>
                                    </td>
                                    <td style="display: none;" id="good-count-7">0</td>
                                </tr>
                                <tr>
                                    <td><strong>8. Body -</strong> no lumps & bumps</td>
                                    <td>
                                        <button class="btn-success" onclick="rate('good', this)">Good</button>
                                    </td>
                                    <td>
                                        <button class="btn-danger" onclick="rate('bad', this)">Bad</button>
                                    </td>
                                    <td style="display: none;" id="good-count-8">0</td>
                                </tr>
                                <tr>
                                    <td><strong>9. Energy -</strong> up for anything</td>
                                    <td>
                                        <button class="btn-success" onclick="rate('good', this)">Good</button>
                                    </td>
                                    <td>
                                        <button class="btn-danger" onclick="rate('bad', this)">Bad</button>
                                    </td>
                                    <td style="display: none;" id="good-count-9">0</td>
                                </tr>
                                <tr>
                                    <td><strong>10. Weight -</strong> feel ribcage, appetite</td>
                                    <td>
                                        <button class="btn-success" onclick="rate('good', this)">Good</button>
                                    </td>
                                    <td>
                                        <button class="btn-danger" onclick="rate('bad', this)">Bad</button>
                                    </td>
                                    <td style="display: none;" id="good-count-10">0</td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <strong>Comment:</strong> 
                                        <input type="text" name="comment" id="comment" style="width: 100%; height: 40px;">
                                    </td>
                                </tr>
                            </thead>
                        </table>
                        <p style="display: none;">Total Good Count: <span id="total-good-count">0</span></p>
                    </div>
                    <div class="table-responsive col-md-6">
                        <h1>Deworming</h1>
                        <form method="post" action="scheduled_apointments.php">
                            <div class="form-group">
                                <label for="dewormingMedication">Deworming Medication:</label>
                                <select class="form-control" id="dewormingMedication" name="deworming_medication">
                                    <option selected>Select an option</option>
                                    <option value="4in1">4 in 1 Vaccine</option>
                                        <option value="5in1">5 in 1 Vaccine</option>
                                        <option value="6in1">6 in 1 Vaccine</option>
                                        <option value="8in1">8 in 1 Vaccine</option>
                                        <option value="kennelCough">KENNEL COUGH VACCINE</option>
                                        <option value="rabies">RABBIES VACCINE</option>
                                        <option value="fvrcpCore">FVRCP Core Vaccine</option>
                                        <option value="fvrcpRabies">FVRCP Rabbies</option>
                                        <option value="felvBoosters">FeLV Boosters</option>
                                        <option value="fivBoosters">FIV Boosters</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="dewormingDate">Select a Date</label>
                                    <input type="text" class="form-control" id="dewormingDate" name="deworming_date" placeholder="Choose a date">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="dewormingDueDate">Select Due Date</label>
                                    <input type="text" class="form-control" id="dewormingDueDate" name="deworming_due_date" placeholder="Choose a date">
                                </div>
                            </div>
                            <!-- Add more Deworming form fields here -->
                            <h1>Vaccination</h1>
                            <div class="form-group">
                                <label for="vaccinationType">Vaccination Type:</label>
                                <select class="form-control" id="vaccinationType" name="vaccination_type">
                                    <option selected>Select a vaccination type</option>
                                    <option value="4in1">4 in 1 Vaccine</option>
                                <option value="5in1">5 in 1 Vaccine</option>
                                <option value="6in1">6 in 1 Vaccine</option>
                                <option value="8in1">8 in 1 Vaccine</option>
                                <option value="kennelCough">KENNEL COUGH VACCINE</option>
                                <option value="rabies">RABBIES VACCINE</option>
                                                                <option value="fvrcpCore">FVRCP Core Vaccine</option>
                                <option value="fvrcpRabies">FVRCP Rabbies</option>
                                <option value="felvBoosters">FeLV Boosters</option>
                                <option value="fivBoosters">FIV Boosters</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="vaccinationWeight">Weight (optional):</label>
                                <input type="text" class="form-control" id="vaccinationWeight" name="vaccination_weight" placeholder="Enter weight">
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="vaccinationDate">Select a Date</label>
                                    <input type="text" class="form-control" id="vaccinationDate" name="vaccination_date" placeholder="Choose a date">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="vaccinationDueDate">Select Due Date</label>
                                    <input type="text" class="form-control" id="vaccinationDueDate" name="vaccination_due_date" placeholder="Choose a date">
                                </div>
                            </div>
                            <!-- Add more Vaccination form fields here -->
                            <input type="hidden" id="pet_id_input" name="pet_id" value=""><!-- Leave the value empty initially -->
                            <button type="submit" id="update-status-button" class="btn btn-primary" name="save_deworming">Save</button>
                        </form>
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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script>
    
    
    $(document).ready(function() {
    // Function to handle the click of the accept button
    $(".accept-button").click(function() {
        var appointmentId = $(this).data("appointment-id");
        var buttonElement = $(this);

        $.ajax({
            type: "POST",
            url: "done.php", // Replace with the actual PHP file name
            data: { id: appointmentId },
            success: function(response) {
                if (response === "success") {
                    buttonElement.closest("tr").remove();
                }
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', function () {
    // Listen for click events on "Edit" buttons
    const editButtons = document.querySelectorAll('.edit-button');

    editButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            // Show the edit table
            document.getElementById('edit').style.display = 'block';

            // Get the pet ID from the button's data attribute
            const petId = button.getAttribute('data-petid');

            // Set the input field's value
            document.getElementById('pet_id_input').value = petId;

            // Hide the scheduled appointments table
            document.getElementById('sched').style.display = 'none';
        });
    });
});

    $(document).ready(function () {
            // Initialize the datepickers with the desired date format
            $('#dewormingDate').datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: true,
                autoclose: true,
            });

            $('#dewormingDueDate').datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: true,
                autoclose: true,
            });
        });
        $(document).ready(function () {
    // Initialize the datepickers for vaccination
    $('#vaccinationDate').datepicker({
        format: 'yyyy-mm-dd',
        todayHighlight: true,
        autoclose: true,
    });

    $('#vaccinationDueDate').datepicker({
        format: 'yyyy-mm-dd',
        todayHighlight: true,
        autoclose: true,
    });
});

function setPetId(button) {
    var petId = button.getAttribute('data-petid'); // Get the pet ID from the button's data attribute
    document.getElementById('pet_id_input').value = petId; // Set the input field's value
}
let clickedButtonCount = 0; // Variable to keep track of the clicked buttons

function rate(choice, button) {
    const row = button.closest('tr');
    const goodButton = row.querySelector('.btn-success');
    const badButton = row.querySelector('.btn-danger');

    if (choice === 'good') {
        if (parseInt(goodButton.getAttribute('data-clicked')) === 1) {
            return; // Don't increment beyond 0
        }
        goodButton.innerHTML = '😊'; // Set the button content to a smiling face (you can use any emoji or text)
        badButton.style.display = 'none'; // Hide the "Bad" button
        incrementGoodCount(goodButton);
        updateTotalGoodCount();
    } else if (choice === 'bad') {
        if (parseInt(badButton.getAttribute('data-clicked')) === 1) {
            return; // Don't decrement beyond -1
        }
        badButton.innerHTML = '☹️'; // Set the button content to a sad face (you can use any emoji or text)
        goodButton.style.display = 'none'; // Hide the "Good" button
        incrementBadCount(badButton);
        updateTotalGoodCount();
    }
}

function incrementGoodCount(button) {
    button.setAttribute('data-clicked', '1');
    clickedButtonCount++;
    checkButtonCountAndEnableSave();
}

function incrementBadCount(button) {
    button.setAttribute('data-clicked', '1');
    clickedButtonCount++;
    checkButtonCountAndEnableSave();
}

function updateTotalGoodCount() {
    const goodButtons = document.querySelectorAll('.btn-success[data-clicked="1"]');
    const totalGoodCount = goodButtons.length;

    document.getElementById('total-good-count').textContent = totalGoodCount;
}

$(document).ready(function () {
    // Add an event listener to the "Update Status" button
    $('#update-status-button').on('click', function () {

        if (clickedButtonCount < 10) {
            event.preventDefault(); // Prevent the default form submission
            alert('Please click at least 10 buttons before submitting the form.');
        } else 
         {
            
        // Get the total good count from the span element
        var totalGoodCount = parseInt($('#total-good-count').text());

        // Get the pet ID from your form (you can adapt this part based on your form structure)
        var petId = $('#pet_id_input').val(); // Assuming you have a hidden input field for pet ID

        // Get the comment from the input field
        var comment = $('#comment').val();

        // Make an Ajax request to update the status and comment
        if (!validateForm()) {
                event.preventDefault(); // Prevent the default form submission
                alert('Please make sure all form fields are filled.');
            } else {
                // Form is valid, proceed with the form submission
                $.ajax({
                    type: 'POST',
                    url: 'update_status.php',
                    data: {
                        petId: petId,
                        totalGoodCount: totalGoodCount,
                        comment: comment
                    },
                    success: function (response) {
                        $('.accept-button').eq(0).trigger('click');
                        alert('Status and comment updated successfully.');
                    },
                    error: function () {
                        alert('Error updating status and comment.');
                    }
                });
            }
        }
    });
});
function validateForm() {
    // Implement your form validation logic here
    // For example, check if all required form fields are filled
    const dewormingDate = $('#dewormingDate').val();
    const dewormingDueDate = $('#dewormingDueDate').val();
    const vaccinationType = $('#vaccinationType').val();
    const vaccinationDate = $('#vaccinationDate').val();
    const vaccinationDueDate = $('#vaccinationDueDate').val();

    if (!dewormingDate || !dewormingDueDate || !vaccinationType || !vaccinationDate || !vaccinationDueDate) {
        return false; // Form is not valid
    }

    return true; // Form is valid
}




     </script>

</body>
</html>
