<?php
function checkAdminSession() {
    if (!isset($_GET['aid']) || empty($_GET['aid'])) {
        header("Location: login.php");
        exit;
    }
}

// Call the function at the top of your files
checkAdminSession();
?>

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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if 'delete_asid' parameter is present in the POST data
    if (isset($_POST['delete_asid'])) {
        // Delete the asset from the database
        $delete_asid = $_POST['delete_asid'];
        $delete_query = "DELETE FROM assets WHERE as_id = ?";
        $delete_stmt = $mysqli->prepare($delete_query);
        $delete_stmt->bind_param("i", $delete_asid);
        $delete_stmt->execute();
        $delete_stmt->close();

        // Redirect back to admin_asts_inv.php
        header("Location: admin_asts_inv.php?aid=" . $aid);
        exit();
    }
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
        $as_name = $asset['as_name'];
        $mod_num = $asset['mod_num'];
        $ser_num = $asset['ser_num'];
        $p_cost = $asset['p_cost'];
        $p_date = $asset['p_date'];
        $add_info = $asset['add_info'];
        $cndtn = $asset['cndtn'];
    } else {
        // Redirect back to admin_asts_inv.php if asset data is not found
        header("Location: admin_asts_inv.php?aid=" . $aid);
        exit();
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
    <title>Assets Inventory</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_srch.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        <a href="admin_dash.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Dashboard</a>
        <a href="admin_pf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Profile</a>
        <a href="admin_srch.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Accounts</a>
        <a href="admin_attd.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Library Logs</a>
        <a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">User Statistics</a>
        <a href="admin_wres.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Walk-in-Borrow</a>
        <a href="admin_preq.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Pending Requests</a>
        <a href="admin_brel.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Borrowed Books</a>
        <a href="admin_ob.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Overdue Books</a>
        <div class="sidebar-item dropdown <?php if (strpos($currentFile, 'admin_asts_inv.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'show'; ?>">
            <a href="#" class="dropdown-link <?php if (strpos($currentFile, 'admin_asts_inv.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'active'; ?>" onclick="toggleDropdown(event)">Inventory</a>
            <div class="dropdown-content <?php if (strpos($currentFile, 'admin_asts_inv.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'show'; ?>">
                <a href="bk_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item <?php if ($currentFile == 'add_bk_inv.php') echo 'active'; ?>">Books</a>
                <a href="admin_asts_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item <?php if ($currentFile == 'admin_asts_inv.php') echo 'active'; ?>">Assets</a>
            </div>
        </div>
        <a href="login.php" class="sidebar-item logout-btn">Logout</a>
    </div>

    <div class="content">
        <nav class="secondary-navbar">
            <a href="admin_asts_inv.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Assets</a>
        </nav>
    </div>

    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>
    
    <div class="content-container">
    <div class="search-bar">
    <h1>Assets Inventory</h1>
    <!-- Search Form -->
    <form action="admin_asts_inv.php" method="GET">
        <input type="hidden" name="aid" value="<?php echo isset($_GET['aid']) ? $_GET['aid'] : ''; ?>">

            <label for="searchName" class="label"></label>
            <input type="text" id="searchName" name="as_name" class="input" placeholder="Enter asset name..." value="<?php echo isset($_GET['as_name']) ? $_GET['as_name'] : ''; ?>">
            
            <label for="searchModel" class="label"></label>
            <input type="text" id="searchModel" name="mod_num" class="input" placeholder="Enter model number..." value="<?php echo isset($_GET['mod_num']) ? $_GET['mod_num'] : ''; ?>">
            
            <label for="searchSerial" class="label"></label>
            <input type="text" id="searchSerial" name="ser_num" class="input" placeholder="Enter serial number..." value="<?php echo isset($_GET['ser_num']) ? $_GET['ser_num'] : ''; ?>">
            
            <label for="searchCondition" class="label"></label>
            <input type="text" id="searchCondition" name="cndtn" class="input" placeholder="Enter asset condition..." value="<?php echo isset($_GET['cndtn']) ? $_GET['cndtn'] : ''; ?>">
            
            <label for="searchDate" class="label"></label>
            <input type="date" id="searchDate" name="p_date" class="input" placeholder="Enter purchase date..." value="<?php echo isset($_GET['p_date']) ? $_GET['p_date'] : ''; ?>">
            <button type="submit" style="margin-left:15px;">Search</button>
            <a href="admin_asts_inv.php<?php if(isset($_GET['aid'])) echo '?aid=' . $_GET['aid']; ?>" class="delete-btn">Clear</a> <!-- Added Clear button -->
    </form>
</div>
<a href="admin_add_asts.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="add-lib"><i class='fas fa-plus'></i> Add Asset(s)</a>
<br><br>
            <!-- Display Table -->
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Asset Name</th>
                        <th>Model Number</th>
                        <th>Serial Number</th>
                        <th>Purchase Cost</th>
                        <th>Purchase Date</th>
                        <th>Number of Units</th>
                        <th>Additional Info</th>
                        <th>Condition</th>
                        <th colspan="2">Actions:</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                include 'config.php';
                $query = "SELECT as_id, as_name, mod_num, ser_num, p_cost, p_date, add_info, cndtn, 
                          (SELECT COUNT(*) FROM assets a2 WHERE a2.as_name = a1.as_name AND a2.mod_num = a1.mod_num AND a2.ser_num = a1.ser_num AND a2.p_date = a1.p_date AND a2.cndtn = a1.cndtn) AS num_units 
                          FROM assets a1 WHERE 1";

                if (isset($_GET['as_name']) && !empty($_GET['as_name'])) {
                    $as_name = $_GET['as_name'];
                    $query .= " AND as_name LIKE '%$as_name%'";
                }
                if (isset($_GET['mod_num']) && !empty($_GET['mod_num'])) {
                    $mod_num = $_GET['mod_num'];
                    $query .= " AND mod_num LIKE '%$mod_num%'";
                }
                if (isset($_GET['ser_num']) && !empty($_GET['ser_num'])) {
                    $ser_num = $_GET['ser_num'];
                    $query .= " AND ser_num LIKE '%$ser_num%'";
                }
                if (isset($_GET['cndtn']) && !empty($_GET['cndtn'])) {
                    $cndtn = $_GET['cndtn'];
                    $query .= " AND cndtn LIKE '%$cndtn%'";
                }
                if (isset($_GET['p_date']) && !empty($_GET['p_date'])) {
                    $p_date = $_GET['p_date'];
                    $query .= " AND p_date = '$p_date'";
                }

                $result = $mysqli->query($query);

                // Initialize variables to store the last displayed row's values
                $last_row = [];

                if ($result && $result->num_rows > 0) {
                    $counter = 1;
                    while ($row = $result->fetch_assoc()) {
                        // Store current row values in an array
                        $current_row = [
                            'as_name' => $row['as_name'],
                            'mod_num' => $row['mod_num'],
                            'ser_num' => $row['ser_num'],
                            'p_cost' => $row['p_cost'],
                            'p_date' => $row['p_date'],
                            'cndtn' => $row['cndtn'] // Include condition in the comparison
                        ];

                        // Compare current row with the last displayed row
                        if ($current_row !== $last_row) {
                            echo '<tr>';
                            echo '<td>' . $counter++ . '</td>';
                            echo '<td>' . $row['as_name'] . '</td>';
                            echo '<td>' . $row['mod_num'] . '</td>';
                            echo '<td>' . $row['ser_num'] . '</td>';
                            echo '<td>' . $row['p_cost'] . '</td>';

                            // Convert date to "MM-DD-YYYY" format
                            $date = new DateTime($row['p_date']);
                            echo '<td>' . $date->format('m-d-Y') . '</td>';

                            echo '<td>' . $row['num_units'] . '</td>';
                            echo '<td>' . $row['add_info'] . '</td>';
                            echo '<td>'; // Start condition column
                            if ($row['cndtn'] == 'Good') {
                                echo '<span style="color: green;">Good</span>';
                            } elseif ($row['cndtn'] == 'Fair') {
                                echo '<span style="color: orange;">Fair</span>';
                            } elseif ($row['cndtn'] == 'Poor') {
                                echo '<span style="color: red;">Poor</span>';
                            } else {
                                echo  $row['cndtn'] ;
                            }
                            echo '</td>'; // End condition column
                            echo '<td>';
                            echo '<button class="edit-btn" onclick="editAsset(' . $row['as_id'] . ')"><i class="fas fa-edit"></i></button>';
                            echo '</td>';
                            echo '<td>';
                            echo '<form id="delete_form_' . $row['as_id'] . '" method="POST">';
                            echo '<input type="hidden" name="delete_asid" value="' . $row['as_id'] . '">';
                            echo '<button class="delete-btn" type="button" onclick="deleteAsset(' . $row['as_id'] . ')"><i class="fas fa-trash-alt"></i></button>';
                            echo '</form>';
                            echo '</td>';
                            echo '</tr>';

                            // Update last displayed row values
                            $last_row = $current_row;
                        }
                    }
                } else {
                    echo '<tr><td colspan="11">No data found matching the search criteria.</td></tr>';
                }

                if ($result) {
                    $result->free();
                }

                $mysqli->close();
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <br><br>
</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    // Function to handle asset deletion confirmation
    function deleteAsset(asid) {
        Swal.fire({
            title: 'Are you sure you want to delete this asset?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                    'Deleted!',
                    'The asset has been deleted successfully.',
                    'success'
                ).then(() => {
                    // Submit the form for deletion
                    document.getElementById('delete_form_' + asid).submit();
                });
            }
        });
    }

    function editAsset(asid) {
        // Get the current URL
        var url = window.location.href;
        // Find the last occurrence of '/'
        var lastSlashIndex = url.lastIndexOf('/');
        // Extract the base URL
        var baseUrl = url.substring(0, lastSlashIndex);
        // Redirect to asset_edit.php with the parameters
        window.location.href = baseUrl + '/edit_asset.php?aid=<?php echo isset($aid) ? $aid : ''; ?>&asid=' + asid;
    }
</script>
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
</html>
