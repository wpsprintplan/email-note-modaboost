<?php
$servername = DB_HOST;
$username = DB_USER;
$password = DB_PASSWORD;
$dbname = DB_NAME;

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if edit_ids parameter is set in the URL
if (isset($_GET['check_id'])) {
    // Retrieve the IDs to edit as an array
    $edit_ids = explode(',', $_GET['check_id']);

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_multiple_records'])) {
        // Loop through each ID and update the corresponding records
        foreach ($edit_ids as $id) {
            // Retrieve form data
            $email = mysqli_real_escape_string($conn, $_POST["email_$id"]);
            $notes = mysqli_real_escape_string($conn, $_POST["notes_$id"]);
            $status = mysqli_real_escape_string($conn, isset($_POST["status_$id"]) ? 'active' : 'inactive');

            // Update the record in the database
            $update_id = mysqli_real_escape_string($conn, $id);
            $update_sql = "UPDATE " . EN_TABLE_NAME . " SET email='$email', notes='$notes', status='$status' WHERE id='$update_id'";

            if ($conn->query($update_sql) !== true) {
                // Error updating record
                echo "Error updating record with ID $id: " . $conn->error;
            }
        }

        // All records updated successfully
        echo "All records updated successfully.";
        $redirect_url = admin_url("admin.php?page=email-note-plugin");

        header("Location: $redirect_url");
        exit;
    }

    // Display a form for editing and updating multiple records
    echo "<h2>Edit Records</h2>";
    echo "<form method='POST'>";
    echo "<input type='hidden' name='update_multiple_records' value='1'>";

    // Add title row with borders
    echo "<div style='display: flex; justify-content: space-between; margin-bottom: 10px; border: 1px solid #ccc; padding: 5px;'>";
    echo "<div style='flex-basis: 60%; text-align: center; border-right: 1px solid #ccc;'>Email</div>";
    echo "<div style='flex-basis: 20%; text-align: center; border-right: 1px solid #ccc;'>Notes</div>";
    echo "<div style='flex-basis: 20%; text-align: center;'>Status</div>";
    echo "</div>";

    foreach ($edit_ids as $id) {
        // Query the database to retrieve the record for this ID
        $sql = "SELECT * FROM " . EN_TABLE_NAME . " WHERE id = '$id'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            echo "<div style='display: flex; justify-content: space-between; margin-bottom: 10px; border: 1px solid #ccc; padding: 5px;'>";
            echo "<div style='flex-basis: 60%; text-align: center; border-right: 1px solid #ccc;'><input style='width:100%;' name='email_$id' type='email' value='" . $row['email'] . "' required></div>";
            echo "<div style='flex-basis: 20%; text-align: center; border-right: 1px solid #ccc;'><select name='notes_$id'>";
            // Add options for notes based on your requirements
            echo "<option value='burnt' " . selected($row['notes'], 'burnt', false) . ">Burnt</option>";
            echo "<option value='unavailable' " . selected($row['notes'], 'unavailable', false) . ">Unavailable</option>";
            echo "<option value='usable' " . selected($row['notes'], 'usable', false) . ">Usable</option>";
            echo "</select></div>";
            echo "<div style='flex-basis: 20%; text-align: center;'><input name='status_$id' type='checkbox' value='active' " . checked($row['status'], 'active', false) . "></div>";
            echo "</div>";

        }
    }

    echo "<button class='button-primary woocommerce-save-button' type='submit' name='update_multiple_records_button'>Update All Records</button>";
    echo "</form>";
} else {
    // Handle the case where check_id parameter is not set
    echo "No records selected for editing.";
}
