<?php
// Database connection details
$servername     = DB_HOST;
$username       = DB_USER;
$password       = DB_PASSWORD;
$dbname         = DB_NAME;

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for form input
$email = $notes = $status = $notice = "";

// Process update request
if (isset($_GET['update'])) {
    $id = mysqli_real_escape_string($conn, $_GET['update']);

    // Retrieve the data to pre-fill the form
    $sql = "SELECT * FROM ".EN_TABLE_NAME." WHERE id = '$id'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        $notes = $row['notes'];
        $status = $row['status'];
    }
}

// Process delete request
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);

    // Delete the record from the table
    $sql = "DELETE FROM ".EN_TABLE_NAME." WHERE id = '$id'";

    if ($conn->query($sql) === true) {
        $notice = "Record deleted successfully.";
    } else {
        $notice = "Error deleting record: " . $conn->error;
    }
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data and sanitize inputs
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $status = mysqli_real_escape_string($conn, (isset($_POST['status'])) ? $_POST['status'] : 'inactive' );

    if (!empty($_POST['update_id'])) {
        // Update the record in the table
        $update_id = mysqli_real_escape_string($conn, $_POST['update_id']);
        $sql = "UPDATE ".EN_TABLE_NAME." SET email='$email',  notes='$notes', status='$status' WHERE id='$update_id'";

        if ($conn->query($sql) === true) {
            $notice = "Record updated successfully.";
            $email  = $notes = $status = ""; // Reset form fields after update
        } else {
            $notice = "Error updating record: " . $conn->error;
        }
    } else {
        // Insert data into the table
        $datetime = date('Y-m-d H:i:s');
        $sql = "INSERT INTO ".EN_TABLE_NAME." (email, notes, status, `time`) VALUES ('$email', '$notes', '$status', '$datetime')";

        if ($conn->query($sql) === true) {
            $notice = "Data inserted successfully.";
            $email = $place = $note = ""; // Reset form fields after insert
        } else {
            $notice = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Retrieve all data from the table
$sql = "SELECT * FROM ".EN_TABLE_NAME;
$result = $conn->query($sql);

// Close the database connection
$conn->close();
?>

<?php if(!empty($notice)){ ?>
    <div id="setting-error-settings_updated" class="email-notice notice notice-success settings-error is-dismissible">
        <p><strong><?php echo $notice; ?></strong></p>
    </div>
<?php } ?>

<div class="row">
    <div class="column width-30">
        <h2>Create New Email Information</h2>
        <form method="POST">
            <input type="hidden" name="update_id" value="<?php echo isset($_GET['update']) ? $_GET['update'] : ''; ?>">
            
            <table class="form-table email-form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="woocommerce_store_address">Email</label>
                        </th>
                        <td class="forminp forminp-text">
                            <input name="email" 
                                id="email" 
                                type="email"  
                                value="<?php echo $email; ?>" 
                                required 
                                style="width: 100%;"
                                placeholder="info@modaboost.com">
                       </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="notes">Notes</label>
                        </th>
                        <td class="forminp forminp-textarea">
                            <!--
                            <textarea name="notes" 
                            id="notes" 
                            style="min-width: 100%; height: 75px;" 
                            class="" 
                            required 
                            placeholder=""><?php echo $notes; ?></textarea>
                            -->
                            <select name="notes" style="width: 100%;">
                                <?php
                                    echo "<option value='-'>Select Note</option>";
                                    $options = array(
                                                        'burnt'         => 'Burnt',
                                                        'unavailable'   => 'Unavailable',
                                                        'usable'        => 'Usable',
                                                    );
                                    foreach ($options as $key => $value) {
                                        echo "<option value='".$key."' ".selected( $notes, $key ).">".$value."</option>";
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top" class="">
                        <th scope="row" class="titledesc">Status</th>
                        <td class="forminp forminp-checkbox ">
                            <fieldset>
                                <legend class="screen-reader-text"><span>Account creation</span></legend>
                                <label for="status">
                                    <input name="status" 
                                    id="status" 
                                    type="checkbox" 
                                    class="" 
                                    value="active" 
                                    <?php checked( $status, 'active' ); ?>>Is Email Active? 
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p class="submit">
                <button name="save" class="button-primary woocommerce-save-button" type="submit" value="<?php echo isset($_GET['update']) ? 'Update' : 'Save'; ?>"><?php echo isset($_GET['update']) ? 'Update' : 'Save'; ?></button>
            </p>
        </form>
    </div>

    <div class="column width-70">
        <h2>Email Information</h2>
        <table id="email-main-tab" class="display" style="width:100%" >
            <thead>
                <tr>
                    <th>Sr No</th>
                    <th>Email</th>
                    <th>Date Added</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>
                        Check All 
                        <input type="checkbox" class='checkall' id='checkall'>
                        <input type="button" id='delete_record' value='Delete' >
                        <input type="button" id='edit_record' value='Edit' >
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $srno = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr class='".$row['notes']."'>";
                        echo "<td>" . $srno . "</td>";
                        echo "<td>" . $row['email'] . "</td>";
                        echo "<td>" . $row['time'] . "</td>";
                        echo "<td>" . ucfirst($row['notes']) . "</td>";
                        echo "<td><label class='switch'>
                                      <input type='checkbox' data-id='".$row['id']."' value='active' ".checked($row['status'], 'active', false).">
                                      <span class='slider round'></span>
                                    </label></td>";
                        echo "<td class='action-buttons'>";
                        echo "<a class='edit-button' href='" . admin_url("admin.php?page=email-note-plugin&update=" . $row['id']) . "'><button>Edit</button></a>";
                        echo "<a class='remove-button' href='" . admin_url("admin.php?page=email-note-plugin&delete=" . $row['id']) . "' onclick='return confirm(\"Are you sure?\")'><button>Remove</button></a>";
                        echo "</td>";
                        echo "<td><input type='checkbox' class='delete_check' id='delcheck_".$row['id']."' onclick='checkcheckbox();' value='".$row['id']."'></td>";
                        echo "</tr>";

                        $srno++;
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>