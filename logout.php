<?php
session_start();

if (isset($_SESSION['user'])) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Set the logout message

} else {
   
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Your existing head content -->

    <title>Morgan&Friends</title>
</head>

<body style="background-image: url(image/back.jpg); background-repeat: no-repeat; background-size: cover; background-attachment:fixed;">
    <div class="container-fluid">
        <!-- Your navigation bar code -->

        <!-- Rest of your content -->

        <!-- Success message div -->
        <div id="successMessage" class="alert alert-success">
            Profile updated successfully, please login again
            <button id="okButton" class="btn btn-primary" onclick="redirectToLogin()">OK</button>
        </div>
    </div>

    <script src="index.js"></script>

    <script>
        // Function to show the success message div
        function showSuccessMessage() {
            const successMessageDiv = document.getElementById('successMessage');
            successMessageDiv.style.display = 'block';
        }

        // Function to redirect to the login page
        function redirectToLogin() {
            window.location.href = 'index.php'; // Replace 'login.php' with the actual login page URL
        }
    </script>
</body>
</html>
