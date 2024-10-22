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
if(isset($_GET['aid'])) {
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
if(isset($_POST['delete_aid'])) {
    $delete_aid = $_POST['delete_aid'];
    
    // Query to delete librarian record
    $query = "DELETE FROM libr WHERE aid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $delete_aid);
    
    if($stmt->execute()) {
        // Deletion successful
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>"; // Include SweetAlerts library
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The account has been deleted successfully.',
                        icon: 'success'
                    }).then(() => {
                        window.location.href = 'admin_srch.php?aid=$aid';
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
                        text: 'Failed to delete librarian.',
                        icon: 'error'
                    });
                });
              </script>";
    }
    $stmt->close();
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
    <a href="admin_dash.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Dashboard</a>
    <a href="admin_pf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Profile</a>
    <a href="admin_srch.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Accounts</a>
    <a href="admin_attd.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Library Logs</a>
    <a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">User Statistics</a>
    <a href="admin_wres.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Walk-in-Borrow</a>
    <a href="admin_preq.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Pending Requests</a>
    <a href="admin_brel.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Borrowed Books</a>
    <a href="admin_ob.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Overdue Books</a>
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
        <a href="admin_srch.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Search Librarian</a>
        <a href="admin_users.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Search Users</a>
    </nav>
</div>

<div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>
    
<div class="content-container">
    <div class="search-bar">
    <h1 style="color: black;">Search Accounts</h1>
        <input type="text" placeholder="Search...">
        <button type="submit">Search</button>
    </div>
    <a href="admin_sacc.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="add-lib"><i class='fas fa-plus'></i> Add Librarian</a>

    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Contact #</th>
                <th>Username</th>
                <th hidden>Password</th>
                <th colspan="2">Actions:</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query to fetch all librarian records
            $query = "SELECT * FROM libr";
            $result = $mysqli->query($query);

            // Counter for the first column
            $counter = 1;

            // Loop through each row
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                // Display the counter
                echo "<td hidden>" . $row['aid'] . "</td>";
                echo "<td>" . $counter++ . "</td>";
                echo "<td>" . $row['name'] . "</td>";
                echo "<td>" . $row['contact'] . "</td>";
                echo "<td>" . $row['username'] . "</td>";
                echo "<td hidden>" . $row['password'] . "</td>";
                // Add edit and delete buttons
                echo '<td style="text-align:center;">';
                echo '<button class="edit-btn" onclick="editAdmin(' . $row['aid'] . ',' . $aid . ')"><i class="fas fa-edit"></i></button>';
                echo '</td>';
                echo '<td style="text-align:center;">';
                echo '<button class="delete-btn" onclick="deleteAdmin(' . $row['aid'] . ')"><i class="fas fa-trash-alt"></i></button>';
                echo '</td>';
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>


<form id="editForm" action="admin_edit.php" method="post">
    <input type="hidden" name="selected_aid" id="selected_aid">
    <input type="hidden" name="current_aid" id="current_aid">
</form>

<!-- Form for delete -->
<form id="deleteForm" method="post">
    <input type="hidden" name="delete_aid" id="delete_aid">
</form>

<script>
function editAdmin(selectedAid, currentAid) {
    document.getElementById('selected_aid').value = selectedAid;
    document.getElementById('current_aid').value = currentAid;
    document.getElementById('editForm').submit();
}


function deleteAdmin(deleteAid) {
    Swal.fire({
        title: 'Are you sure you want to delete this admin?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_aid').value = deleteAid;
            document.getElementById('deleteForm').submit();
        }
    });
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
