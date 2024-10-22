<?php
// Include database connection
include 'config.php';

// Check if 'aid' parameter is present in the URL
if(isset($_GET['aid'])) {
    $aid = $_GET['aid'];

    // Prepare query to fetch the username corresponding to the aid
    $query = "SELECT username FROM admin WHERE aid = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        die("Error in preparing statement: " . $mysqli->error);
    }

    // Bind parameters and execute
    $stmt->bind_param("i", $aid);
    if (!$stmt->execute()) {
        die("Error in executing statement: " . $stmt->error);
    }

    // Get result
    $result = $stmt->get_result();

    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $admin_username_display = $admin['username'];
    } else {
        // Display a default message if admin username is not found
        $admin_username_display = "Username";
    }
    // Close statement
    $stmt->close();
}

// Check if 'id' parameter is present in the POST data
if(isset($_POST['id'])) {
    $id = $_POST['id'];

    // Prepare and execute SQL query to update the 'archived' column
    $query = "UPDATE chkin SET archived = 'Yes' WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        die("Error in preparing statement: " . $mysqli->error);
    }

    // Bind parameter
    $stmt->bind_param("i", $id);

    // Execute the statement
    if (!$stmt->execute()) {
        die("Error in executing statement: " . $stmt->error);
    }

    // Check if any row was affected
    if ($stmt->affected_rows > 0) {
        // Update successful
        header("Location: liblogs.php?aid=$aid");
        exit();
    } else {
        // Update failed
        echo "Error: No rows affected.";
    }

    // Close the statement
    $stmt->close();
}

// Initialize search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$user_type = isset($_GET['user_type']) ? $_GET['user_type'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$purpose = isset($_GET['purpose']) ? $_GET['purpose'] : '';

// Modify the query to include only archived entries
$query = "SELECT * FROM chkin WHERE archived = ''";

// Add conditions to the query based on search parameters
if(!empty($search)) {
    $query .= " AND info LIKE '%" . $mysqli->real_escape_string($search) . "%'";
}
if(!empty($user_type)) {
    $query .= " AND user_type = '" . $mysqli->real_escape_string($user_type) . "'";
}
if(!empty($start_date)) {
    $start_date = date("Y-m-d", strtotime($start_date));
    $query .= " AND STR_TO_DATE(date, '%m-%d-%Y') >= '" . $start_date . "'";
}
if(!empty($end_date)) {
    $end_date = date("Y-m-d", strtotime($end_date));
    $query .= " AND STR_TO_DATE(date, '%m-%d-%Y') <= '" . $end_date . "'";
}   
if(!empty($purpose)) {
    $query .= " AND purpose = '" . $mysqli->real_escape_string($purpose) . "'";
}

