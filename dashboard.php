  <?php
  session_start();

  if (!isset($_SESSION['id']) && !isset($_SESSION['chat_id'])) {
    // Redirect to the login page if the user is not logged in
    header('Location: adminlogin.php');
    exit();
  }

  // Include the database connection logic from db_connection.php
  require_once('db_connection.php');
$userId = $_SESSION['chat_id'];
$sql = "SELECT firstname, chat_id FROM admins WHERE chat_id = $userId";
$result = mysqli_query($conn, $sql);

// Fetch contacts who messaged the user
$contacts = array(); // Initialize an array to store contacts
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $userId = $row['chat_id']; // Get the user's ID
    // Query the messages table to find contacts who messaged the user
    $contactsSql = "SELECT DISTINCT incoming_msg_id AS contact_id FROM messages WHERE outgoing_msg_id = $userId
                    UNION
                    SELECT DISTINCT outgoing_msg_id AS contact_id FROM messages WHERE incoming_msg_id = $userId";
    $contactsResult = mysqli_query($conn, $contactsSql);
    if ($contactsResult && mysqli_num_rows($contactsResult) > 0) {
        while ($contactRow = mysqli_fetch_assoc($contactsResult)) {
            $contacts[] = $contactRow['contact_id'];
        }
    }
} else {
    // Handle the case where the user data cannot be retrieved
    // You may want to redirect the user to a different page or display an error message
}



  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

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

<div class="container">
    <div class="row">
    <?php
// ... (your existing code)

// Count the registered clients where is_verified is equal to 1
$countClientsSql = "SELECT COUNT(*) AS clientCount FROM users WHERE is_verified = 1";
$countClientsResult = mysqli_query($conn, $countClientsSql);

// Check if the query was successful
if ($countClientsResult && mysqli_num_rows($countClientsResult) > 0) {
    $row = mysqli_fetch_assoc($countClientsResult);
    $clientCount = $row['clientCount'];

    // Output the count in your card counter
    echo '
        <div class="col-md-4">
            <div class="card-counter primary">
            <i class="fas fa-users"></i>
                <span class="count-numbers">' . $clientCount . '</span>
                <span class="count-name">Clients</span>
            </div>
        </div>';
} else {
    // Handle the case where the query fails
    echo 'Error: Unable to fetch client count.';
}

?>

<?php
// ... (your existing code)

// Count the pets where archived_user_id is equal to 0
$countPetsSql = "SELECT COUNT(*) AS petCount FROM pets WHERE is_archived_userid = 0";
$countPetsResult = mysqli_query($conn, $countPetsSql);

// Check if the query was successful
if ($countPetsResult && mysqli_num_rows($countPetsResult) > 0) {
    $row = mysqli_fetch_assoc($countPetsResult);
    $petCount = $row['petCount'];

    // Output the count in your card counter
    echo '
        <div class="col-md-4">
            <div class="card-counter danger">
            <i class="fas fa-paw"></i>
                <span class="count-numbers">' . $petCount . '</span>
                <span class="count-name">Pets</span>
            </div>
        </div>';
} else {
    // Handle the case where the query fails
    echo 'Error: Unable to fetch pet count.';
}

// ... (rest of your code)
?>



<?php
// ... (your existing code)

// Count the admins
$countAdminsSql = "SELECT COUNT(*) AS adminCount FROM admins";
$countAdminsResult = mysqli_query($conn, $countAdminsSql);

// Check if the query was successful
if ($countAdminsResult && mysqli_num_rows($countAdminsResult) > 0) {
    $row = mysqli_fetch_assoc($countAdminsResult);
    $adminCount = $row['adminCount'];

    // Output the count in your card counter
    echo '
        <div class="col-md-4">
            <div class="card-counter success">
            <i class="fas fa-cog"></i>
            <span class="count-numbers">' . $adminCount . '</span>
                <span class="count-name">Admin</span>
            </div>
        </div>';
} else {
    // Handle the case where the query fails
    echo 'Error: Unable to fetch admin count.';
}

// ... (rest of your code)
?>

      
  </div>
