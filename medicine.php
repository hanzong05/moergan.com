<?php
session_start();

if (!isset($_SESSION['id'])) {
    // Redirect to the login page if the user is not logged in
    header('Location: adminlogin.php');
    exit();
}

// Include the file or code that establishes the database connection
include('db_connection.php'); // Adjust the file path as needed

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get selected diseases from the AJAX request
    $selectedDiseases = $_POST["diseases"];

    // Build the WHERE clause based on selected diseases
    $conditions = array();
    foreach ($selectedDiseases as $disease) {
        $conditions[] = "disease = '$disease'";
    }

    // Implode conditions with OR and create the WHERE clause
    $whereClause = implode(" OR ", $conditions);

    // Query to retrieve medicines based on selected diseases
    $sqlMedicine = "SELECT * FROM medicines WHERE $whereClause";
    $resultMedicine = $conn->query($sqlMedicine);

    if ($resultMedicine === false) {
        die("Error executing query: " . $conn->error);
    }

    if ($resultMedicine->num_rows > 0) {
        while ($rowMedicine = $resultMedicine->fetch_assoc()) {
            ?>
            <tr>
                <td><?php echo $rowMedicine['medicine_name']; ?></td>
                <td><?php echo $rowMedicine['disease']; ?></td>
                <td><img src="Medicine/<?php echo $rowMedicine['image_path']; ?>.jpg" alt="<?php echo $rowMedicine['medicine_name']; ?>" class="img-fluid" style="max-width: 100px; max-height: 100px;"></td>
            </tr>
            <?php
        }
    } else {
        echo '<tr><td colspan="3">No data available</td></tr>';
    }
} else {
    // Handle invalid requests
    echo "Invalid request";
}
?>
