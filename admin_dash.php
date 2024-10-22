<?php
function checkAdminSession() {
    if (!isset($_GET['aid']) || empty($_GET['aid'])) {
        header("Location: admin_login.php");
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

// Get the current date
$currentDate = date("m-d-Y");

// Prepare and execute SQL query to count attendance
$query_attendance = "SELECT COUNT(*) AS attendance_count FROM chkin WHERE date = ?";
$stmt_attendance = $mysqli->prepare($query_attendance);
$stmt_attendance->bind_param("s", $currentDate);
$stmt_attendance->execute();
$result_attendance = $stmt_attendance->get_result();

// Initialize attendance count
$attendance_count = 0;

// Check if there are results
if ($result_attendance && $result_attendance->num_rows > 0) {
    $attendance_data = $result_attendance->fetch_assoc();
    $attendance_count = $attendance_data['attendance_count'];
}

// Free the result set
if ($result_attendance) {
    $result_attendance->free();
}

// Prepare and execute SQL query to count pending requests
$query_pending = "SELECT COUNT(*) AS pending_count FROM rsv WHERE status = 'Pending'";
$stmt_pending = $mysqli->prepare($query_pending);
$stmt_pending->execute();
$result_pending = $stmt_pending->get_result();

// Initialize pending count
$pending_count = 0;

// Check if there are results
if ($result_pending && $result_pending->num_rows > 0) {
    $pending_data = $result_pending->fetch_assoc();
    $pending_count = $pending_data['pending_count'];
}

// Free the result set
if ($result_pending) {
    $result_pending->free();
}

// Prepare and execute SQL query to count released books
$query_released = "SELECT COUNT(*) AS released_count FROM rsv WHERE status = 'Released'";
$stmt_returned = $mysqli->prepare($query_released);
$stmt_returned->execute();
$result_returned = $stmt_returned->get_result();

// Initialize returned count
$released_count = 0;

// Check if there are results
if ($result_returned && $result_returned->num_rows > 0) {
    $returned_data = $result_returned->fetch_assoc();
    $released_count = $returned_data['released_count'];
}

// Free the result set
if ($result_returned) {
    $result_returned->free();
}

// Query to count total books in inventory
$query_total_books = "SELECT COUNT(*) AS total_books FROM inventory";
$result_total_books = $mysqli->query($query_total_books);

// Initialize total books count
$totalBooksCount = 0;

// Check if the query executed successfully
if ($result_total_books) {
    $total_books_data = $result_total_books->fetch_assoc();
    $totalBooksCount = $total_books_data['total_books'];
}

// Prepare and execute SQL query to count overdued books
$query_overdued = "SELECT COUNT(*) AS overdued_count FROM rsv WHERE status = 'Overdue'";
$stmt_overdued = $mysqli->prepare($query_overdued);
$stmt_overdued->execute();
$result_overdued = $stmt_overdued->get_result();

// Initialize overdued count
$overdued_count = 0;

// Check if there are results
if ($result_overdued && $result_overdued->num_rows > 0) {
    $overdued_data = $result_overdued->fetch_assoc();
    $overdued_count = $overdued_data['overdued_count'];
}

// Free the result set
if ($result_overdued) {
    $result_overdued->free();
}

// Close the statement
$stmt_overdued->close();

// Query to count total assets in assets table
$query_total_assets = "SELECT COUNT(*) AS total_assets FROM assets";
$result_total_assets = $mysqli->query($query_total_assets);

// Initialize total assets count
$totalAssetsCount = 0;

// Check if the query executed successfully
if ($result_total_assets) {
    $total_assets_data = $result_total_assets->fetch_assoc();
    $totalAssetsCount = $total_assets_data['total_assets'];
}

// Prepare and execute SQL query to count total PDFs
$query_total_pdfs = "SELECT COUNT(*) AS total_pdfs FROM pdf";
$result_total_pdfs = $mysqli->query($query_total_pdfs);

// Initialize total PDFs count
$totalPdfsCount = 0;

// Check if the query executed successfully
if ($result_total_pdfs) {
    $total_pdfs_data = $result_total_pdfs->fetch_assoc();
    $totalPdfsCount = $total_pdfs_data['total_pdfs'];
}

// Free the result set
if ($result_total_pdfs) {
    $result_total_pdfs->free();
}

// Query to count users with status 'Active'
$query_active_users = "SELECT COUNT(*) AS active_count FROM users WHERE status = 'Active'";
$result_active_users = $mysqli->query($query_active_users);

// Initialize active users count
$activeUsersCount = 0;

if ($result_active_users) {
    $active_users_data = $result_active_users->fetch_assoc();
    $activeUsersCount = $active_users_data['active_count'];
}

// Free the result set
if ($result_active_users) {
    $result_active_users->free();
}

// Query to count users with status 'Pending'
$query_pending_users = "SELECT COUNT(*) AS pending_users_count FROM users WHERE status = 'Pending'";
$result_pending_users = $mysqli->query($query_pending_users);

// Initialize pending users count
$pendingUsersCount = 0;

if ($result_pending_users) {
    $pending_users_data = $result_pending_users->fetch_assoc();
    $pendingUsersCount = $pending_users_data['pending_users_count'];
}

// Free the result set
if ($result_pending_users) {
    $result_pending_users->free();
}

// Query to count users with status 'Disabled'
$query_disabled_users = "SELECT COUNT(*) AS disabled_count FROM users WHERE status = 'Disabled'";
$result_disabled_users = $mysqli->query($query_disabled_users);

// Initialize disabled users count
$disabledUsersCount = 0;

if ($result_disabled_users) {
    $disabled_users_data = $result_disabled_users->fetch_assoc();
    $disabledUsersCount = $disabled_users_data['disabled_count'];
}

// Free the result set
if ($result_disabled_users) {
    $result_disabled_users->free();
}

// Query to count librarian accounts
$query_librarian_count = "SELECT COUNT(*) AS librarian_count FROM libr";
$result_librarian_count = $mysqli->query($query_librarian_count);

// Initialize librarian count
$librarianCount = 0;

if ($result_librarian_count) {
    $librarian_data = $result_librarian_count->fetch_assoc();
    $librarianCount = $librarian_data['librarian_count'];
}

// Free the result set
if ($result_librarian_count) {
    $result_librarian_count->free();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_dash.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Add jQuery library -->
    <a href="https://www.flaticon.com/free-icons/2" title="2 icons" hidden>2 icons created by iconsmind - Flaticon</a>
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
        <a href="admin_dash.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Dashboard</a>
        <a href="admin_pf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Profile</a>
        <a href="admin_srch.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Accounts</a>
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
        <p>EasyLib: Library User Experience and Management Through Integrated Monitoring Systems</p>
    </nav>
</div>

    <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
        <div class="box-container">

        
        <div class="box attendance"> <a style="text-decoration:none;" href="admin_attd.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>">
                <div class="box-content">
            <div class="box-icon">
                <img src="css/pics/attendance.png" alt="Attendance">
            </div>
            <div class="box-text">
                <div><span style="font-weight:bold; font-size: 30px;"><?php echo $attendance_count; ?></span> <br>Library Attendance</div>
            </div>
            </div>
        </a>
        </div>

        <div class="box librarian"> 
    <a style="text-decoration:none;" href="admin_srch.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>">
        <div class="box-content">
            <div class="box-icon">
                <img src="css/pics/librarian.png" alt="Librarian Accounts">
            </div>
            <div class="box-text">
                <div><span style="font-weight:bold; font-size: 30px;"><?php echo $librarianCount; ?></span> <br> Librarian Accounts</div>
            </div>
        </div>
    </a>
</div>

    <div class="box active-users"> <a style="text-decoration:none;" href="admin_users.php?status=Active<?php if(isset($aid)) echo '&aid=' . $aid; ?>">
    <div class="box-content">
        <div class="box-icon">
            <img src="css/pics/active.png" alt="Active Users">
        </div>
        <div class="box-text">
            <div><span style="font-weight:bold; font-size: 30px;"><?php echo $activeUsersCount; ?></span> <br> Users Activated </div>
        </div>
    </div></a>
</div>

<div class="box pending-users"> <a style="text-decoration:none;" href="admin_users.php?status=Pending<?php if(isset($aid)) echo '&aid=' . $aid; ?>">
    <div class="box-content">
        <div class="box-icon">
            <img src="css/pics/pending.png" alt="Pending Users">
        </div>
        <div class="box-text">
            <div><span style="font-weight:bold; font-size: 30px;"><?php echo $pendingUsersCount; ?></span> <br> Pending Users</div>
        </div>
    </div>
</a>
</div>

<div class="box disabled-users"> <a style="text-decoration:none;" href="admin_users.php?status=Disabled<?php if(isset($aid)) echo '&aid=' . $aid; ?>">
    <div class="box-content">
        <div class="box-icon">
            <img src="css/pics/disabled.png" alt="Disabled Users">
        </div>
        <div class="box-text">
            <div><span style="font-weight:bold; font-size: 30px;"><?php echo $disabledUsersCount; ?></span> <br> Disabled Users</div>
        </div>
    </div>
</a>
</div>

    <div class="box pending"> <a style="text-decoration:none;" href="admin_preq.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>">
        <div class="box-content">
        <div class="box-icon">
            <img src="css/pics/information.png" alt="Pending">
        </div>
        <div class="box-text">
            <div><span id="pending-count" style="font-weight:bold; font-size: 30px;"><?php echo $pending_count; ?></span> <br> Pending Requests</div>
        </div>
        </div>
    </a>
    </div>

        <div class="box borrowed"> <a style="text-decoration:none;" href="admin_bret.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>">
            <div class="box-content">
            <div class="box-icon">
                <img src="css/pics/read.png" alt="Borrowed">
            </div>
            <div class="box-text">
                <div><span style="font-weight:bold; font-size: 30px;"><?php echo $released_count; ?></span> <br> Books Released</div>
            </div>
            </div>
        </a>
        </div>

        <div class="box overdued"> <a style="text-decoration:none;" href="admin_ob.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>">
            <div class="box-content">
            <div class="box-icon">
                <img src="css/pics/questions.png" alt="Overdue">
            </div>
            <div class="box-text">
                <div><span style="font-weight:bold; font-size: 30px;"><?php echo $overdued_count; ?></span> <br> Overdue Books</div>
            </div>
            </div></a>
        </div>

<div class="box total-books"> <a style="text-decoration:none;" href="bk_inv.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>">
    <div class="box-content">
        <div class="box-icon">
            <img src="css/pics/books.png" alt="Books">
            </div>
        <div class="box-text">
            <div><span style="font-weight:bold; font-size: 30px;"><?php echo $totalBooksCount; ?></span> <br> Total Books Registered</div>
        </div>
    </div>
</a>
</div>

<div class="box assets"> <a style="text-decoration:none;" href="admin_asts_inv.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>">
        <div class="box-content">
        <div class="box-icon">
            <img src="css/pics/asset.png" alt="Total Assets">
        </div>
        <div class="box-text">
            <div><span style="font-weight:bold; font-size: 30px;"><?php echo $totalAssetsCount; ?></span> <br>Total Assets</div>
        </div>
        </div>
    </a>
    </div>

    <div class="box pdf"> <a style="text-decoration:none;" href="admin_pdf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>">
        <div class="box-content margin-right:11%;">
        <div class="box-icon">
            <img src="css/pics/pdf.png" style="width:70px; height:65px;" alt="Total PDFs">
        </div>
        <div class="box-text">
                <div><span style="font-weight:bold; font-size: 30px;"><?php echo $totalPdfsCount; ?></span> <br>Total E-Books</div>
        </div>
        </div></a>
    </div>

    <!-- <div class="box pdf"> <a style="text-decoration:none;" href="admin_noise.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>">
        <div class="box-content margin-right:11%;">
        <div class="box-icon">
            <img src="css/pics/pdf.png" style="width:70px; height:65px;" alt="Total PDFs">
        </div>
        <div class="box-text">
                <div><span style="font-weight:bold; font-size: 30px;"><?php echo $totalPdfsCount; ?></span> <br>Noise Levels</div>
        </div>
        </div></a>
    </div> -->

        </div>
    </div>

 <!-- JavaScript code to update date and time -->
<script>
$(document).ready(function() {
    // Function to update time
    function updateTime() {
        var currentDate = new Date();
        var month = (currentDate.getMonth() + 1).toString().padStart(2, '0'); // Adding 1 to month since it's zero-based index
        var day = currentDate.getDate().toString().padStart(2, '0');
        var year = currentDate.getFullYear().toString();
        var dateString = month + '-' + day + '-' + year;
        var timeString = currentDate.toLocaleTimeString();
        $("#current-date").text(dateString);
        $("#current-time").text(timeString);
    }

// Function to check pending requests count and show alert if it increases
function checkPendingRequests() {
    // Send AJAX request to get pending requests count
    $.ajax({
        url: 'get_pending_count.php', // Change this to the correct PHP file
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            // Check if the current count is greater than the previous count
            var previousCount = sessionStorage.getItem('pendingCount') || 0;
            if (response.count > previousCount) {
                // Show the alert
                Swal.fire({
                    title: 'Alert!',
                    text: 'You have new Pending Requests',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            }
            // Store the current count in session storage for comparison
            sessionStorage.setItem('pendingCount', response.count);

            // Update the pending count displayed on the dashboard
            $('#pending-count').text(response.count);
        },
        error: function(xhr, status, error) {
            console.error("Error:", xhr.responseText);
        }
    });
}
    // Call the function to update time immediately
    updateTime();
    // Update time every second
    setInterval(updateTime, 1000);

    // Call the function to check pending requests every 2 seconds
    setInterval(checkPendingRequests, 5000);
});


$(document).ready(function() {
    // Function to update time
    function updateTime() {
        var currentDate = new Date();
        var month = (currentDate.getMonth() + 1).toString().padStart(2, '0'); // Adding 1 to month since it's zero-based index
        var day = currentDate.getDate().toString().padStart(2, '0');
        var year = currentDate.getFullYear().toString();
        var dateString = month + '-' + day + '-' + year;
        var timeString = currentDate.toLocaleTimeString();
        $("#current-date").text(dateString);
        $("#current-time").text(timeString);
    }

    // Variable to keep track of whether the alert has been shown
    let alertShown = false;

// Function to check overdue books
function checkOverdueBooks() {
    // Send AJAX request to get overdue books
    $.ajax({
        url: 'get_overdue_books.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log("Response received:", response); // Log the response for debugging
            // Check if there are overdue books and the alert has not been shown
            if (response.length > 0 && !alertShown) {
                var bookList = response.map(book => book.title + ' (Due Date: ' + book.due_date + ')').join(', ');
                Swal.fire({
                    title: 'Alert!',
                    text: 'The following books are overdue: ' + bookList,
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Extract book IDs from the response
                        var bookIDs = response.map(book => book.rid);
                        // Send AJAX request to update status to "Overdue"
                        $.ajax({
                            url: 'update_status.php',
                            type: 'POST',
                            dataType: 'json',
                            data: { bookIDs: bookIDs },
                            success: function(updateResponse) {
                                console.log("Status updated successfully:", updateResponse); // Log the response for debugging
                                // Update the status to prevent future alerts
                                alertShown = true;
                                // Reload the page
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error("Error updating status:", xhr.responseText);
                            }
                        });
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error:", xhr.responseText);
        }
    });
}

    // Call the function to update time immediately
    updateTime();
    // Update time every second
    setInterval(updateTime, 1000);

    // Call the function to check overdue books every 5 seconds
    setInterval(checkOverdueBooks, 5000);
});

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
