<?php
session_start();

// Include the database connection logic
require_once('db_connection.php');

// Handle pet registration form data
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
$pettable = 'pet_data_' . $user['Id'];

$queryPets = "SELECT * FROM pets WHERE owner_id = ?";
$stmtPets = $conn->prepare($queryPets);
$stmtPets->bind_param("i", $user['Id']);
$stmtPets->execute();
$resultPets = $stmtPets->get_result();

// Create an array to store pets
$pets = array();
while ($rowPets = $resultPets->fetch_assoc()) {
    $pets[] = $rowPets;
}
$stmtPets->close();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['upload_profile_picture'])) {
      // Check if a file was uploaded
      if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
          // Define the upload directory
          $uploadDir = __DIR__ . '/uploads/'; // Make sure 'uploads' directory exists

          // Generate a unique file name
          $originalFileName = $_FILES['profilePicture']['name'];
          $uniqueFileName = uniqid() . '_' . $originalFileName;
          $uploadFile = $uploadDir . $uniqueFileName;

          // Move the uploaded file to the destination directory
          if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $uploadFile)) {
            // File uploaded successfully, now insert the file path into the database
            $query = "UPDATE users SET Image=? WHERE Id=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $uniqueFileName, $user['Id']);
        
            if ($stmt->execute()) {
                // Profile picture path updated successfully
                header('Location: userprofile.php');
                exit();
            } else {
                // Handle the database insert error
                echo "Profile picture update failed. Please try again.";
                echo $stmt->error; // Display SQL error if any
            }
        } else {
            // Handle file upload error
            echo "File upload failed. Please try again.";
        }
      }  else {
          // Handle specific upload errors
          switch ($_FILES['profilePicture']['error']) {
              case UPLOAD_ERR_INI_SIZE:
                  echo "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                  break;
              case UPLOAD_ERR_FORM_SIZE:
                  echo "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
                  break;
              case UPLOAD_ERR_PARTIAL:
                  echo "The uploaded file was only partially uploaded.";
                  break;
              case UPLOAD_ERR_NO_FILE:
                  echo "No file was uploaded.";
                  break;
              case UPLOAD_ERR_NO_TMP_DIR:
                  echo "Missing a temporary folder.";
                  break;
              case UPLOAD_ERR_CANT_WRITE:
                  echo "Failed to write the file to disk.";
                  break;
              case UPLOAD_ERR_EXTENSION:
                  echo "A PHP extension stopped the file upload.";
                  break;
              default:
                  echo "File upload failed. Please try again.";
          }
      }
  }if (isset($_POST['save_profile'])) {
    // Get updated user information from the POST data
    $userId = $user['Id'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $mobileNumber = $_POST['mobile_number'];
    $address = $_POST['address'];

  
    // Check if a new password is provided
    if (!empty($plainTextPassword)) {
        // Hash the new password
        $hashedPassword = password_hash($plainTextPassword, PASSWORD_DEFAULT);
    } else {
        // If no new password is provided, use the existing hashed password
        $hashedPassword = $user['Password'];
    }

    if (!preg_match('/^09[0-9]{9}$/', $mobileNumber)) {
        echo "<script>alert('Invalid mobile number. It must start with \"09\" and have exactly 11 digits.');</script>";
    }
    else{
         // Update the user information in the database
$query = "UPDATE users SET Firstname=?, Lastname=?, MobileNumber=?, Address=? WHERE Id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssi", $firstName, $lastName, $mobileNumber, $address, $userId);

$owner = $firstName . ' ' . $lastName;

$query2 = "UPDATE pets SET owner = ? WHERE owner_id=?";
$stmt2 = $conn->prepare($query2);
$stmt2->bind_param("si", $owner, $userId);

$pet_owner = $firstName; // Make sure this is correct
$query3 = "UPDATE requested_appointments SET pet_owner = ? WHERE owner_id=?"; // Assuming it's user_id
$stmt3 = $conn->prepare($query3);
$stmt3->bind_param("si", $pet_owner, $userId);

$query4 = "UPDATE scheduled_appointments SET pet_owner = ? WHERE owner_id=?"; // Assuming it's user_id
$stmt4 = $conn->prepare($query4);
$stmt4->bind_param("si", $pet_owner, $userId);

$query5 = "UPDATE notification SET user_name = ? WHERE user_id=?"; // Assuming it's user_id
$stmt5 = $conn->prepare($query5);
$stmt5->bind_param("si", $pet_owner, $userId);

if ($stmt->execute() && $stmt2->execute() && $stmt3->execute() && $stmt4->execute()&& $stmt5->execute()) {
    // All updates were successful
    header('Location: logout.php');
    exit();
} else {
    // Handle the update error
    echo "Profile update failed. Please try again.";
    echo $stmt->error; // Display SQL error if any
    echo $stmt2->error; // Display SQL error if any
    echo $stmt3->error;
    echo $stmt4->error; // Display SQL error if any
}
}
}
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Check if the form submission is for updating pet information
        if (isset($_POST['update_pet_info'])) {
            $petId = $_POST['pet_id']; // Add a hidden input field in your form to store the pet ID
            $newPetName = $_POST['name'];
            $newSpecies = $_POST['species'];
            $newBreed = $_POST['breed'];
            $newAge = $_POST['age'];
            $newGender = $_POST['gender'];
            $newBirthdate = $_POST['birthdate'];
    
            // Prepare and execute the update query for the specific pet in the pet_data table
            $updatePetDataTableQuery = "UPDATE $pettable SET pet_name=?, species=?, breed=?, age=?, gender=?, birthdate=? WHERE unique_id=?";
            $updatePetDataTableStmt = $conn->prepare($updatePetDataTableQuery);
            $updatePetDataTableStmt->bind_param("sssssss", $newPetName, $newSpecies, $newBreed, $newAge, $newGender, $newBirthdate, $petId);
            $updatePetDataTableResult = $updatePetDataTableStmt->execute();
    
            // Check if the update in the pet_data table was successful
            if ($updatePetDataTableResult) {
                // Prepare and execute the update query for the specific pet in the pets table
                $updatePetsTableQuery = "UPDATE pets SET pet_name=?, species=?, breed=?, age=?, gender=?, birthdate=? WHERE unique_id=?";
                $updatePetsTableStmt = $conn->prepare($updatePetsTableQuery);
                $updatePetsTableStmt->bind_param("sssssss", $newPetName, $newSpecies, $newBreed, $newAge, $newGender, $newBirthdate, $petId);
                $updatePetsTableResult = $updatePetsTableStmt->execute();
    
                // Check if the update in the pets table was successful
                if ($updatePetsTableResult) {
                    echo "Pet information updated successfully.";
                } else {
                    echo "Error updating pet information in the pets table: " . $updatePetsTableStmt->error;
                }
            } else {
                echo "Error updating pet information in the pet_data table: " . $updatePetDataTableStmt->error;
            }
        }
    
        // Add other conditions for handling other form submissions if needed
    }
  

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

    
</head>

