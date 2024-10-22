<?php
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

// Delete functionality
if (isset($_POST['delete_uid'])) {
    $delete_uid = $_POST['delete_uid'];

    // Query to delete user record
    $query = "DELETE FROM users WHERE uid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $delete_uid);

    if ($stmt->execute()) {
        // Deletion successful
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>"; // Include SweetAlerts library
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The user has been deleted successfully.',
                        icon: 'success'
                    }).then(() => {
                        window.location.href = 'admin_users.php?aid=$aid';
                    });
                });
              </script>";
    } else {
        // Deletion failed
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>"; // Include SweetAlerts library
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete user.',
                        icon: 'error'
                    });
                });
              </script>";
    }
    $stmt->close();
}

// Update status functionality
if (isset($_POST['action']) && isset($_POST['checked_uids'])) {
    $action = $_POST['action'];
    $checked_uids = explode(',', $_POST['checked_uids']);
    $status = ($action == 'activate') ? 'Active' : 'Disabled';

    // Check if checked_uids is an array and not empty
    if (is_array($checked_uids) && count($checked_uids) > 0) {
        // Prepare the placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($checked_uids), '?'));

        // Query to update the status of selected users
        $query = "UPDATE users SET status = ? WHERE uid IN ($placeholders)";
        $stmt = $mysqli->prepare($query);

        // Bind parameters (status first, followed by each uid)
        $types = str_repeat('i', count($checked_uids));
        $stmt->bind_param("s" . $types, $status, ...$checked_uids);

        if ($stmt->execute()) {
            // Update successful
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>"; // Include SweetAlerts library
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            title: 'Success!',
                            text: 'The status has been updated successfully.',
                            icon: 'success'
                        }).then(() => {
                            window.location.href = 'admin_users.php?aid=$aid';
                        });
                    });
                  </script>";
        } else {
            // Update failed
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>"; // Include SweetAlerts library
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to update status.',
                            icon: 'error'
                        });
                    });
                  </script>";
        }
        $stmt->close();
    } else {
        // Handle case where checked_uids is not an array or is empty
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>"; // Include SweetAlerts library
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Error!',
                        text: 'No users selected.',
                        icon: 'error'
                    });
                });
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Accounts</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_srch.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
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
<a href="admin_users.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Accounts</a>
<a href="admin_attd.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Library Logs</a>
<a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">User Statistics</a>
<a href="admin_wres.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Walk-in-Borrow</a>
<a href="admin_preq.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Pending Requests</a>
<a href="admin_brel.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Borrowed Books</a>
<a href="admin_ob.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Overdue Books</a>
<div class="sidebar-item dropdown">
            <a href="#" class="dropdown-link" onmouseover="toggleDropdown(event)">Inventory</a>
            <div class="dropdown-content">
                <a href="bk_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Books</a>
                <a href="admin_asts_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Assets</a>
            </div>
        </div>
    <a href="login.php" class="sidebar-item logout-btn">Logout</a>
</div>

<div class="content">
    <nav class="secondary-navbar">
        <a href="admin_srch.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Search Librarian</a>
        <a href="admin_users.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Search Users</a>
    </nav>
</div>

<div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>
    
<div class="content-container">
    <div class="search-bar">
    <h1 style="color: black;">Search User Accounts</h1>
        <input type="text" id="searchInput" placeholder="Search by Info or Username...">
        <select id="userTypeFilter">
            <option value="">All User Types</option>
            <option value="Student">Student</option>
            <option value="Faculty">Faculty</option>
            <option value="Staff">Staff</option>
        </select>
        <select id="statusFilter">
            <option value="">All Statuses</option>
            <option value="Active">Active</option>
            <option value="Pending">Pending</option>
            <option value="Disabled">Disabled</option>
        </select>
        <button type="button" onclick="applyFilters()">Search</button>
        <button type="button" onclick="clearFilters()">Clear</button>
    </div>
    <a href="admin_adu.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="add-lib"><i class='fas fa-plus'></i> Add User</a>

    <div class="table-actions">
        <button type="button" onclick="checkAll()">Check All</button>
        <button type="button" onclick="uncheckAll()">Uncheck All</button>
    </div>

    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th>#</th>
                <th>User Info</th>
                <th>ID #</th>
                <th>Contact #</th>
                <th>Username</th>
                <th>User Type</th>
                <th>Status</th>
                <th colspan="2">Actions:</th>
                <th>Check</th>
            </tr>
        </thead>
        <tbody id="userTableBody">
            <?php
            // Query to fetch all user records
            $query = "SELECT * FROM users";
            $result = $mysqli->query($query);

            // Counter for the first column
            $counter = 1;

            // Loop through each row
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                // Display the counter
                echo "<td>" . $counter++ . "</td>";
                echo "<td>" . $row['info'] . "</td>";
                echo "<td>" . $row['idnum'] . "</td>";
                echo "<td>" . $row['contact'] . "</td>";
                echo "<td>" . $row['username'] . "</td>";
                echo "<td>" . $row['user_type'] . "</td>";
                echo "<td>" . $row['status'] . "</td>";
                // Add edit and delete buttons
                echo '<td style="text-align:center;">';
                echo '<button class="edit-btn" onclick="editUser(' . $row['uid'] . ',' . $aid . ')"><i class="fas fa-edit"></i></button>';
                echo '</td>';
                echo '<td style="text-align:center;">';
                echo '<button class="delete-btn" onclick="deleteUser(' . $row['uid'] . ')"><i class="fas fa-trash-alt"></i></button>';
                echo '</td>';
                // Add a checkbox
                echo '<td style="text-align:center;">';
                echo '<input type="checkbox" name="user_check[]" value="' . $row['uid'] . '">';
                echo '</td>';
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="status-actions">
        <button type="button" onclick="updateStatus('activate')">Activate</button>
        <button type="button" id="disable" onclick="updateStatus('disable')">Disable</button>
    </div>
