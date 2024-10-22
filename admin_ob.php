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
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    <title>Overdued Books</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_srch.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <a href="admin_ob.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Overdue Books</a>
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
            <a href="admin_ob.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Overdue Books</a>
            <a href="admin_ol.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Overdue Logs</a>
        </nav>
    </div>
    <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
        <div class="search-bar">
            <h1>Overdue:</h1>
        </div>
            <table id="overdue-books-table">
    <thead>
        <tr>
            <th>#</th>
            <th>User Info</th>
            <th>Book Title</th>
            <th>Due Date</th>
            <th>Fines '₱'</th>
            <th>Settled</th>
            <th style="display: none;">RID</th>
            <th style="display: none;">UID</th>
            <th style="display: none;">BID</th>
        </tr>
    </thead>
    <tbody id="overdue-books-body-1">
    <?php
    // Query to fetch overdued books data
    $query = "SELECT r.rid, r.uid, r.bid, r.title, u.info, r.due_date FROM users u JOIN rsv r ON u.uid = r.uid WHERE r.status = 'Overdue'";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        $counter = 1;
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $counter . "</td>";
            echo "<td>" . $row["info"] . "</td>";
            echo "<td>" . $row["title"] . "</td>";
            echo "<td>" . $row["due_date"] . "</td>";
            echo "<td class='fines' style='text-align:center;'></td>";
            echo "<td style='text-align:center;'><button class='approve-btn' name='checkButton'><i class='fas fa-check'></i></button></td>";
            // Output hidden columns with RID, UID, BID, and Title
            echo "<td style='display: none;' class='rid'>" . $row["rid"] . "</td>";
            echo "<td style='display: none;' class='uid'>" . $row["uid"] . "</td>";
            echo "<td style='display: none;' class='bid'>" . $row["bid"] . "</td>";
            echo "</tr>";
            $counter++; // Increment counter after it's used
        }
    } else {
        echo "<tr><td colspan='7'>No  books found.</td></tr>";
    }
    ?>
    </tbody>
</table>

<div class="search-bar">
            <h1>Walk-In Overdue:</h1>
        </div>
        <table id="walkin-overdue-books-table">
    <thead>
        <tr>
            <th>#</th>
            <th>User Info</th>
            <th>Book Title</th>
            <th>Due Date</th>
            <th>Fines '₱'</th>
            <th>Settled</th>
            <th style="display: none;">RID</th>
            <th style="display: none;">UID</th>
            <th style="display: none;">BID</th>
        </tr>
    </thead>
    <tbody id="overdue-books-body-2">
    <?php
// Query to fetch overdue books data including the book title from the inventory table
$query = "SELECT r.rid, r.uid, r.bid, r.info, r.due_date, i.title
          FROM rsv r
          JOIN inventory i ON r.bid = i.bid
          WHERE r.status = 'Overdue' AND r.uid = 0";
$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    $counter = 1;
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $counter . "</td>";
        echo "<td>" . $row["info"] . "</td>";
        echo "<td>" . $row["title"] . "</td>";
        echo "<td>" . $row["due_date"] . "</td>";
        echo "<td class='fines' style='text-align:center;'></td>";
        echo "<td style='text-align:center;'><button class='approve-btn' name='checkButton'><i class='fas fa-check'></i></button></td>";
        // Output hidden columns with RID, UID, BID, and Title
        echo "<td style='display: none;' class='rid'>" . $row["rid"] . "</td>";
        echo "<td style='display: none;' class='uid'>" . $row["uid"] . "</td>";
        echo "<td style='display: none;' class='bid'>" . $row["bid"] . "</td>";
        echo "</tr>";
        $counter++; // Increment counter after it's used
    }
} else {
    echo "<tr><td colspan='7'>No overdue books found.</td></tr>";
}
?>
</tbody>
</table>

    </div>

<script>
    // Function to update time
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

    updateTime(); // Call the function to update time immediately
    setInterval(updateTime, 1000); // Update time every second
</script>
<script>
    // Calculate fines for overdue books in a specific table
