<?php
// Include database connection
include 'config.php';

// Initialize success message variable
$successMessage = '';

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

// Query to fetch reservation information with formatted dates and specific conditions
$query = "SELECT r.rid, r.bid, r.info, r.contact, i.title, r.date_rel, r.due_date
          FROM rsv r 
          INNER JOIN inventory i ON r.bid = i.bid 
          WHERE r.status = 'Released' AND r.uid = 0";
$result = $mysqli->query($query);

// Check if a reservation needs to be deleted
if (isset($_POST['delete_reservation'])) {
    $rid = $_POST['reservation_id'];
    // Query to update the status of the reservation to 'Cancelled'
    $update_query = "UPDATE rsv SET status = 'Cancelled' WHERE rid =?";
    $stmt = $mysqli->prepare($update_query);
    $stmt->bind_param("i", $rid);
    if ($stmt->execute()) {
        $successMessage = "Reservation successfully cancelled.";
    } else {
        $successMessage = "Error: Failed to cancel reservation.";
    }
    $stmt->close();
    // Redirect back to the same page to prevent form resubmission
    header("Location: admin_wrel.php" . (isset($aid) ? '?aid=' . $aid : ''));
    exit();
}

// Function to handle check button click event
function markAsReturned($rid, $bid) {
    global $mysqli, $successMessage;
    // Convert current date to mm-dd-yyyy format
    $currentDate = date('m-d-Y');
    // Update reservation status to 'Returned' and set the return date to current date in mm-dd-yyyy format
    $update_query = "UPDATE rsv SET status = 'Returned', date_ret = ? WHERE rid = ?";
    $stmt = $mysqli->prepare($update_query);
    $stmt->bind_param("si", $currentDate, $rid);
    if ($stmt->execute()) {
        // Update book status to 'Available' in the inventory table
        $update_inventory_query = "UPDATE inventory SET status = 'Available' WHERE bid = ?";
        $stmt_inventory = $mysqli->prepare($update_inventory_query);
        $stmt_inventory->bind_param("i", $bid);
        if ($stmt_inventory->execute()) {
            $successMessage = "Reservation successfully marked as returned.";
        } else {
            $successMessage = "Error: Failed to update book status.";
        }
        $stmt_inventory->close();
    } else {
        $successMessage = "Error: Failed to mark reservation as returned.";
    }
    $stmt->close();
}

// Check if the check button is clicked
if (isset($_POST['check_reservation'])) {
    $rid = $_POST['reservation_id'];
    $bid = $_POST['book_id'];
    markAsReturned($rid, $bid);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Released Books</title>
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
            <a href="admin_wrel.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Released Books</a>
            <a href="admin_wlogs.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Borrow Logs</a>
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
                            <th hidden>bid</th>
                            <th>User Info</th>
                            <th>Contact Number</th>
                            <th>Book Title</th>
                            <th>Date Released</th>
                            <th>Return Due</th>
                            <th>Returned</th>
                            <th colspan="2">Actions:</th>
                        </tr>
                    </thead>
                    <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    $counter = 1;
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $counter++ . "</td>";
                        echo "<td hidden>" . $row["rid"] . "</td>";
                        echo "<td hidden>" . $row["bid"] . "</td>";
                        echo "<td>" . $row["info"] . "</td>";
                        echo "<td>" . $row["contact"] . "</td>";
                        echo "<td>" . $row["title"] . "</td>";
                        echo "<td>" . $row["date_rel"] . "</td>";
                        echo "<td>" . $row["due_date"] . "</td>";
                        echo "<td style='text-align: center;'>
                                <form id='check_form_" . $row["rid"] . "' name='check_form_" . $row["rid"] . "' method='post'>
                                    <input type='hidden' name='check_reservation' value='true'>
                                    <input type='hidden' name='reservation_id' value='" . $row["rid"] . "'>
                                    <input type='hidden' name='book_id' value='" . $row["bid"] . "'>
                                    <button type='submit' class='approve-btn'><i class='fas fa-check'></i></button>
                                </form>
                        </td>";
                        // Add edit and delete buttons
                        echo '<td>';
                        echo '<a href="edit_rsv.php?aid=' . $aid . '&rid=' . $row["rid"] . '"><button class="edit-btn"><i class="fas fa-edit"></i></button></a>';
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
                    echo "<tr><td colspan='8'>No data found in reservation table.</td></tr>";
                }
                ?>
            </tbody>
                </table>
                <?php
            // Display success alert if success message is set
            if (!empty($successMessage)) {
                echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: '" . $successMessage . "',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            // Redirect to admin_wrel.php with the current aid
                            window.location.href = 'admin_wrel.php" . (isset($aid) ? '?aid=' . $aid : '') . "';
                        });
                    </script>";
            }
            ?>
    </div>
    <script>
        // Function to handle check button click event
        function checkButton(rid) {
            // Submit the form to mark the reservation as returned
            document.forms['check_form_' + rid].submit();
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
// Function to handle reservation deletion
function deleteReservation(rid) {
    // Show a confirmation dialog
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to delete this reservation.',
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
</body>
</html>
