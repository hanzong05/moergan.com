<?php
// Assuming $conn is your database connection
include('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['species'])) {
    $species = $_POST['species'];

    // Query to retrieve medicines for cats
    $sqlMedicineForCats = "SELECT * FROM medicines WHERE species = '$species' OR species = 'all' ";
    $resultMedicineForCats = $conn->query($sqlMedicineForCats);

    if ($resultMedicineForCats->num_rows > 0) {
        while ($rowMedicineForCats = $resultMedicineForCats->fetch_assoc()) {
            ?>
            <tr>
                <td><?php echo $rowMedicineForCats['medicine_name']; ?></td>
                <td><img src="Medicine/<?php echo $rowMedicineForCats['image_path']; ?>.jpg" alt="<?php echo $rowMedicineForCats['medicine_name']; ?>" class="img-fluid" style="max-width: 100px; max-height: 100px;"></td>
            </tr>
            <?php
        }
    } else {
        echo '<tr><td colspan="3">No data available for cats</td></tr>';
    }
} else {
    echo "Invalid request";
}
?>
