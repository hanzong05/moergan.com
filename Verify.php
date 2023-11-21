<?php
// Include your database connection logic (db_connection.php) here.
require_once('db_connection.php');

// Check if a 'secret' parameter is present in the URL (e.g., http://localhost/case%20study/verify.php?secret=BASE64_ENCODED_EMAIL)
if (isset($_GET['secret'])) {
    // Decode the 'secret' parameter to get the email
    $encodedEmail = $_GET['secret'];
    $email = base64_decode($encodedEmail);

    // Perform a database query to update the user's status as verified
    $updateQuery = "UPDATE users SET is_verified = 1 WHERE Email = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        echo "Your email has been verified. You can now log in to your account.";
    } else {
        echo "Email verification failed. Please try again or contact support.";
    }
} else {
    // If the 'secret' parameter is missing, display an error or a message.
    echo "Invalid verification link.";
}
?>