// Execute the query
$result = $mysqli->query($query);
if ($result === false) {
    die("Error in executing statement: " . $mysqli->error);
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
            <a href="admin_attd.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Attendance</a>
            <a href="liblogs.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">User Logs</a>
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
    <h1 style="color: black;">Library Logs</h1>
    <form method="GET" action="liblogs.php">
    <div class="search-inputs">
                    <label for="search"></label>
                    <input type="text" id="search" name="search" value="<?php echo $search; ?>" placeholder="Search by Info">
                    <select name="user_type">
                        <option value="" <?php if(empty($user_type)) echo "selected"; ?>>User Type:</option>
                        <option value="Student" <?php if($user_type == "Student") echo "selected"; ?>>Student</option>
                        <option value="Faculty" <?php if($user_type == "Faculty") echo "selected"; ?>>Faculty</option>
                        <option value="Visitor" <?php if($user_type == "Visitor") echo "selected"; ?>>Visitor</option>
                        <option value="Staff" <?php if($user_type == "Staff") echo "selected"; ?>>Staff</option>
                    </select>
                    <label for="start-date">From:</label>
                    <input type="date" id="start-date" name="start_date" value="<?php echo $start_date; ?>">
                    <label for="end-date">To:</label>
                    <input type="date" id="end-date" name="end_date" value="<?php echo $end_date; ?>">
                    <select name="purpose">
                        <option value="" <?php if(empty($purpose)) echo "selected"; ?>>User Purpose:</option>
                        <option value="Study" <?php if ($purpose === "Study") echo "selected"; ?>>Study</option>
                        <option value="Research" <?php if ($purpose === "Research") echo "selected"; ?>>Research</option>
                        <option value="Printing" <?php if ($purpose === "Printing") echo "selected"; ?>>Printing</option>
                        <option value="Clearance" <?php if ($purpose === "Clearance") echo "selected"; ?>>Clearance</option>
                        <option value="Borrow Book(s)" <?php if($purpose == 'Borrow Book(s)') echo "selected"; ?>>Borrow Book(s)</option>
                        <option value="Return Book(s)" <?php if($purpose == 'Return Book(s)') echo "selected"; ?>>Return Book(s)</option>
                    </select>
                    <?php if(isset($aid)) echo '<input type="hidden" name="aid" value="'.$aid.'">'; ?>
                    <button type="submit"><i class="fas fa-search"></i> Search</button>
                    <button type="button" onclick="clearForm()">Clear</button>
                </div>
            </form>
    </div>
    <?php
        if ($result && $result->num_rows > 0) {
            echo '<table id="dataTable">';
            echo '<thead>';
            echo '<tr><th>#</th><th>User Info</th><th>ID Number</th><th>User Type</th><th>Date</th><th>Time In</th><th>Time Out</th><th>Purpose</th><th colspan="2">Actions</th></tr>';
            echo '</thead>';
            echo '<tbody id="dataTableBody">';

            // Loop through all rows and fetch the data
            $counter = 0;
            while ($row = $result->fetch_assoc()) {
                $counter++;
                echo '<tr>';
                echo '<td class="counter">' . $counter . '</td>';
                echo '<td hidden>' . $row['id'] . '</td>';
                echo '<td>' . $row['info'] . '</td>';
                echo '<td>' . $row['idnum'] . '</td>';
                echo '<td>' . $row['user_type'] . '</td>';
                echo '<td>' . $row['date'] . '</td>';
                echo '<td>' . $row['timein'] . '</td>';
                echo '<td>' . ($row['timeout'] ? $row['timeout'] : 'N/A') . '</td>';
                echo '<td>' . $row['purpose'] . '</td>';
                echo '<td hidden><button class="edit-btn" onclick="editBook(' . $row['id'] . ')"><i class="fas fa-edit"></i></button></td>';
                echo '<td style="text-align: center;"><button class="delete-btn" onclick="deleteEntry(' . $row['id'] . ', ' . $aid . ')"><i class="fas fa-trash-alt"></i></button></td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No data found.</p>';
        }

        // Free the result set
        if ($result) {
            $result->free();
        }

        // Close the database connection
        $mysqli->close();
        ?>
</div>
    <button class="print-button" onclick="printData()">Print Data</button> <!-- Button to print data -->
    
    <script>
    // Function to clear the search form and redirect back to liblogs.php
    function clearForm() {
        var baseUrl = 'liblogs.php'; // Change this if the base URL is different
        var aidParam = "<?php if(isset($aid)) echo '?aid=' . $aid; else echo ''; ?>";
        var redirectUrl = baseUrl + aidParam;
        window.location.href = redirectUrl;
    }
</script>

    <script>
        function printData() {
        var startDate = document.getElementById("start-date").value;
        var endDate = document.getElementById("end-date").value;
        var heading = "<h2>Library users ";
        if (startDate && endDate) {
            // Convert dates to mm-dd-yyyy format
            startDate = formatDate(startDate);
            endDate = formatDate(endDate);
            heading += "From " + startDate + " to " + endDate;
        } else {
            heading += "for All Dates";
        }
        heading += "</h2>";
        var table = document.getElementById("dataTable").cloneNode(true); // Clone the table
        var headerRow = table.querySelector("thead tr"); // Get the header row
        headerRow.deleteCell(headerRow.cells.length - 1); // Remove the last cell from the header row

        var rows = table.querySelectorAll("tbody tr"); // Get all data rows
        rows.forEach(function(row) {
            row.deleteCell(row.cells.length - 1); // Remove the last cell from each row
        });

        var printContents = heading + table.outerHTML; // Get the outer HTML of the modified table
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }

    function formatDate(date) {
    var d = new Date(date);
    var month = '' + (d.getMonth() + 1);
    var day = '' + d.getDate();
    var year = d.getFullYear();

    if (month.length < 2)
        month = '0' + month;
    if (day.length < 2)
        day = '0' + day;

    return [month, day, year].join('-'); // Format: mm-dd-yyyy
}


function deleteEntry(id, aid) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, archive it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Call PHP function to update 'archived' column
            updateEntryInDatabase(id, aid);
        }
    });
}

function updateEntryInDatabase(id, aid) {
    // Create a form with hidden input fields to send the ID and aid to the PHP script
    var form = document.createElement('form');
    form.method = 'post';
    form.action = 'liblogs.php?aid=' + aid; // Pass aid parameter in the URL

    // Create hidden input fields to send the ID and aid
    var idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = id;

    var aidInput = document.createElement('input');
    aidInput.type = 'hidden';
    aidInput.name = 'aid';
    aidInput.value = aid;

    // Append the input fields to the form
    form.appendChild(idInput);
    form.appendChild(aidInput);

    // Append the form to the document body
    document.body.appendChild(form);

    // Submit the form
    form.submit();
}

function updateCounter() {
    var visibleRows = document.querySelectorAll("#dataTable tbody tr[style='']");
    var counter = 0;
    visibleRows.forEach(function(row) {
        counter++;
        var cell = row.querySelector('.counter');
        if (cell) {
            cell.textContent = counter;
        }
    });
}

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
</body>
</html>