<body style="background-image: url(image/back.jpg); background-repeat: no-repeat; background-size: cover; background-attachment:fixed;">
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light fixed-top bg-light">
            <a class="navbar-brand" href="#">Morgan&Friends</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
              aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item ">
                      <a class="nav-link" href="home.php"aria-current="page">Home</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" aria-current="page"  href="appointment.php">Appointment</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" aria-current="page"  href="userprofile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" aria-current="page" href="notification.php">Notification</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" aria-current="page" href="messages.php">Message</a>
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

    <br>
    <div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="uploads/<?php echo $image; ?>" class="img-fluid rounded-circle"
                        alt="Profile Picture" style="max-width: 200px;">
                        
                        
                <div id="uploadContent">
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" id="profilePicture" name="profilePicture" accept="image/*">
                        <button class="btn btn-primary" type="submit" name="upload_profile_picture">Upload</button>
                    </form>
                </div>
                
                    <button id="editProfilePictureButton" class="btn btn-secondary mt-3">Edit Profile Picture</button>
                </div>
               
            </div>
            <br>
            <div class="card">
                <div class="card-body text-center">
                   
                
                <?php echo $user['Firstname'] . ' ' . $user['Lastname']; ?>
                <span class="text-black-50"><?php echo $user['Email']; ?></span>
                </div>
                <button class="btn btn-primary" onclick="redirectToChangePassword()">Change Password</button>

            </div>
        </div>
      
        <div class="col-md-4" >
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Profile Settings</h4>
                    <form method="post">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control profile-input" name="first_name"
                                value="<?php echo $user['Firstname'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control profile-input" name="last_name"
                                value="<?php echo $user['Lastname'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="mobile_number">Mobile Number</label>
                            <input type="number" class="form-control profile-input"   value="<?php echo $user['MobileNumber'] ?>" name="mobile_number"required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" class="form-control profile-input" name="address"
                                value="<?php echo $user['Address'] ?>">
                        </div>
                        <button class="btn btn-secondary" type="button" id="editProfileButton">Edit Profile</button>
                        <button class="btn btn-primary profile-button" type="submit" name="save_profile" >Save Profile</button>
                    </form>
                </div>
            </div>
        </div>
        

        

        <div class="col-md-4" style="max-height: 465px; overflow-y: auto;">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Your Pets</h4>
            <?php foreach ($pets as $pet) : ?>
                <div class="pet-info mb-3">
                    <p class="mb-1"><strong>Name:</strong> <?= $pet['pet_name']; ?></p>
                    <p class="mb-1"><strong>Species:</strong> <?= $pet['species']; ?></p>

                    <!-- Edit Button -->
                    <button class="btn btn-warning btn-sm" onclick="toggleInfo('petInfoForm<?= $pet['unique_id']; ?>', 'petInfo<?= $pet['unique_id']; ?>')">Info</button>

                </div>

                <!-- Form for each pet -->
                <form method="post" id="petInfoForm<?= $pet['unique_id']; ?>" style="display:none">
    <!-- Add a hidden input field to store the pet ID -->
    <input type="hidden" name="update_pet_info" value="1">
    <input type="hidden" name="pet_id" value="<?= $pet['unique_id']; ?>">

    <div class="form-group">
        <label for="pet_name">Pet Name</label>
        <input type="text" class="form-control" name="name" value="<?= $pet['pet_name']; ?>" readonly required>
    </div>
    <div class="form-group">
        <label for="species">Species</label>
        <select class="form-control" name="species" id="species"required onchange="updateBreedOptions()" disabled>
            <option value="dog" <?= ($pet['species'] == 'dog') ? 'selected' : ''; ?>>Dog</option>
            <option value="cat" <?= ($pet['species'] == 'cat') ? 'selected' : ''; ?>>Cat</option>
            <!-- Add more options as needed -->
        </select>
    </div>
    <div class="form-group">
        <label for="breed">Breed</label>
        <select class="form-control" name="breed" id="breed" disabled>
        <?php
        // Assuming $pet['breed'] contains the breed of the pet
        $selectedBreed = $pet['breed'];
        echo '<option value="' . $selectedBreed . '" selected>' . $selectedBreed . '</option>';
        
        // Add other options here if needed
        ?>
        </select>
    </div>
    <div class="form-group">
        <label for="gender">Gender</label>
        <select class="form-control" name="gender" id="gender" required disabled>
            <option value="Male" <?= ($pet['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
            <option value="Female" <?= ($pet['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
            <!-- Add more options as needed -->
        </select>
    </div>
    <div class="form-group">
    <label for="birthdate">Birthdate</label>
    <input type="date" class="form-control" name="birthdate" id="birthdate"max="<?php echo date('Y-m-d', strtotime('-1 month')); ?>"  value="<?= $pet['birthdate']; ?>"   onchange="calculateAge()" required readonly >
</div>
<div class="form-group">
        <label for="age">Age</label>
        <input type="text" class="form-control" name="age" id="age" value="<?= $pet['age']; ?>"required readonly>
    </div>
 
 
    <div class="form-group">
        <!-- Edit Button -->
        <button id="editButton<?= $pet['unique_id']; ?>" class="btn btn-warning btn-sm edit-pet" data-pet-id="<?= $pet['unique_id']; ?>" type="button">Edit</button>
        <!-- Save Button -->
        <button id="saveButton<?= $pet['unique_id']; ?>" style="display: none;" class="btn btn-success btn-sm save-pet">Save</button>
    </div>
</form>


<!-- Add this line in the head section of your HTML to include jQuery UI -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


            <?php endforeach; ?>
        </div>
    </div>
</div>
   </div>
</div>


                    
    <script src="index.js"></script>

    <script>
        function validateMobileNumber() {
    var mobileNumberInput = document.getElementsByName('mobile_number')[0];
    var mobileNumber = mobileNumberInput.value;

    // Check if the mobile number starts with "09"
    if (!mobileNumber.startsWith('09')) {
        alert('Mobile number must start with "09".');
        return false; // Prevent form submission
    }

    // Continue with form submission if the check passes
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    // Disable input fields and hide the save button initially
    const inputFields = document.querySelectorAll('.profile-input');
    const saveButton = document.querySelector('.profile-button');

    inputFields.forEach(function (input) {
        input.disabled = true;
    });

    saveButton.style.display = 'none';

    // Add an event listener to the "Edit Profile" button
    const editButton = document.getElementById('editProfileButton');
    editButton.addEventListener('click', function () {
        inputFields.forEach(function (input) {
            input.disabled = false;
        });

        saveButton.style.display = 'block';

        editButton.style.display = 'none';
    });
});
document.addEventListener('DOMContentLoaded', function () {
    // Get the necessary elements
    const editPictureButton = document.getElementById('editProfilePictureButton');
    const uploadContent = document.getElementById('uploadContent');
    const profilePictureInput = document.getElementById('profilePicture');

    // Hide the profile picture input initially
    uploadContent.style.display = 'none';

    // Add an event listener to the "Edit Profile Picture" button
    editPictureButton.addEventListener('click', function () {
        // Show the file input field for uploading a new profile picture
        uploadContent.style.display = 'block';
    });
});

function redirectToChangePassword() {
    window.location.href = 'newpass.php';
}


</script>
<script>
   function toggleInfo(formId, infoId) {
        var form = document.getElementById(formId);
        var info = document.getElementById(infoId);

        // Toggle form display
        form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';

        // Toggle pet info display
        info.style.display = (info.style.display === 'none' || info.style.display === '') ? 'block' : 'none';
    }
    function togglePetInfoButtons(petId) {
        var editButton = $('#editButton' + petId);
        var saveButton = $('#saveButton' + petId);
        var form = $('#petInfoForm' + petId);

        // Toggle buttons and form display
        editButton.toggle();
        saveButton.toggle();
        form.find(':input[readonly]').prop('readonly', function (i, value) {
            return !value;
        });
    }

    $(document).ready(function() {
        $(".datepicker").datepicker();
        var petId = $(this).data("pet-id");

        $("#petInfoForm" + petId + " #breed").prop("disabled", true);


// Modify the edit-pet click event to re-initialize the datepicker
$(".edit-pet").click(function () {
    
    var petId = $(this).data("pet-id");

    // Enable editing for the fields in the corresponding form, excluding species and breed
    enableEditing(petId);

    // Toggle the visibility of the Edit and Save buttons
    $("#editButton" + petId).hide();
    $("#saveButton" + petId).show();

    
    // Enable species and breed fields
   // Enable species and breed fields
    $("#petInfoForm" + petId + " #species, #petInfoForm" + petId + " #breed").prop("disabled", false);

    // Set the selected species in the species dropdown
    var selectedSpecies = $("#petInfoForm" + petId + " #species").val();
    $("#petInfoForm" + petId + " #species option[value='" + selectedSpecies + "']").prop("selected", true);

    // Set the selected breed to be equal to the breed of the pet
    var selectedBreed = $("#petInfoForm" + petId + " #breed").val();
    $("#petInfoForm" + petId + " #breed option[value='" + selectedBreed + "']").prop("selected", true);
    updateBreedOptions();
});


    // Save button click event
    $(".save-pet").click(function() {
        // Validate the form before submitting
        var petId = $(this).attr("id").replace("saveButton", "");
        if (validateForm(petId)) {
            // Enable editing for the fields in the corresponding form
            enableEditing(petId);

            // Update the field names to match those expected by the PHP script
            updateFieldNames(petId);

            // Submit the form
            $("#petInfoForm" + petId).submit();
        } else {
            alert("Please fill out all required fields before saving.");
        }
    });

    function enableEditing(petId) {
        // Enable editing for the fields in the corresponding form
        $("#petInfoForm" + petId + " :input").prop("disabled", false);
        $("#petInfoForm" + petId + " :input:not(#species, #breed)").prop("readonly", false);
    }

    function updateFieldNames(petId) {
        // Update the field names to match those expected by the PHP script
        $("#petInfoForm" + petId + " [name^='new_']").each(function() {
            var currentName = $(this).attr("name");
            var updatedName = currentName.replace("new_", "");
            $(this).attr("name", updatedName);
        });
    }

    function validateForm(petId) {
        // Perform form validation
        var isValid = true;
        $("#petInfoForm" + petId + " :input[required]").each(function() {
            if ($(this).val().trim() === "") {
                isValid = false;
                return false; // Break out of the loop if any required field is empty
            }
        });
        return isValid;
    }
});

    
</script>
    </body>
    </html>