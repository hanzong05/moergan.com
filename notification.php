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

$sql = "SELECT Firstname FROM users WHERE Id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user['Id']);
$stmt->execute();
$stmt->bind_result($firstName);
$stmt->fetch();
$stmt->close();

// Now you have the user's first name, and you can use it to query notifications
$sql = "SELECT * FROM notification WHERE user_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $firstName);
$stmt->execute();
$result = $stmt->get_result();

// Loop through the result and process your notifications
while ($row = $result->fetch_assoc()) {
    // Process each notification as needed
    // Example: echo $row['notification_content'];
}

// Close the database connection





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

    <br>

    <br>

    <?php
// ... (your existing code)
// Modify your SQL query to select the data from the "notification" table and order by notification_date in descending order
$sql = "SELECT notification_text, notification_date FROM notification WHERE user_name = ? ORDER BY notification_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $firstName);
$stmt->execute();
$result = $stmt->get_result();

// Check if the query was successful
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Extract the data from the query result for each notification
        $notificationText = $row['notification_text'];
        $notificationDate = $row['notification_date'];

        // Generate HTML to display each notification
        echo '<div class="container-fluid ">
                <div class="container">
                    <div class="notification card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="ml-3">
                                    <div class="notification">
                                        <p class="notification-text font-weight-bold mb-1">' . $notificationText . '</p>
                                        <p class="notification-time text-muted small">' . $notificationDate . '</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
    }
} else {
    echo "Query failed: ";
}

// ... (the rest of your code)
?>

    


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
                    
    <script src="index.js"></script>
   
    </script>
    </body>
    </html>
    