</div>
            <div class="container mt-5   .custom-container" >
                
                <div class="row">
                    <div class="col-md-3 contact">
                        <div class="card">
                            <div class="card-header">
                                <h4>Customers</h4>
                            </div>
                            <div class="card-body contact-list">
                                <ul>
                                    <?php foreach ($contacts as $contactId) {
                                        $contactNameSql = "SELECT Firstname, unique_id,Image FROM users WHERE unique_id = $contactId";
                                        $contactNameResult = mysqli_query($conn, $contactNameSql);
                                        if ($contactNameResult && mysqli_num_rows($contactNameResult) > 0) {
                                            $contactNameRow = mysqli_fetch_assoc($contactNameResult);
                                            $contactName = $contactNameRow['Firstname'];
                                            $uniqueId = $contactNameRow['unique_id'];
                                            $image = $contactNameRow['Image'];

                                            echo '
                                            <li class="p-2 border-bottom" style="background-color: #eee;">
                                                <a href="#!" class="contact-link d-flex justify-content-between" data-unique-id="' . $uniqueId . '">
                                                    <div class="d-flex flex-row">
                                                        <img src="uploads/' . $image . '" class="rounded-circle d-flex align-self-center me-3 shadow-1-strong" width="60">
                                                        <div class="pt-1">
                                                            <p class="fw-bold mb-0">' . $contactName . '</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>';
                                        }
                                    } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9 box">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-5 "  id="chatHeader">  <h4>Select Contact</h4></div>
                               
                                    <div class="col-md-2">
                                <div id="exitButton" style="margin-top: -40px">X</div>
                                </div>
                            </div>
                                
                            </div>
                            <div class="card-body chat-body">
                                <!-- Existing chat messages will be displayed here -->
                            </div>
                            <div class="card-footer">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="messageInput" placeholder="Type your message">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" id="sendButton">Send</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap and jQuery JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="dashboard.js"></script>
    <script>
   $(document).ready(function () {
    var uniqueId; // Define uniqueId outside of the click event handler

    // Function to fetch and append messages
    function fetchAndAppendMessages() {
        // Make an AJAX request to get messages
        $.ajax({
            url: "get_messages_admin.php",
            method: "POST",
            dataType: "json",
            data: { uniqueId: uniqueId },
            success: function (response) {
                // Handle success, append messages to the chat body
                var chatBody = $(".chat-body");
                chatBody.empty(); // Clear previous messages

                if (Array.isArray(response)) {
                    response.forEach(function (message) {
                        var messageHtml = '<div class="message ' + message.class + '">' + message.msg + '</div>';
                        chatBody.append(messageHtml);
                    });
                } else {
                    // Handle the case where response is not an array
                    console.error("Invalid response format. Expected an array.");
                }
            }
        });
    }

    function toggleChatContainer(show) {
        var chatContainer = $(".box");
        var contactContainer = $(".contact");

        if (show) {
            chatContainer.show();
            if (window.innerWidth >= 768) {
                contactContainer.show();
            } else {
                contactContainer.hide();
            }
        } else {
            chatContainer.hide();
            contactContainer.show(); // Always show the contact on smaller screens
        }
    }

    function toggleExitButtonVisibility() {
        var exitButton = $("#exitButton");
        var isSmallScreen = window.innerWidth < 600; // Adjust the breakpoint as needed

        if (isSmallScreen) {
            exitButton.show();
        } else {
            exitButton.hide();
        }
    }

    // Event listener for clicking a contact link
    $(".contact-link").on("click", function (e) {
        e.preventDefault();

        uniqueId = $(this).data("unique-id");
        var contactName = $(this).text();
        $("#chatHeader").html('<h4>' + contactName + '</h4>');
        fetchAndAppendMessages();

        // Show the box and hide the contact when a contact is selected on smaller screens
        // On larger screens, both will be shown
        toggleChatContainer(true);
    });

    // Event listener for clicking the exit button
    $("#exitButton").on("click", function () {
        // Hide the box and show the contact when exiting the chat on smaller screens
        // On larger screens, both will be shown
        toggleChatContainer(false);
        uniqueId = null; // Clear the selected contact when exiting the chat
    });

    // Event listener for window resize
    $(window).resize(function () {
        toggleExitButtonVisibility();

        // Show both the box and contact containers on larger screens
        // On smaller screens, maintain the current show/hide state
        toggleChatContainer(window.innerWidth >= 768);
    });

    // Initial call to set the initial visibility based on screen size
    toggleExitButtonVisibility();

    // Set an interval for fetching messages
    setInterval(function () {
        if (uniqueId) {
            fetchAndAppendMessages(); // Fetch and append messages for the selected contact
        }
    }, 5000);

    // Function to send a message to the selected contact
    $("#sendButton").click(function () {
        var messageInput = $("#messageInput");
        var message = messageInput.val().trim();
        
        if (message !== "") {
            // Make an AJAX request to send the message
            $.ajax({
                url: "save_message_admin.php",
                method: "POST",
                data: {
                    message: message,
                    uniqueId: uniqueId
                },
                success: function () {
                    // Handle success, such as clearing the input field
                    messageInput.val("");
                }
            });
        }
    });

    // Prevent automatic toggling when the message input is touched
    $("#messageInput").on("touchstart", function (e) {
        e.stopPropagation();
    });
});


  </script>

  </body>
  </html>