function calculateFines(tableBodyId) {
    $('#' + tableBodyId + ' tr').each(function() {
        var dueDateStr = $(this).find('td:eq(3)').text(); // Due Date
        var dueDate = new Date(dueDateStr);
        var currentDate = new Date();
        var timeDiff = currentDate.getTime() - dueDate.getTime(); // Get the difference in milliseconds

        if (timeDiff > 0) { // Ensure that fines are only calculated for overdue books
            var overdueDays = Math.ceil(timeDiff / (1000 * 3600 * 24)); // Calculate difference in days
            var fines = (overdueDays - 1) * 3; // Fines calculation (₱3 per day after the first day)
            $(this).find('.fines').text(fines); // Update fines in the table
        } else {
            $(this).find('.fines').text(0); // If not overdue, set fines to 0
        }
    });
}

$(document).ready(function() {
    calculateFines('overdue-books-body-1'); // Calculate fines for the first table on page load
    calculateFines('overdue-books-body-2'); // Calculate fines for the second table on page load
});
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
<script>
// Function to handle the approve button click for the first table
function handleApproveButtonClickForTable1() {
    $('#overdue-books-body-1').on('click', '.approve-btn', function() {
        var row = $(this).closest('tr'); // Get the row of the clicked button
        var rid = row.find('.rid').text(); // Get the RID
        var uid = row.find('.uid').text(); // Get the UID
        var bid = row.find('.bid').text(); // Get the BID
        var fines = row.find('.fines').text(); // Get the fines
        var info = row.find('td:eq(1)').text(); // Get the User Info
        var title = row.find('td:eq(2)').text(); // Get the Book Title

        // Get the current date
        var currentDate = new Date();
        var formattedDate = (currentDate.getMonth() + 1).toString().padStart(2, '0') + '-' +
                            currentDate.getDate().toString().padStart(2, '0') + '-' +
                            currentDate.getFullYear().toString();

        // AJAX request to update the database
        $.ajax({
            url: 'update_overdue.php', // PHP file to handle the database operations
            type: 'POST',
            data: {
                rid: rid,
                uid: uid,
                bid: bid,
                info: info,
                title: title,
                fines: fines,
                date_set: formattedDate
            },
            success: function(response) {
                var res = JSON.parse(response);
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Overdue book settled and updated successfully.',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            row.remove(); // Remove the row from the table
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: res.message || 'An error occurred while updating the database.',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Overdue book settled and updated successfully.',
                        confirmButtonText: 'OK'
}).then((result) => {
    if (result.isConfirmed) {
        location.reload(); // Reload the page
    }
});
            }
        });
    });
}

// Function to handle the approve button click for the second table
function handleApproveButtonClickForTable2() {
    $('#overdue-books-body-2').on('click', '.approve-btn', function() {
        var row = $(this).closest('tr'); // Get the row of the clicked button
        var rid = row.find('.rid').text(); // Get the RID
        var uid = row.find('.uid').text(); // Get the UID
        var bid = row.find('.bid').text(); // Get the BID
        var fines = row.find('.fines').text(); // Get the fines
        var info = row.find('td:eq(1)').text(); // Get the User Info
        var title = row.find('td:eq(2)').text(); // Get the Book Title

        // Get the current date
        var currentDate = new Date();
        var formattedDate = (currentDate.getMonth() + 1).toString().padStart(2, '0') + '-' +
                            currentDate.getDate().toString().padStart(2, '0') + '-' +
                            currentDate.getFullYear().toString();

        // AJAX request to update the database
        $.ajax({
            url: 'update_overdue.php', // PHP file to handle the database operations
            type: 'POST',
            data: {
                rid: rid,
                uid: uid,
                bid: bid,
                info: info,
                title: title,
                fines: fines,
                date_set: formattedDate
            },
            success: function(response) {
    console.log(response);  // Log the response to inspect it
    try {
        var res = JSON.parse(response);
        if (res.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Overdue book settled and updated successfully.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    row.remove(); // Remove the row from the table
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: res.message || 'An error occurred while updating the database.',
                confirmButtonText: 'OK'
            });
        }
    } catch (e) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Received invalid JSON: ' + response,
            confirmButtonText: 'OK'
        });
    }
},
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Overdue book settled and updated successfully.',
                        confirmButtonText: 'OK'
}).then((result) => {
    if (result.isConfirmed) {
        location.reload(); // Reload the page
    }
});
            }
        });
    });
}

$(document).ready(function() {
    // Bind the event handler for the first table
    handleApproveButtonClickForTable1();
    // Bind the event handler for the second table
    handleApproveButtonClickForTable2();
});
</script>
</body>
</html>

