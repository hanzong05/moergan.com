<?php
session_start();
if (!isset($_SESSION['id'])) {
    // Redirect to the login page if the user is not logged in
    header('Location: adminlogin.php');
    exit();
}

require_once('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['clientId'])) {
    $clientId = $_POST['clientId'];

   
    // Update the `is_archive` field in the users table
    $updateUserQuery = "UPDATE users SET is_archive = 1 WHERE Id = ?";
    $stmtUser = $conn->prepare($updateUserQuery);
    $stmtUser->bind_param("i", $clientId);

    $usrn = "SELECT Firstname AND Lastname FROM users WHERE Id =?";
    $stmtUser = $conn->prepare($updateUserQuery);
    $stmtUser->bind_param("i", $clientId);

    // Update the `is_archived_userid` field in the pets table
    $updatePetsQuery = "UPDATE pets SET is_archived_userid = 1 WHERE owner = ?";
    $stmtPets = $conn->prepare($updatePetsQuery);
    $stmtPets->bind_param("i", $usrn);

    // Perform the user update first
    if ($stmtUser->execute()) {
        // Then perform the pets update
        if ($stmtPets->execute()) {
            echo 'Client and associated pets archived successfully';
        } else {
            echo 'Error archiving client pets: ' . $stmtPets->error;
        }
    } else {
        echo 'Error archiving client: ' . $stmtUser->error;
    }

    $stmtUser->close();
    $stmtPets->close();
}

?>