</div>

<form id="editForm" action="admin_ued.php" method="post">
    <input type="hidden" name="selected_uid" id="selected_uid">
    <input type="hidden" name="current_aid" id="current_aid">
</form>

<!-- Form for delete -->
<form id="deleteForm" method="post">
    <input type="hidden" name="delete_uid" id="delete_uid">
</form>

<!-- Form for status update -->
<form id="statusForm" method="post">
    <input type="hidden" name="action" id="statusAction">
    <input type="hidden" name="checked_uids" id="checkedUids">
</form>

<script>
function editUser(selectedUid, currentAid) {
    window.location.href = 'admin_ued.php?uid=' + selectedUid + '&aid=' + currentAid;
}

function deleteUser(deleteUid) {
    Swal.fire({
        title: 'Are you sure you want to delete this user?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_uid').value = deleteUid;
            document.getElementById('deleteForm').submit();
        }
    });
}

// Function to check all checkboxes
function checkAll() {
    var checkboxes = document.getElementsByName('user_check[]');
    for (var checkbox of checkboxes) {
        checkbox.checked = true;
    }
}

// Function to uncheck all checkboxes
function uncheckAll() {
    var checkboxes = document.getElementsByName('user_check[]');
    for (var checkbox of checkboxes) {
        checkbox.checked = false;
    }
}

// Function to apply filters
function applyFilters() {
    var input = document.getElementById("searchInput").value.toLowerCase();
    var userType = document.getElementById("userTypeFilter").value;
    var status = document.getElementById("statusFilter").value;
    var tableBody = document.getElementById("userTableBody");
    var rows = tableBody.getElementsByTagName("tr");

    for (var i = 0; i < rows.length; i++) {
        var info = rows[i].getElementsByTagName("td")[1].textContent.toLowerCase();
        var username = rows[i].getElementsByTagName("td")[3].textContent.toLowerCase();
        var type = rows[i].getElementsByTagName("td")[4].textContent;
        var rowStatus = rows[i].getElementsByTagName("td")[5].textContent;

        if ((info.includes(input) || username.includes(input)) && (userType === "" || type === userType) && (status === "" || rowStatus === status)) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }
    }
}

// Function to clear filters
function clearFilters() {
    document.getElementById("searchInput").value = "";
    document.getElementById("userTypeFilter").value = "";
    document.getElementById("statusFilter").value = "";
    applyFilters();
}

// Function to update status
function updateStatus(action) {
    var checkboxes = document.getElementsByName('user_check[]');
    var checkedUids = [];

    for (var checkbox of checkboxes) {
        if (checkbox.checked) {
            checkedUids.push(checkbox.value);
        }
    }

    if (checkedUids.length === 0) {
        Swal.fire({
            title: 'Error!',
            text: 'No user selected.',
            icon: 'error'
        });
        return;
    }

    document.getElementById('statusAction').value = action;
    document.getElementById('checkedUids').value = checkedUids.join(',');
    document.getElementById('statusForm').submit();
}


// Dropdown script
function toggleDropdown(event) {
    event.preventDefault();
    var dropdownContent = event.target.nextElementSibling;
    dropdownContent.classList.toggle('show');
}

window.onclick = function(event) {
    if (!event.target.matches('.dropdown-link')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
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
</body>
</html>
