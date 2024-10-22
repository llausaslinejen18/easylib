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
?>
<?php
function getCurrentFileName() {
    return basename($_SERVER['PHP_SELF']);
}
$currentFile = getCurrentFileName();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_asset.css">
    <!-- Include SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="bg">
<div class="sidebar">
    <span style="margin-left: 25%;"><img src="css/pics/logop.png" alt="Logo" class="logo"></span>
        <?php
        // Check if $admin_username_display is set
        if(isset($admin_username_display)) {
            // Add spaces before the admin username to align it
            echo '<div class="hell">Admin: ' . $admin_username_display . '</span></div>';
        } else {
            // Display a default message if admin username is not found
            echo '<div>Admin: <br>Username</div>';
        }
        ?>
        <a href="admin_dash.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Dashboard</a>
        <a href="admin_pf.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Profile</a>
        <a href="admin_srch.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Accounts</a>
        <a href="admin_attd.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Library Logs</a>
        <a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">User Statistics</a>
        <a href="admin_wres.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Walk-in-Borrow</a>
        <a href="admin_preq.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Pending Requests</a>
        <a href="admin_brel.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Borrowed Books</a>
        <a href="admin_ob.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Overdue Books</a>
        <div class="sidebar-item dropdown <?php if (strpos($currentFile, 'admin_add_asts.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'show'; ?>">
            <a href="#" class="dropdown-link <?php if (strpos($currentFile, 'admin_add_asts.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'active'; ?>" onclick="toggleDropdown(event)">Inventory</a>
            <div class="dropdown-content <?php if (strpos($currentFile, 'admin_add_asts.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'show'; ?>">
                <a href="add_bk_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item <?php if ($currentFile == 'add_bk_inv.php') echo 'active'; ?>">Books</a>
                <a href="admin_add_asts.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item <?php if ($currentFile == 'admin_add_asts.php') echo 'active'; ?>">Assets</a>
            </div>
        </div>
        <a href="login.php" class="sidebar-item logout-btn">Logout</a>
    </div>

    <div class="content">
        <nav class="secondary-navbar">
            <a href="admin_add_asts.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Add</a>
        </nav>
    </div>

    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
        <div class="form-container">
            <center><h2>Add Asset to Inventory</h2></center>
            <!-- PHP code for handling form submission and displaying alerts -->
            <?php
            // Initialize alert message
            $alertMessage = '';

            // Check if form is submitted
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Retrieve form data
                $asset_name = $_POST['asset_name'];
                $mod_num = $_POST['mod_num'];
                $ser_num = $_POST['ser_num'];
                $p_cost = $_POST['p_cost'];
                $p_date = $_POST['p_date'];
                $condition = $_POST['condition'];
                $additional_info = $_POST['additional_info'];
                $count = intval($_POST['count']);

                // Prepare SQL insert statement
                $query = "INSERT INTO assets (as_name, mod_num, ser_num, p_cost, p_date, add_info, cndtn, added_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $mysqli->prepare($query);

                // Bind parameters
                $stmt->bind_param("ssssssss", $asset_name, $mod_num, $ser_num, $p_cost, $p_date, $additional_info, $condition, $admin_username);

                // Execute the statement for the number of times specified by count
                for ($i = 0; $i < $count; $i++) {
                    $stmt->execute();
                }

                // Check if insertion was successful
                if ($stmt->affected_rows > 0) {
                    $alertMessage = "Asset added successfully.";
                    // Show success message using SweetAlert
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: '{$alertMessage}',
                                });
                            });
                        </script>";
                } else {
                    $alertMessage = "Error adding asset: " . $stmt->error;
                    // Show error message using SweetAlert
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: '{$alertMessage}',
                                });
                            });
                        </script>";
                }

                // Close statement
                $stmt->close();
            }
            ?>
            <!-- Form to add an asset -->
            <form action="admin_add_asts.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" method="POST">
                <label for="asset_name"></label>
                <input type="text" id="asset_name" name="asset_name" placeholder="Enter Asset name" required><br>

                <label for="mod_num"></label>
                <input type="text" id="mod_num" name="mod_num" placeholder="Enter Model Number" required><br>

                <label for="ser_num"></label>
                <input type="text" id="ser_num" name="ser_num" placeholder="Enter Serial Number" required><br>

                <label for="p_cost"></label>
                <input type="text" id="p_cost" name="p_cost" placeholder="Enter Purchase Cost" required><br>

                <label for="p_date">Purchase Date:</label>
                <input type="date" id="p_date" name="p_date" placeholder="Enter Purchase Date" required><br>

                <label for="condition"></label>
                <input type="text" id="condition" name="condition" placeholder="Enter Asset Condition" required><br>

                <label for="count"></label>
                <input type="number" id="count" name="count" placeholder="Enter Number of Assets" required><br>

                <label for="additional_info"></label>
                <input type="text" id="additional_info" name="additional_info" placeholder="Enter additional information"><br>

                <div class="button-container" style="margin-top:3%;">
                    <!-- Add Asset button -->
                    <button style="width: 100px;" type="submit">Add Asset</button>
                    <!-- Cancel button -->
                    <a href="admin_asts_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>"><button style="width: 100px; background-color:red;" type="button">Cancel</button></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Include SweetAlert JavaScript library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function updateTime() {
            var currentDate = new Date();
            var month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
            var day = currentDate.getDate().toString().padStart(2, '0');
            var year = currentDate.getFullYear().toString();
            var dateString = month + '-' + day + '-' + year;
            var timeString = currentDate.toLocaleTimeString();
            document.getElementById("current-date").textContent = dateString;
            document.getElementById("current-time").textContent = timeString;
        }
        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>
</html>
