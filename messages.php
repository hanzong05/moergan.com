
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
      
      .card {
  border: none;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}
.card-header {
  background-color: #007bff;
  color: #fff;
  border-radius: 10px 10px 0 0;
}
.chat-body {
  height: 400px;
  overflow-y: auto;
}
.message {
  max-width: 300px;
  padding: 10px;
  margin: 5px;
  border-radius: 10px;
  font-size: 16px;
}
.received {
background-color: #d4d4d4;
color: #000;
}

.sent {
text-align: right;
background-color: #007bff;
color: #fff;
margin-left: auto; /* Move sent messages to the right by pushing them to the right margin */
}

/* Style the chat input container */
.chat-input {
    display: flex;
    align-items: center;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 5px;
}

/* Style the chat input field */
.chat-input input {
    flex: 1;
    border: none;
    outline: none;
    padding: 10px;
    border-radius: 0;
}

/* Style the send button */
.chat-input button {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 0;
    cursor: pointer;
}

/* Hover effect for the send button */
.chat-input button:hover {
    background-color: #0056b3;
}

    </style>
    
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
            
                  <ul class="navbar-nav flex-row" >
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
                      <a class="nav-link ps-2" href="#!">
                        
                      <img src="uploads/<?php echo $image; ?>" alt="Profile Image" width="20" height="20" style="border-radius:50%;">
                      </a>
                    </li>
                  </ul>
            </div>
          </nav>
          
    <br>
    
    

    <br>
    
    <br>

       
</div>
 <!-- Set the column to 12 columns to take up the full width -->
 <div class="container mt-5">
                <div class="row">
                    <div class="col-md-12"> <!-- Set the column to 12 columns to take up the full width -->
                        <div class="card">
                            <div class="card-header">
                                Customer Service
                            </div>
                            <div class="card-body chat-body">
                            <div class="faq-buttons">
    <button class="btn btn-outline-info faq-button" data-message="What services do you offer?">Service FAQ</button>
    <button class="btn btn-outline-info faq-button" data-message="How can I schedule an appointment?">Appointment FAQ</button>
    <button class="btn btn-outline-info faq-button" data-message="What types of pets do you treat?">Pet Types FAQ</button>
    <button class="btn btn-outline-info faq-button" data-message="Do you offer grooming services?">Grooming FAQ</button>
    <button class="btn btn-outline-info faq-button" data-message="Can you provide nutritional advice for pets?">Nutrition FAQ</button>
    <!-- Add more FAQ buttons as needed -->
</div>

</div><div class="card-footer">
    <div class="chat-input">
        <input type="text" class="form-control" id="messageInput" placeholder="Type your message">
        <button class="btn btn-primary" id="sendButton">Send</button>
    </div>
   
</div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    <script src="index.js"></script>
   <script>

$(document).ready(function () {
    var lastMessageId = 0; // Keep track of the last message ID
    var faqVisible = true; // Flag to track whether FAQs are visible

    // Function to fetch and append messages
    function fetchAndAppendMessages() {
        // Make an AJAX request to get messages
        $.ajax({
            url: "get_messages.php", // Create a PHP script to retrieve messages
            method: "GET",
            dataType: "json", // Expect JSON response
            data: { lastMessageId: lastMessageId }, // Send the last message ID
            success: function (response) {
                // Handle success, append only new messages
                for (var i = 0; i < response.length; i++) {
                    var message = response[i];
                    if (message.msg_id > lastMessageId) {
                        var messageHtml = '<div class="message ' + message.class + '">' + message.msg + '</div>';
                        $(".chat-body").append(messageHtml);
                        lastMessageId = message.msg_id;
                    }
                }

                // Scroll to the bottom of the chat window
                var chatBody = document.querySelector('.chat-body');
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        });
    }

    // Function to check if there are existing messages
    function checkExistingMessages() {
        // Make an AJAX request to check for existing messages
        $.ajax({
            url: "check_messages.php", // Create a PHP script to check for messages
            method: "GET",
            dataType: "json",
            success: function (response) {
                // If there are existing messages, hide the FAQs
                if (response.length > 0) {
                    $(".faq-buttons").hide();
                    faqVisible = false;
                }
            }
        });
    }

    // Initial call to fetch and append messages and check for existing messages
    fetchAndAppendMessages();
    checkExistingMessages();

    // Set intervals for fetching messages and checking for existing messages
    setInterval(fetchAndAppendMessages, 5000);
    setInterval(checkExistingMessages, 5000);

    // Handle FAQ button clicks
    $(".faq-button").click(function () {
        var faqMessage = $(this).data("message");

        // Make an AJAX request to send the FAQ message
        $.ajax({
            url: "save_message.php", // Update with the correct URL
            method: "POST",
            data: {
                message: faqMessage
            },
            success: function () {
                // Handle success, such as clearing the input field
                $("#messageInput").val("");

                // Hide the FAQ buttons after the user clicks an FAQ
                $(".faq-buttons").hide();
                faqVisible = false;
            }
        });
    });

    // Handle send button click
    $("#sendButton").click(function () {
        var message = $("#messageInput").val();

        if (message.trim() !== "") {
            // Make an AJAX request to send the message
            $.ajax({
                url: "save_message.php", // Update with the correct URL
                method: "POST",
                data: {
                    message: message
                },
                success: function () {
                    // Handle success, such as clearing the input field
                    $("#messageInput").val("");

                    // Hide the FAQ buttons after the user sends a message
                    $(".faq-buttons").hide();
                    faqVisible = false;
                }
            });
        }
    });
});

   </script>
    </body>
    </html>
    