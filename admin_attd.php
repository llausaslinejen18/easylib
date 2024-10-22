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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Logs</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_srch.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
        <a href="admin_attd.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Library Logs</a>
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
            <a href="admin_attd.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Attendance</a>
            <a href="liblogs.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">User Logs</a>
            <a href="admin_aliblogs.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Archived User Logs</a>
        </nav>
    </div>

    <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
        <div class="search-bar">
            <h1 style="color: black;">Library Users Today:</h1>
            <button type="button" onclick="addUserPopup()"><i class='fas fa-plus'></i> New Library User</button>
        </div>
        <?php
        // Get the current date
        $currentDate = date("m-d-Y");

        // Include database connection configuration
        include 'config.php';

        // Prepare and execute SQL query
        $query = "SELECT * FROM chkin WHERE date = '$currentDate'";
        $result = $mysqli->query($query);

        // Check if there are results
        if ($result && $result->num_rows > 0) {
            echo '<table id="dataTable">';
            echo '<thead>';
            echo '<tr><th>#</th><th>User Info</th><th>ID Number</th><th>User Type</th><th>Date</th><th>Time In</th><th>Time Out</th><th>Purpose</th><th>Edit:</th></tr>';
            echo '</thead>';
            echo '<tbody id="dataTableBody">'; // Added id to tbody

            // Loop through all rows and fetch the data
            $counter = 0; // Initialize counter
            while ($row = $result->fetch_assoc()) {
                $counter++; // Increment counter
                echo '<tr>';
                echo '<td class="counter">' . $counter . '</td>'; // Display counter
                echo '<td hidden>' . $row['id'] . '</td>';
                echo '<td>' . $row['info'] . '</td>';
                echo '<td>' . $row['idnum'] . '</td>';
                echo '<td>' . $row['user_type'] . '</td>';
                echo '<td>' . $row['date'] . '</td>';
                echo '<td>' . $row['timein'] . '</td>';
                echo '<td>' . ($row['timeout'] ? $row['timeout'] : 'N/A') . '</td>';
                echo '<td>' . $row['purpose'] . '</td>';
                echo '<td style="text-align: center;">';
                echo '<button class="edit-btn" onclick="editBook(' . $row['id'] . ')"><i class="fas fa-edit"></i></button>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No data found for the current date.</p>';
        }

        // Free the result set
        if ($result) {
            $result->free();
        }

        // Close the database connection
        $mysqli->close();
        ?>
    </div>
<script>
function addUserPopup() {
    Swal.fire({
        title: 'Add Library User',
        html: `<input type="text" id="userInfo" class="swal2-input" placeholder="User Info">
               <select id="userType" class="swal2-input">
                   <option value="" disabled selected>User Type</option>
                   <option value="Student">Student</option>
                   <option value="Faculty">Faculty</option>
                   <option value="Staff">Staff</option>
                   <option value="Visitor">Visitor</option>
               </select>
               <select id="purpose" class="swal2-input">
                   <option value="" disabled selected>Purpose</option>
                   <option value="Study">Study</option>
                   <option value="Research">Research</option>
                   <option value="Printing">Printing</option>
                   <option value="Clearance">Clearance</option>
                   <option value="Borrow">Borrow</option>
                   <option value="Return">Return</option>
               </select>`,
        focusConfirm: false,
        preConfirm: () => {
            const userInfo = Swal.getPopup().querySelector('#userInfo').value;
            const userType = Swal.getPopup().querySelector('#userType').value;
            const purpose = Swal.getPopup().querySelector('#purpose').value;
            if (!userInfo || !userType || !purpose) {
                Swal.showValidationMessage(`Please enter all fields`);
            }
            return { userInfo: userInfo, userType: userType, purpose: purpose };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { userInfo, userType, purpose } = result.value;
            $.ajax({
                url: 'add_chkin.php',
                type: 'POST',
                data: {
                    userInfo: userInfo,
                    userType: userType,
                    purpose: purpose
                },
                success: function(response) {
                    console.log(response); // Debugging line to check the response
                    if(response.trim() === 'success') {
                        Swal.fire('Saved!', 'Library user has been added.', 'success')
                        .then(() => {
                            // Reload the page to see the new data
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', 'There was a problem adding the user.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'There was a problem connecting to the server.', 'error');
                }
            });
        }
    });
}
</script>
    <!-- JavaScript code to update date and time -->
    <script>
        function updateTime() {
            var currentDate = new Date();
            var month = (currentDate.getMonth() + 1).toString().padStart(2, '0'); // Adding 1 to month since it's zero-based index
            var day = currentDate.getDate().toString().padStart(2, '0');
            var year = currentDate.getFullYear().toString();
            var dateString = month + '-' + day + '-' + year;
            var timeString = currentDate.toLocaleTimeString();
            document.getElementById("current-date").textContent = dateString;
            document.getElementById("current-time").textContent = timeString;
        }
        updateTime(); // Call the function to update time immediately
        setInterval(updateTime, 1000); // Update time every second
    </script>
<script>
    function editBook(id) {
        // Get the current URL
        var url = window.location.href;
        // Find the last occurrence of '/'
        var lastSlashIndex = url.lastIndexOf('/');
        // Extract the base URL
        var baseUrl = url.substring(0, lastSlashIndex);
        // Redirect to bk_edit.php with the parameters
        window.location.href = baseUrl + '/edit_logs.php?aid=<?php echo isset($aid) ? $aid : ''; ?>&id=' + id;
    }
</script>
<script>
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
</body>
</html>
