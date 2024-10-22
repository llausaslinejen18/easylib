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
    $rid = $_POST['reservation_id'];
    $delete_query = "DELETE FROM rsv WHERE rid = ?";
    $stmt = $mysqli->prepare($delete_query);
    $stmt->bind_param("i", $rid);
    if ($stmt->execute()) {
        $successMessage = "Data successfully deleted.";
    } else {
        $successMessage = "Error: Failed to delete reservation.";
    }
    $stmt->close();
    // Redirect back to the same page to prevent form resubmission
    header("Location: admin_wlogs.php" . (isset($aid) ? '?aid=' . $aid : ''));
    exit();
}
?>
<?php
// Initialize the WHERE clause for the SQL query
$where_clause = " WHERE r.uid = 0 AND (r.status = 'Returned' OR r.status = 'Cancelled')";

// Check if any search parameters are provided
if (isset($_GET['search_info']) || isset($_GET['search_title']) || (isset($_GET['start_date']) && isset($_GET['end_date'])) || isset($_GET['search_status'])) {
    $search_info = isset($_GET['search_info']) ? $_GET['search_info'] : '';
    $search_title = isset($_GET['search_title']) ? $_GET['search_title'] : '';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    $search_status = isset($_GET['search_status']) ? $_GET['search_status'] : '';

    // Add conditions based on provided search parameters
    if (!empty($search_info)) {
        $where_clause .= " AND r.info LIKE '%$search_info%'";
    }
    if (!empty($search_title)) {
        $where_clause .= " AND i.title LIKE '%$search_title%'";
    }
    if (!empty($start_date) && !empty($end_date)) {
        // Add date range condition without changing the format
        $where_clause .= " AND STR_TO_DATE(r.date_rel, '%m-%d-%Y') >= STR_TO_DATE('$start_date', '%Y-%m-%d') AND STR_TO_DATE(r.date_ret, '%m-%d-%Y') <= STR_TO_DATE('$end_date', '%Y-%m-%d')";
    }
    if (!empty($search_status)) {
        $where_clause .= " AND r.status = '$search_status'";
    }
}

// Finalize the SQL query
$query = "SELECT r.rid, r.bid, r.info, r.contact, i.title, r.status, r.date_rel, r.date_ret
          FROM rsv r 
          INNER JOIN inventory i ON r.bid = i.bid 
          $where_clause";

$result = $mysqli->query($query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books Logs</title>
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
        <a href="admin_wres.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Walk-in-Borrow</a>
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
            <a href="admin_wres.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Reserve Books</a>
            <a href="admin_wrel.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Released Books</a>
            <a href="admin_wlogs.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Borrow Logs</a>
        </nav>
    </div>
    <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>
    <br>
    <div class="content-container">
    <div class="search-bar">
    <form method="GET" action="admin_wlogs.php">
        <input type="text" name="search_info" placeholder="Search by Student Info">
        <input type="text" name="search_title" placeholder="Search by Book Title">
        <select name="search_status">
            <option value="">Select Status</option>
            <option value="Returned">Returned</option>
            <option value="Cancelled">Cancelled</option>
        </select>
        <br>
        <label for="start-date">From:</label>
            <input type="date" name="start_date" placeholder="Start Date">
        <label for="end-date">To:</label>
            <input type="date" name="end_date" placeholder="End Date">
        <?php if(isset($aid)) echo '<input type="hidden" name="aid" value="'.$aid.'">'; ?>
        <button type="submit" style="margin-left: 1%;"><i class="fas fa-search"></i> Search</button>
    </form>
</div>
                <table>
                <thead>
                        <tr>
                            <th>#</th>
                            <th hidden>rid</th>
                            <th hidden>bid</th>
                            <th>User Info</th>
                            <th>Contact Number</th>
                            <th>Book Title</th>
                            <th>Status</th>
                            <th>Date Released</th>
                            <th>Date Returned</th>
                            <th colspan="2">Actions:</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
            if ($result && $result->num_rows > 0) {
                $counter = 1;
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $counter++ . "</td>";
                    echo "<td hidden>" . $row["rid"] . "</td>";
                    echo "<td hidden>" . $row["bid"] . "</td>";
                    echo "<td>" . $row["info"] . "</td>";
                    echo "<td>" . $row["contact"] . "</td>";
                    echo "<td>" . $row["title"] . "</td>";
                    echo "<td>" . $row["status"] . "</td>";
                    echo "<td>" . $row["date_rel"] . "</td>";
                    echo "<td>" . $row["date_ret"] . "</td>";
                    // Add edit and delete buttons
                    echo '<td hidden>';
                    echo '<a href="edit_wlogs.php?aid=' . $aid . '&rid=' . $row["rid"] . '"><button class="edit-btn"><i class="fas fa-edit"></i></button></a>';
                    echo '</td>';
                    echo "<td style='text-align: center;'>
                            <form id='delete_form_" . $row["rid"] . "' name='delete_form_" . $row["rid"] . "' method='post'>
                            <input type='hidden' name='delete_reservation' value='true'>
                                <input type='hidden' name='reservation_id' value='" . $row["rid"] . "'>
                                <button type='button' class='delete-btn' onclick='deleteReservation(" . $row["rid"] . ")'><i class='fas fa-trash-alt'></i></button>
                                </form>
                        </td>";                               
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No data found.</td></tr>";
            }
            ?>
            </tbody>
                </table>
                <br>
                <button onclick="printData()" class="print-button">Print Data</button>
    </div>
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
// Function to handle reservation deletion
function deleteReservation(rid) {
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
            document.forms['delete_form_' + rid].submit();
        }
    });
}
    </script>
<script>
        // Function to print the table
        function printData() {
            var tableContent = `
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Info</th>
                            <th>Contact Number</th>
                            <th>Book Title</th>
                            <th>Status</th>
                            <th>Date Released</th>
                            <th>Date Returned</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            // Loop through each row in the table
            var rows = document.querySelectorAll('.content-container table tbody tr');
            rows.forEach(function(row, index) {
                var rowData = row.querySelectorAll('td');
                tableContent += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${rowData[3].textContent}</td>
                        <td>${rowData[4].textContent}</td>
                        <td>${rowData[5].textContent}</td>
                        <td>${rowData[6].textContent}</td>
                        <td>${rowData[7].textContent}</td>
                        <td>${rowData[8].textContent}</td>
                    </tr>
                `;
            });

            tableContent += `
                    </tbody>
                </table>
            `;

            // Open a new window and write the table content
            var printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Borrowed Books Logs</title>');
            printWindow.document.write('<style>@media print { table, th, td { border: 1px solid black; border-collapse: collapse; } th, td { padding: 8px; text-align: left; } }</style>'); // Print styling
            printWindow.document.write('</head><body>');
            printWindow.document.write('<h1>Borrowed Books Logs</h1>'); // Add header
            printWindow.document.write(tableContent);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
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
