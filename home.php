<?php
session_start();

// Include the database connection logic
require_once('db_connection.php');

// Add these lines for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php'); // Redirect to the login page if not logged in
    exit();
}

$user = $_SESSION['user'];
$petButtonTable = 'petbutton_' . $user['Id'];
$appointmentsTable = 'user_appointments_' . $user['Id'];

$pettable = 'pet_data_' . $user['Id'];

$query = "SELECT Image FROM users WHERE Id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user['Id']);
$stmt->execute();
$stmt->bind_result($image);
$stmt->fetch();
$stmt->close();

// Query to fetch buttons from the petbutton table
$query = "SELECT button_name FROM $petButtonTable";
$result = $conn->query($query);

$buttons = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $buttons[] = $row['button_name'];
    }
}

$petNames = [];
foreach ($buttons as $buttonName) {
    $petId = substr($buttonName, strrpos($buttonName, '_') + 1);
    $queryPetName = "SELECT pet_name FROM $pettable WHERE unique_id = ?";
    $stmtPetName = $conn->prepare($queryPetName);
    $stmtPetName->bind_param("s", $petId);
    $stmtPetName->execute();
    $stmtPetName->bind_result($petName);
    $stmtPetName->fetch();
    $stmtPetName->close();

    $petNames[$buttonName] = $petName;
}

