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

// Fetch data with status = "Pending" from rsv table
$query = "SELECT r.rid, r.uid, u.info, u.contact, r.title, r.status FROM rsv r JOIN users u ON r.uid = u.uid WHERE r.status = 'Pending'";
$result = $mysqli->query($query);

// Handle approve and reject actions
if(isset($_POST['action']) && isset($_POST['rid'])) {
    $rid = $_POST['rid'];
    $action = $_POST['action'];
    if($action === 'approve') {
        // Update status to 'Reserved' and set rsv_end to the current date plus 3 days
        $updateQuery = "UPDATE rsv SET status = 'Reserved', rsv_end = ? WHERE rid = ?";
        $currentDate = $_POST['currentDate'];
        $rsvEnd = $_POST['rsvEnd'];
    } elseif($action === 'reject') {
        // Update status to 'Rejected' in rsv table
        $updateQuery = "UPDATE rsv SET status = 'Rejected' WHERE rid = ?";
        // Update status to 'Available' in inventory table
        $updateInventoryQuery = "UPDATE inventory SET status = 'Available' WHERE title = (SELECT title FROM rsv WHERE rid = ?)";
        $stmtInventory = $mysqli->prepare($updateInventoryQuery);
        $stmtInventory->bind_param("i", $rid);
        $stmtInventory->execute();
        $stmtInventory->close();
    }
    $stmt = $mysqli->prepare($updateQuery);
    if ($action === 'approve') {
        $stmt->bind_param("si", $rsvEnd, $rid);
    } else {
        $stmt->bind_param("i", $rid);
    }
    $stmt->execute(); // Execute the query
    $stmt->close(); // Close the statement
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Requests</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_srch.css">
    <!-- Include SweetAlert library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script>
    // Function to calculate rsv_end based on current date
    function calculateRsvEnd(rid) {
        var currentDateElement = document.getElementById("current-date");
        var currentDate = currentDateElement.textContent.trim(); // Get the text content of the current date element
        var currentDateParts = currentDate.split("-"); // Split the date into parts (month, day, year)
        var month = parseInt(currentDateParts[0]) - 1; // Month in JavaScript Date object is 0-based index, so subtract 1
        var day = parseInt(currentDateParts[1]);
        var year = parseInt(currentDateParts[2]);
        
        // Create a JavaScript Date object with the current date
        var currentDateObject = new Date(year, month, day);

        // Add 3 days to the current date
        currentDateObject.setDate(currentDateObject.getDate() + 3);

        // Format the new date as "mm-dd-yyyy"
        var rsvEnd = (currentDateObject.getMonth() + 1).toString().padStart(2, '0') + "-" +
                     currentDateObject.getDate().toString().padStart(2, '0') + "-" +
                     currentDateObject.getFullYear().toString();

        // Set the value of rsvEnd in the hidden input field for the specific row
        var rsvEndInput = document.getElementById("rsvEnd_" + rid);
        if (rsvEndInput) {
            rsvEndInput.value = rsvEnd;
        }
    }

    // Call the function to calculate rsv_end and set its value in the hidden input field for each row
    document.addEventListener("DOMContentLoaded", function() {
        // Get all approve buttons
        var approveButtons = document.querySelectorAll("[data-action='approve']");
        // Add click event listener to each approve button
        approveButtons.forEach(function(button) {
            button.addEventListener("click", function() {
                // Get the rid of the row associated with the clicked button
                var rid = button.getAttribute("data-rid");
                // Calculate and set rsvEnd value for the corresponding row
                calculateRsvEnd(rid);
                // Submit the form
                submitForm("approveForm_" + rid, "approve");
            });
        });
    });
    </script>
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
        <a href="admin_preq.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Pending Requests</a>
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
            <a href="admin_preq.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Pending Requests</a>
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
        </div>
            
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User Info</th>
                    <th>Contact Number</th>
                    <th>Book Title</th>
                    <th>Status:</th>
                    <th colspan='2'>Actions:</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if there are pending requests
                if ($result && $result->num_rows > 0) {
                    $counter = 1;
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $counter++ . "</td>";
                        echo "<td>" . $row["info"] . "</td>";
                        echo "<td>" . $row["contact"] . "</td>";
                        echo "<td>" . $row["title"] . "</td>";
                        echo "<td>" . $row["status"] . "</td>";
                        echo "<td>";
                        // Button to approve request
                        echo "<form style='text-align: center;' id='approveForm_" . $row['rid'] . "' method='post' action=''>";
                        echo "<input type='hidden' name='rid' value='" . $row['rid'] . "'/>";
                        echo "<input type='hidden' id='rsvEnd_" . $row['rid'] . "' name='rsvEnd' value=''>"; // Hidden field for rsv_end
                        echo "<input type='hidden' name='currentDate' value='" . date("m-d-Y") . "'/>"; // Hidden field for current date
                        echo "<button type='button' class='approve-btn' data-action='approve' data-rid='" . $row['rid'] . "'><i class='fas fa-check'></i></button>";
                        echo "</form>";
                        echo "</td>";
                        echo "<td hidden>";
                        // Button to reject request
                        echo "<form style='text-align: center;' id='rejectForm" . $row['rid'] . "' method='post' action=''>";
                        echo "<input type='hidden' name='rid' value='" . $row['rid'] . "'/>";
                        echo "<button type='button' class='reject-btn' onclick='submitForm(\"rejectForm" . $row['rid'] . "\", \"reject\")'><i class='fas fa-times'></i></button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No pending requests.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
    // Function to update date and time
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

    // Function to submit form and prevent multiple submissions
    function submitForm(formId, action) {
        var form = document.getElementById(formId);
        form.querySelector("button").disabled = true; // Disable button after click

        // Show a confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this action!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirms, proceed with form submission
                var formData = new FormData(form);
                formData.append('action', action); // Append action to form data

                fetch(form.action, {
                    method: form.method,
                    body: formData
                }).then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok.');
                    }
                    // Reload the page after successful action
                    location.reload();
                }).catch(error => {
                    console.error('Error:', error);
                    // Display error message using Swal library
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while processing the request. Please try again later.'
                    });
                    form.querySelector("button").disabled = false; // Re-enable button if there's an error
                });
            } else {
                // If user cancels, re-enable the button
                form.querySelector("button").disabled = false;
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
</body>
</html>
