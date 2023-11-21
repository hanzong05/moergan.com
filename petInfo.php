
<?php
if (isset($_GET['buttonName'])) {
    $buttonName = $_GET['buttonName'];
    // Construct the pet table name (replace with your actual naming convention)
    $buttonParts = explode('_', $buttonName);
    if (count($buttonParts) != 3) {
        $response = ["error" => "Invalid button name"];
    }
    $petId = intval($buttonParts[2]);
    $pettable = 'pet_data_' . $_SESSION['user']['Id'];

    // Use prepared statement to fetch pet information by button name
    $query = "SELECT * FROM $pettable WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $petId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Fetch and return the pet information as an associative array
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Pet not found"]);
    }
    exit; // Exit to prevent further execution of the PHP code
}

// ...
To fetch and return vaccination data, you should modify your PHP code to include vaccination information in the response. You can use a similar approach to fetch the vaccination data and add it to the response. Here's an example of how to modify your PHP code:

php
Copy code
// ...

if (isset($_GET['buttonName'])) {
    $buttonName = $_GET['buttonName'];
    // Construct the pet table name (replace with your actual naming convention)
    $buttonParts = explode('_', $buttonName);
    if (count($buttonParts) != 3) {
        $response = ["error" => "Invalid button name"];
    }
    $petId = intval($buttonParts[2]);
    $pettable = 'pet_data_' . $_SESSION['user']['Id'];

    // Use prepared statement to fetch pet information by button name
    $query = "SELECT * FROM $pettable WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $petId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Fetch pet information as an associative array
        $row = $result->fetch_assoc();

        // Now, fetch vaccination data for the pet
        $vaccinationQuery = "SELECT * FROM vaccinations WHERE pet_id = ?";
        $vaccinationStmt = $conn->prepare($vaccinationQuery);
        $vaccinationStmt->bind_param("i", $petId);
        $vaccinationStmt->execute();
        $vaccinationResult = $vaccinationStmt->get_result();

        // Add vaccination data to the pet information
        $row['vaccinations'] = [];

        while ($vaccinationRow = $vaccinationResult->fetch_assoc()) {
            $row['vaccinations'][] = $vaccinationRow;
        }

        // Return the combined data as JSON
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Pet not found"]);
    }
    exit; // Exit to prevent further execution of the PHP code
}?>