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

// Check if a reservation needs to be deleted
if (isset($_POST['delete_reservation'])) {
    $oid = $_POST['reservation_id'];
    $delete_query = "DELETE FROM ovrd WHERE oid = ?";
    $stmt = $mysqli->prepare($delete_query);
    $stmt->bind_param("i", $oid);
    if ($stmt->execute()) {
        $successMessage = "Data successfully deleted.";
    } else {
        $successMessage = "Error: Failed to delete reservation.";
    }
    $stmt->close();
    // Redirect back to the same page to prevent form resubmission
    header("Location: admin_ol.php" . (isset($aid) ? '?aid=' . $aid : ''));
    exit();
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
            <a href="admin_ob.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Overdue Books</a>
            <a href="admin_ol.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Overdue Logs</a>
        </nav>
    </div>

        <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
    <div class="search-bar">
        <input type="text" id="searchInfo" placeholder="Search user info">
        <input type="text" id="searchTitle" placeholder="Search book title">
        <label for="start-date" style="color: white;">From:</label>
            <input type="date" id="startDate" name="start_date" placeholder="Start Date">
        <label for="end-date" style="color: white;">To:</label>
            <input type="date" id="endDate" name="end_date" placeholder="End Date">
        <button type="button" onclick="searchTable()">Search</button>
    </div>
    
    <table id="dataTable">
    <thead>
        <tr>
            <th>#</th>
            <th>User Info</th>
            <th>Book Title</th>
            <th>Due Date</th>
            <th>Fines</th>
            <th>Date Settled</th>
            <th style="display: none;">OID</th>
            <th>Actions:</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Query to fetch data from ovrd table
        $query = "SELECT ovrd.oid, ovrd.uid, ovrd.bid, ovrd.title, ovrd.fines, ovrd.date_set, ovrd.info, users.info AS student_info, inventory.title AS book_title, rsv.due_date FROM ovrd
        LEFT JOIN users ON ovrd.uid = users.uid
        LEFT JOIN inventory ON ovrd.bid = inventory.bid
        LEFT JOIN rsv ON ovrd.rid = rsv.rid";

        $result = $mysqli->query($query);
        
        if ($result && $result->num_rows > 0) {
            $counter = 1;
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $counter++ . "</td>"; // Counter
                // Output student info based on uid
                if ($row["uid"] == 0) {
                    echo "<td>" . $row["info"] . "</td>"; // Student Info
                } else {
                    echo "<td>" . $row["student_info"] . "</td>"; // Student Info
                }
                echo "<td>" . $row["book_title"] . "</td>"; // Book Title
                echo "<td>" . $row["due_date"] . "</td>"; // Due Date
                echo "<td>" . $row["fines"] . "</td>"; // Fines
                echo "<td>" . date('m-d-Y', strtotime($row["date_set"])) . "</td>"; // Date Settled
                echo "<td style='display: none;'>" . $row["oid"] . "</td>"; // OID (hidden)
                // Add delete buttons
                echo "<td style='text-align: center;'>
                    <form id='delete_form_" . $row["oid"] . "' name='delete_form_" . $row["oid"] . "' method='post'>
                        <input type='hidden' name='delete_reservation' value='true'>
                            <input type='hidden' name='reservation_id' value='" . $row["oid"] . "'>
                            <button type='button' class='delete-btn' onclick='deleteReservation(" . $row["oid"] . ")'><i class='fas fa-trash-alt'></i></button>
                    </form>
                    </td>";   
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No records found.</td></tr>";
        }
        ?>
    </tbody>
</table>
</div>
<script>
// Function to handle reservation deletion
function deleteReservation(oid) {
    // Show a confirmation dialog
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to delete this data.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit the form to delete the reservation
            document.forms['delete_form_' + oid].submit();
        }
    });
}

// Function to filter the table based on search inputs
function searchTable() {
    // Get the values from the input fields
    var searchInfo = document.getElementById('searchInfo').value.toLowerCase();
    var searchTitle = document.getElementById('searchTitle').value.toLowerCase();
    var startDate = document.getElementById('startDate').value;
    var endDate = document.getElementById('endDate').value;

    // Get the table rows
    var table = document.getElementById('dataTable');
    var rows = table.getElementsByTagName('tr');

    // Loop through the table rows
    for (var i = 1; i < rows.length; i++) {
        var cells = rows[i].getElementsByTagName('td');
        var showRow = true;

        // Check student info
        var studentInfo = cells[1].textContent.toLowerCase();
        if (searchInfo && studentInfo.indexOf(searchInfo) === -1) {
            showRow = false;
        }

        // Check book title
        var bookTitle = cells[2].textContent.toLowerCase();
        if (searchTitle && bookTitle.indexOf(searchTitle) === -1) {
            showRow = false;
        }

        // Check due date
        var dueDate = cells[3].textContent;
        if (startDate && !compareDates(dueDate, startDate, 'start')) {
            showRow = false;
        }

        // Check date settled
        var dateSettled = cells[5].textContent;
        if (endDate && !compareDates(dateSettled, endDate, 'end')) {
            showRow = false;
        }

        // Show or hide the row
        if (showRow) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

// Helper function to convert date format and compare
function compareDates(dateStr, compareDateStr, compareType) {
    // Convert dateStr (mm-dd-yyyy) to yyyy-mm-dd
    var parts = dateStr.split('-');
    var formattedDateStr = parts[2] + '-' + parts[0] + '-' + parts[1];

    // Create Date objects for comparison
    var date = new Date(formattedDateStr);
    var compareDate = new Date(compareDateStr);

    // Compare dates
    if (compareType === 'start') {
        return date >= compareDate;
    } else if (compareType === 'end') {
        return date <= compareDate;
    }
    return false;
}
</script>
<script>
// Function to format date to mm-dd-yyyy
function formatDateString(dateStr) {
    // Check if the date is in yyyy-mm-dd format
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
        var parts = dateStr.split('-');
        return parts[1] + '-' + parts[2] + '-' + parts[0];
    }
    return dateStr; // Return the date as-is if it's not in yyyy-mm-dd format
}

// Apply the date formatting function to all due dates in the table
window.onload = function() {
    var table = document.getElementById('dataTable');
    var rows = table.getElementsByTagName('tr');

    for (var i = 1; i < rows.length; i++) {
        var cells = rows[i].getElementsByTagName('td');
        var dueDateCell = cells[3]; // Due Date column
        var dueDate = dueDateCell.textContent;

        // Format the due date and update the cell
        dueDateCell.textContent = formatDateString(dueDate);
    }
};
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
</body>
</html>
