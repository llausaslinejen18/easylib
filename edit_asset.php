<?php
// Include database connection
include 'config.php';

// Check if 'aid' parameter is present in the URL
if (isset($_GET['aid'])) {
    $aid = $_GET['aid'];
    // Query to fetch the username corresponding to the aid
    $query = "SELECT username FROM admin WHERE aid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $aid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $admin_username = $admin['username'];
        // Display the admin username in the sidebar
        $admin_username_display = $admin_username;
    } else {
        // Display a default message if admin username is not found
        $admin_username_display = "Username";
    }
    // Close statement
    $stmt->close();
}

// Check if 'asid' parameter is present in the URL
if (isset($_GET['asid'])) {
    $asid = $_GET['asid'];
    // Query to fetch the asset data corresponding to the asid
    $query = "SELECT * FROM assets WHERE as_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $asid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $asset = $result->fetch_assoc();
        // Assign asset data to variables for display
        $asset_name = $asset['as_name'];
        $mod_num = $asset['mod_num'];
        $ser_num = $asset['ser_num'];
        $p_cost = $asset['p_cost'];
        $p_date = $asset['p_date'];
        $condition = $asset['cndtn'];
        $additional_info = $asset['add_info'];
        $added_by = $asset['added_by'];
    } else {
        // Redirect back to admin_asts_inv.php if asset data is not found
        header("Location: admin_asts_inv.php?aid=" . $aid);
        exit();
    }
    // Close statement
    $stmt->close();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract data from the form
    $asid = $_POST['asid'];
    $asset_name = $_POST['asset_name'];
    $mod_num = $_POST['mod_num'];
    $ser_num = $_POST['ser_num'];
    $p_cost = $_POST['p_cost'];
    $p_date = $_POST['p_date'];
    $condition = $_POST['condition'];
    $additional_info = $_POST['additional_info'];
    $added_by = $_POST['added_by'];

    // Update the asset information in the database
    $query = "UPDATE assets SET as_name=?, mod_num=?, ser_num=?, p_cost=?, p_date=?, cndtn=?, add_info=?, added_by=? WHERE as_id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssssssssi", $asset_name, $mod_num, $ser_num, $p_cost, $p_date, $condition, $additional_info, $added_by, $asid);
    $stmt->execute();
    $stmt->close();

    // Redirect back to admin_asts_inv.php
    header("Location: admin_asts_inv.php?aid=" . $aid);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Asset Information</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/edit.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
</head>
<body class="bg">
<nav class="navbar">
    <div class="navbar-container">
        <img src="css/pics/logop.png" alt="Logo" class="logo">
    </div>
</nav>

<div class="wrap">
    <div class="form-container">
        <h2 style="text-align:center">Edit Asset Information</h2>
        <!-- Form to edit an asset -->
        <form id="editForm" method="POST">
            <input type="hidden" name="asid" value="<?php echo $asid; ?>">
            <label for="asset_name">Asset Name:</label>
            <input type="text" id="asset_name" name="asset_name" placeholder="Enter Asset name" required value="<?php echo $asset_name; ?>"><br>

            <label for="mod_num">Model Number:</label>
            <input type="text" id="mod_num" name="mod_num" placeholder="Enter Model Number" required value="<?php echo $mod_num; ?>"><br>

            <label for="ser_num">Serial Number:</label>
            <input type="text" id="ser_num" name="ser_num" placeholder="Enter Serial Number" required value="<?php echo $ser_num; ?>"><br>

            <label for="p_cost">Purchase Cost (Pesos):</label>
            <input type="text" id="p_cost" name="p_cost" placeholder="Enter Purchase Cost" required value="<?php echo $p_cost; ?>"><br>

            <label for="p_date">Purchase Date:</label>
            <input type="date" id="p_date" name="p_date" placeholder="Enter Purchase Date" required value="<?php echo $p_date; ?>"><br>

            <label for="condition">Asset Condition:</label>
            <input type="text" id="condition" name="condition" placeholder="Enter Asset Condition" required value="<?php echo $condition; ?>"><br>

            <label for="additional_info">Additional Information:</label>
            <input type="text" id="additional_info" name="additional_info" placeholder="Enter additional information" value="<?php echo $additional_info; ?>"><br>

            <label for="added_by">Added By:</label>
            <input type="text" id="added_by" name="added_by" placeholder="Enter added by" value="<?php echo $added_by; ?>"><br>

            <div class="button-container" style="margin-top:3%;">
                <!-- Save button -->
                <button id="saveBtn" style="width: 100px;" type="button">Save</button>
                <!-- Cancel button -->
                <a href="admin_asts_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>"><button style="background-color:red; width: 100px;" type="button">Cancel</button></a>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('saveBtn').addEventListener('click', function() {
        Swal.fire({
            title:'Success!',
            text: "Asset Information Updated!",
            icon: "success",
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('editForm').submit();
            }
        })
    });
</script>

</body>
</html>