if (isset($_GET['buttonName'])) {
    $buttonName = $_GET['buttonName'];

    // Construct the pet table name (replace with your actual naming convention)
    $buttonParts = explode('_', $buttonName);
    if (count($buttonParts) != 3) {
        $response = ["error" => "Invalid button name"];
    } else {
        $petId = intval($buttonParts[2]);

        $pettable = 'pet_data_' . $_SESSION['user']['Id'];

        $queryPet = "SELECT * FROM $pettable WHERE unique_id = ?";
        $stmtPet = $conn->prepare($queryPet);
        $stmtPet->bind_param("i", $petId);
        $stmtPet->execute();
        $resultPet = $stmtPet->get_result();

        $queryStatus = "SELECT status, docComment FROM pets WHERE unique_id = ?";
        $stmtStatus = $conn->prepare($queryStatus);
        $stmtStatus->bind_param("i", $petId);
        $stmtStatus->execute();
        $resultStatus = $stmtStatus->get_result();
        
        if ($resultStatus->num_rows > 0) {
            $rowStatus = $resultStatus->fetch_assoc();
            $status = $rowStatus['status'];
            $docComment = $rowStatus['docComment'];
        } else {
            // Status not found or null, set a default value
            $status = "Status not available";
            $docComment = "Comment not available";
        }

        // Initialize arrays for vaccinations and deworming records
        $vaccinations = [];
        $dewormingRecords = [];
        $petInfo = [];

        // Check if pet information is available
        if ($resultPet->num_rows == 1) {
            $petInfo = $resultPet->fetch_assoc();

            // Fetch vaccination information
            $queryVaccinations = "SELECT * FROM vaccinations WHERE pet_id = ? ORDER BY created_at DESC";
            $stmtVaccinations = $conn->prepare($queryVaccinations);
            $stmtVaccinations->bind_param("i", $petId);
            $stmtVaccinations->execute();
            $resultVaccinations = $stmtVaccinations->get_result();

            // Fetch deworming records
            $queryDeworming = "SELECT * FROM deworming_records WHERE pet_id = ? ORDER BY created_at DESC";
            $stmtDeworming = $conn->prepare($queryDeworming);
            $stmtDeworming->bind_param("i", $petId);
            $stmtDeworming->execute();
            $resultDeworming = $stmtDeworming->get_result();

            // Check if vaccinations are available
            if ($resultVaccinations->num_rows > 0) {
                while ($row = $resultVaccinations->fetch_assoc()) {
                    $vaccinations[] = $row;
                }
            }

            // Check if deworming records are available
            if ($resultDeworming->num_rows > 0) {
                while ($row = $resultDeworming->fetch_assoc()) {
                    $dewormingRecords[] = $row;
                }
            }
        } else {
            // Pet information not found
            $response = ["error" => "Pet not found"];
        }

        // Combine pet, vaccination, and deworming data into a single array
        $petInfo['vaccinations'] = $vaccinations;
        $petInfo['deworming_records'] = $dewormingRecords;
        $petInfo['status'] = $status;
        $petInfo['docComment'] = $docComment; // Add status to the $petInfo array

        // Check if pet information exists
        if (!empty($petInfo)) {
            // Make sure that there is no unexpected output before sending JSON
            ob_clean(); // Clear output buffer
            header('Content-Type: application/json');
            echo json_encode($petInfo);
            exit();
        }
    }

    // Handle cases when there's no pet information, vaccinations, or deworming records
    if (isset($response)) {
        ob_clean(); // Clear output buffer
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pet_registration']) && $_POST['pet_registration'] === 'register_pet') {
    $petName = $_POST['petName'];
    $species = $_POST['species'];
    $breed = $_POST['breed'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $pettable = 'pet_data_' . $user['Id'];
    $petOwner = $user['Firstname'] . ' ' . $user['Lastname']; // User's full name
    $unique_id = uniqid();

    $stmt = $conn->prepare("INSERT INTO $pettable (unique_id, pet_name, species, breed, age, gender, birthdate) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssss", $unique_id, $petName, $species, $breed, $age, $gender, $birthdate);
    $execval = $stmt->execute();

    if (!$execval) {
        echo "Error inserting into $pettable: " . $stmt->error;
    } else {
        // Retrieve the ID of the last inserted pet
        $lastPetUniqueId = $unique_id;

        $stmt2 = $conn->prepare("INSERT INTO pets (unique_id, pet_name, species, breed, age, gender, birthdate, owner, owner_id) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt2->bind_param("ssssssssi", $unique_id, $petName, $species, $breed, $age, $gender, $birthdate, $petOwner, $user['Id']);
        $execval = $stmt2->execute();

        if (!$execval) {
            echo "Error inserting into pets: " . $stmt2->error;
        } else {
            // Redirect to home.php after successful pet registration
            header("location: home.php");

            // Generate the button name with an increment number
            $buttonName = "button_" . $user['Id'] . "_" . $lastPetUniqueId;

            // Prepare and execute the new statement for inserting into the petbutton table
            $stmt3 = $conn->prepare("INSERT INTO $petButtonTable (button_name) VALUES (?)");
            $stmt3->bind_param("s", $buttonName);
            $execval3 = $stmt3->execute();

            if ($execval3) {
                echo '<script type="text/javascript">
                    alert("Button storage successful.");
                    </script>';
            } else {
                echo '<script type="text/javascript">
                    alert("Button storage failed. Please try again.");
                    </script>';
            }

            $stmt3->close();
        }

        $stmt2->close();
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
    <link rel="stylesheet" href="index.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

    <title>Morgan&Friends</title>

    <style>
</style>

    
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

    <div class="row" style="width: 100%;" >
        <div class="col-md-3 sidebar">
            <div class="register-pet-section">
                <button class="register-pet-button" onclick="showRegistrationForm()">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="col-md-12 ">
                <div id="buttonContainer" class="row">
                    <!-- Content for button container -->
                </div>
            </div>
        </div>

        <div class="col-md-9 opacity">
            <div id="homeContent" class="container-fluid">
                <div class="sliding-content">
                    <div class="slider">
                        <div class="tab active" onclick="showTab(0)">Pet Information</div>
                        <div class="tab" onclick="showTab(1)">Deworming</div>
                        <div class="tab" onclick="showTab(2)">Vaccinations</div>
                    </div>
                    <div class="pet-info">
                        <!-- Pet Information -->
                        <div class="tab-content content" id="petInfo"></div>
                        <!-- Treatment Information -->
                        <div class="tab-content content" id="deworming_records" style="display: none;"></div>
                        <div class="tab-content content" id="vaccinations" style="display: none;"></div>
                    </div>
                </div>
            </div>

            <div id="registrationForm" style="display: none;">
                <form onsubmit="submitRegistration(event)" class="form-container">
                    <h2>Register Your Pet</h2>

                    <div class="form-group row">
                        <div class="col">
                            <label for="petName">Name of Pet:</label>
                            <input type="text" id="petName" name="petName" required>
                        </div>
                        <div class="col">
                            <label for="species">Species:</label>
                            <select id="species" name="species" required onchange="updateBreedOptions()" required>
                                <option value="dog">Dog</option>
                                <option value="cat">Cat</option>
                                <!-- Add more options as needed -->
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="breed">Breed:</label>
                        <select id="breed" name="breed" required></select>
                    </div>
                    <div class="form-group row">
                    <div class="col-md-6">
    <label for="birthdate">Birthdate:</label>
    <input type="date" id="birthdate" name="birthdate" class="form-control"max="<?php echo date('Y-m-d', strtotime('-1 month')); ?>" required onchange="calculateAge()">
</div>
                        <div class="col-md-6">
                            <label for="age">Age:</label>
                            <input type="text" id="age" name="age" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender:</label>
                        <div class="radio-group">
                            <input type="radio" id="male" name="gender" value="Male" required>
                            <label for="male">Male</label>
                            <input type="radio" id="female" name="gender" value="Female" required>
                            <label for="female">Female</label>
                        </div>
                    </div>
                    <div class="form-group row button-container">
                        <div class="col-md-6">
                            <input type="hidden" name="pet_registration" value="register_pet">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-secondary" onclick="cancelRegistration()">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</html>

    <script src="index.js"></script>
    <script>
           function selectTimeSlot(timeSlot) {
            // Update the hidden input field with the selected time slot
            document.getElementById('selectedTimeSlot').value = timeSlot;
        }
        
        function cancelAppointment() {
            // Clear the selected time slot when canceling
            document.getElementById('selectedTimeSlot').value = '';
        }
    
          function showTab(tabIndex) {
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
    
        // Hide all tab contents
        tabContents.forEach(content => {
            content.style.display = 'none';
        });
    
        // Show the selected tab content
        tabContents[tabIndex].style.display = 'block';
    
        // Update the tab styles (add/remove 'active' class)
        tabs.forEach(tab => {
            tab.classList.remove('active');
        });
        tabs[tabIndex].classList.add('active');
    }
     // Trigger a click event on the first tab
     function showTab(tabIndex) {
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
    
        // Hide all tab contents
        tabContents.forEach(content => {
            content.style.display = 'none';
        });
    
        // Show the selected tab content
        tabContents[tabIndex].style.display = 'block';
    
        // Update the tab styles (add/remove 'active' class)
        tabs.forEach(tab => {
            tab.classList.remove('active');
        });
        tabs[tabIndex].classList.add('active');
    }
     // Trigger a click event on the first tab
      
    var buttonsArray = <?php echo json_encode($buttons); ?>;
    
    
    console.log(buttonsArray);
    $(document).ready(function() {
    // Add a click event listener to all buttons with the 'custom-button' class
    $('.custom-button').click(function() {
        var buttonName = $(this).data('value'); // Get the value stored in the data attribute
        fetchPetInfo(buttonName);
    });

    // Display the pet info of the first button as the initial value
    var firstButton = $('.custom-button').first();
    var initialButtonName = firstButton.data('value');
    fetchPetInfo(initialButtonName);
});
    function displayPetInfo(petInfo, tabId) {
    var tabContent = document.getElementById(tabId);

    if (tabContent) {
        // Clear previous content
        tabContent.innerHTML = '';

        if (petInfo.error && petInfo.error === "Pet not found") {
            // Display a message indicating that no pets are registered
            var noPetsMessage = "<p>No pets registered. Please register a pet first.</p>";
            tabContent.innerHTML = noPetsMessage;
        } else {
            if (tabId === "petInfo") {
                // Display pet information
                var petInfoHTML = `
                    <table>
                        <tr>
                            <th>Name:</th>
                            <td>${petInfo.pet_name}</td>
                        </tr>
                        <tr>
                            <th>Species:</th>
                            <td>${petInfo.species}</td>
                        </tr>
                        <tr>
                            <th>Breed:</th>
                            <td>${petInfo.breed}</td>
                        </tr>
                        <tr>
                            <th>Birthdate:</th>
                            <td>${petInfo.birthdate}</td>
                        </tr>
                        <tr>
                            <th>Age:</th>
                            <td>${petInfo.age}</td>
                        </tr>
                        <tr>
                            <th>Gender:</th>
                            <td>${petInfo.gender}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>${petInfo.status}</td>
                        </tr>
                        <tr>
                            <th>Comment:</th>
                            <td>${petInfo.docComment}</td>
                        </tr>
                    </table>
                `;
                tabContent.innerHTML = petInfoHTML;
            } else if (tabId === "vaccinations") {
                // Display both pet and vaccination information
                var vaccinationsHTML = `
                    <table>
                        <tr>
                            <th>Weight</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Due Date</th>
                        </tr>
                `;

                petInfo.vaccinations.forEach(vaccination => {
                    vaccinationsHTML += `
                        <tr>
                            <td>${vaccination.weight}</td>
                            <td>${vaccination.vaccination_date}</td>
                            <td>${vaccination.vaccination_type}</td>
                            <td>${vaccination.vaccination_due_date}</td>
                        </tr>
                    `;
                });

                vaccinationsHTML += `</table>`;
                tabContent.innerHTML = vaccinationsHTML;
            } else if (tabId === "deworming_records") {
                // Display deworming records
                var dewormingHTML = `
                    <table>
                        <tr>
                            <th>Date</th>
                            <th>Medication</th>
                            <th>Due Date</th>
                        </tr>
                `;

                petInfo.deworming_records.forEach(deworming => {
                    dewormingHTML += `
                        <tr>
                            <td>${deworming.deworming_date}</td>
                            <td>${deworming.deworming_medication}</td>
                            <td>${deworming.deworming_due_date}</td>
                        </tr>
                    `;
                });

                dewormingHTML += `</table>`;
                tabContent.innerHTML = dewormingHTML;
            }
        }
    }
}



function fetchPetInfo(buttonName) {
    $.ajax({
        url: 'home.php',
        type: 'GET',
        data: { buttonName: buttonName },
        dataType: 'json',
        success: function (petInfo) {
            console.log(JSON.stringify(petInfo)); // Log the JSON to the console

            // Check if there is no pet information
            if (Object.keys(petInfo).length === 0) {
                displayNoPetMessage();
            } else {
                displayPetInfo(petInfo, "petInfo");
                displayPetInfo(petInfo, "vaccinations");
                displayPetInfo(petInfo, "deworming_records");
            }
        },
        error: function (xhr, status, error) {
           
        }
    });
}



var buttonPetNames = <?php echo json_encode($petNames); ?>;

function displayButtons() {
    var buttonContainer = document.getElementById("buttonContainer");

    // Clear any existing buttons
    buttonContainer.innerHTML = '';

    // Loop through the buttonsArray and create buttons
    for (var i = 0; i < buttonsArray.length; i++) {
        var buttonName = buttonsArray[i];
        var button = document.createElement("button");

        // Set the button's data attribute to store the value (buttonName)
        button.setAttribute("data-value", buttonName);

        // Use the pet name as the button label
        button.textContent = buttonPetNames[buttonName];
        button.className = "custom-button";

        // Add an event listener to each button to display pet information
        button.addEventListener("click", function () {
            var buttonValue = this.getAttribute("data-value"); // Retrieve the stored value
            // Fetch pet information using AJAX using buttonValue
            fetchPetInfo(buttonValue);
        });

        // Add the button to the container
        buttonContainer.appendChild(button);
    }
}
 
  
function populateTreatmentTable(petInfo) {
    // Get the elements from the HTML structure
    const dewormingDateElement = document.querySelector('#treatment-info #treatment-table tr:nth-child(1) td');
    const dewormingMedicationElement = document.querySelector('#treatment-info #treatment-table tr:nth-child(3) td');
    const dewormingDueDateElement = document.querySelector('#treatment-info #treatment-table tr:nth-child(5) td');

    // Populate the elements with data from the petInfo object
    dewormingDateElement.textContent = petInfo.dewormingate;
    dewormingMedicationElement.textContent = petInfo.dewormingMedication;
    dewormingDueDateElement.textContent = petInfo.dewormingDueDate;
}

function populateVaccinationsTable(petInfo) {
    // Get the elements from the HTML structure
    const weightElement = document.querySelector('#vaccinations-info #vaccinations-table tr:nth-child(2) td');
    const dateElement = document.querySelector('#vaccinations-info #vaccinations-table tr:nth-child(4) td');
    const vaccinationTypeElement = document.querySelector('#vaccinations-info #vaccinations-table tr:nth-child(6) td');
    const dueDateElement = document.querySelector('#vaccinations-info #vaccinations-table tr:nth-child(8) td');

    // Populate the elements with data from the petInfo object
    weightElement.textContent = petInfo.weight;
    dateElement.textContent = petInfo.vaccination_date;
    vaccinationTypeElement.textContent = petInfo.vaccination_type;
    dueDateElement.textContent = petInfo.vaccination_due_date;
} 
  function displayPetInfoOrMessage(petInfo) {
            var petInfoContainer = document.getElementById("petInfo");
            var vaccinationsContainer = document.getElementById("vaccinations");
            var dewormingRecordsContainer = document.getElementById("deworming_records");

            if (Object.keys(petInfo).length === 0) {
                var noPetsMessage = "<p>No pets registered. Please register a pet first.</p>";
                petInfoContainer.innerHTML = noPetsMessage;
                vaccinationsContainer.innerHTML = "";
                dewormingRecordsContainer.innerHTML = "";
            } else {
                displayPetInfo(petInfo, "petInfo");
                displayPetInfo(petInfo, "vaccinations");
                displayPetInfo(petInfo, "deworming_records");
            }
        }

           function fetchAndDisplayFirstPet() {
            if (buttonsArray.length > 0) {
                var firstButtonName = buttonsArray[0];
                fetchPetInfo(firstButtonName);
            } else {
                var noButtonsMessage = "<p>No buttons (pets) found. Please register a pet first.</p>";
                var petInfoContainer = document.getElementById("petInfo");
                petInfoContainer.innerHTML = noButtonsMessage;
            }
        }

       
        window.onload = function() {
            displayButtons();
            fetchAndDisplayFirstPet();
        };

            document.getElementById("species").value = "dog";
    updateBreedOptions();

            
        

    </script>
    </body>
    </html>
    