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

// Initialize the WHERE clause for the SQL query
$where_clause = " WHERE r.status IN ('Returned', 'Cancelled', 'Rejected')";

// Check if any search parameters are provided
if (isset($_GET['search_info']) || isset($_GET['search_title']) || isset($_GET['search_date']) || isset($_GET['search_status']) || isset($_GET['search_rsv_due']) || isset($_GET['search_date_returned']) || (isset($_GET['start_date']) && isset($_GET['end_date']))) {
    $search_info = isset($_GET['search_info']) ? $_GET['search_info'] : '';
    $search_title = isset($_GET['search_title']) ? $_GET['search_title'] : '';
    $search_date = isset($_GET['search_date']) ? $_GET['search_date'] : '';
    $search_rsv_due = isset($_GET['search_rsv_due']) ? $_GET['search_rsv_due'] : '';
    $search_date_returned = isset($_GET['search_date_returned']) ? $_GET['search_date_returned'] : '';
    $search_status = isset($_GET['search_status']) ? $_GET['search_status'] : '';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

    // Add conditions based on provided search parameters
    if (!empty($search_info)) {
        $where_clause .= " AND u.info LIKE '%$search_info%'";
    }
    if (!empty($search_title)) {
        $where_clause .= " AND r.title LIKE '%$search_title%'";
    }
    if (!empty($search_date)) {
        // Format the date to match the database format
        $where_clause .= " AND (DATE_FORMAT(r.date_rel, '%m-%d-%Y') = DATE_FORMAT('$search_date', '%m-%d-%Y') OR DATE_FORMAT(r.date_ret, '%m-%d-%Y') = DATE_FORMAT('$search_date', '%m-%d-%Y'))";
    }
    if (!empty($search_rsv_due)) {
        // Format the date to match the database format
        $where_clause .= " AND DATE_FORMAT(r.rsv_end, '%m-%d-%Y') = DATE_FORMAT('$search_rsv_due', '%m-%d-%Y')";
    }
    if (!empty($search_date_returned)) {
        // Format the date to match the database format
        $where_clause .= " AND DATE_FORMAT(r.date_ret, '%m-%d-%Y') = DATE_FORMAT('$search_date_returned', '%m-%d-%Y')";
    }
    if (!empty($search_status)) {
        $where_clause .= " AND r.status = '$search_status'";
    }
    if (!empty($start_date) && !empty($end_date)) {
        // Add date range condition with correct date format
        $where_clause .= " AND r.rsv_end >= DATE_FORMAT('$start_date', '%m-%d-%Y') AND r.date_ret <= DATE_FORMAT('$end_date', '%m-%d-%Y')";
    } 
}

// Finalize the SQL query
$query = "SELECT r.rid, u.info, u.contact, r.status, r.title, r.date_rel, r.date_ret, r.rsv_end 
          FROM users u 
          JOIN rsv r ON u.uid = r.uid 
          $where_clause";
$result = $mysqli->query($query);
?>
<?php
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
    header("Location: admin_blogs.php" . (isset($aid) ? '?aid=' . $aid : ''));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Logs</title>
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
        <a href="admin_brel.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Borrowed Books</a>
        <a href="admin_ob.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item ">Overdue Books</a>
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
            <a href="admin_brel.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Release</a>
            <a href="admin_bret.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Return</a>
            <a href="admin_blogs.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Borrow Logs</a>
        </nav>
    </div>

    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
    <div class="search-bar">
    <div class="search-bar">
    <form method="GET" action="admin_blogs.php">
        <input type="text" name="search_info" placeholder="Search by Student Info">
        <input type="text" name="search_title" placeholder="Search by Book Title">
        <select name="search_status">
            <option value="">Select Status</option>
            <option value="Returned">Returned</option>
            <option value="Cancelled">Canceled</option>
        </select>
        <br>
        <label for="start_date" style="color:white;">From:</label>
        <input type="date" name="start_date" placeholder="Start Date">
        <label for="end_date" style="color:white;">To:</label>
        <input type="date" name="end_date" placeholder="End Date">
        <?php if(isset($aid)) echo '<input type="hidden" name="aid" value="'.$aid.'">'; ?>
        <button type="submit"><i class="fas fa-search"></i> Search</button>
    </form>
</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User Info</th>
                    <th>Contact Number</th>
                    <th>Book Title</th>
                    <th>Status</th>
                    <th>Reservation Due</th>
                    <th>Date Released</th>
                    <th>Date Returned</th>
                    <th>Action:</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    $counter = 1;
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td hidden>" . $row["rid"] . "</td>";
                        echo "<td>" . $counter++ . "</td>";
                        echo "<td>" . $row["info"] . "</td>";
                        echo "<td>" . $row["contact"] . "</td>";
                        echo "<td>" . $row["title"] . "</td>";
                        echo "<td>" . $row["status"] . "</td>";
                        echo "<td>" . $row["rsv_end"] . "</td>";
                        echo "<td>" . $row["date_rel"] . "</td>";
                        echo "<td>" . $row["date_ret"] . "</td>";
                        // Add delete buttons
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
                    echo "<tr><td colspan='9'>No data available.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <br>
        <button onclick="printData()" class="print-button">Print Data</button>
    </div>

    <script>
    // Function to print the table
    function printData() {
        var tableContent = `
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User Info</th>
                        <th>Contact Number</th>
                        <th>Book Title</th>
                        <th>Status</th>
                        <th>Reservation Due</th>
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
                    <td>${rowData[2].textContent}</td>
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
