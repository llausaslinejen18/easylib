<?php
function checkAdminSession() {
    if (!isset($_GET['uid']) || empty($_GET['uid'])) {
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

// Initialize username variable
$user_username_display = "";
$info = "";

// Check if 'uid' parameter is present in the URL
if(isset($_GET['uid'])) {
    $uid = $_GET['uid'];
    // Query to fetch the username and info corresponding to the uid
    $query = "SELECT username, profile_image, info FROM users WHERE uid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_username = $user['username'];
        $profile_image_path = $user['profile_image']; // Fetch profile image path
        $info = $user['info']; // Get the user's info
        // Set the username for display
        $user_username_display = $user_username;
    } else {
        // Display a default message if user username is not found
        $user_username_display = "Username";
        $profile_image_path = "uploads/default.jpg"; // Default profile image
    }
    // Close statement
    $stmt->close();
}

// Fetch data from rsv table based on the current user (uid) with statuses "Pending", "Reserved", and "Released"
$query_current = "
    SELECT rsv.*, IF(rsv.uid = 0, inventory.title, rsv.title) AS title
    FROM rsv 
    LEFT JOIN users ON rsv.uid = users.uid 
    LEFT JOIN inventory ON rsv.bid = inventory.bid
    WHERE 
        (
            rsv.uid = ? OR 
            (rsv.uid = 0 AND rsv.info = ?)
        ) 
    AND (rsv.status = 'Pending' OR rsv.status = 'Reserved' OR rsv.status = 'Released')
";
$stmt_current = $mysqli->prepare($query_current);
$stmt_current->bind_param("is", $uid, $info); // Bind uid and info
$stmt_current->execute();
$result_current = $stmt_current->get_result();

// Fetch data from rsv table based on the current user (uid) with status "Returned" or "Rejected" for history
$query_history = "
    SELECT rsv.*, IF(rsv.uid = 0, inventory.title, rsv.title) AS title
    FROM rsv 
    LEFT JOIN users ON rsv.uid = users.uid 
    LEFT JOIN inventory ON rsv.bid = inventory.bid
    WHERE 
        (
            rsv.uid = ? OR 
            (rsv.uid = 0 AND rsv.info = ?)
        ) 
    AND (rsv.status = 'Returned' OR rsv.status = 'Rejected' OR rsv.status = 'Canceled')
";
$stmt_history = $mysqli->prepare($query_history);
$stmt_history->bind_param("is", $uid, $info); // Bind uid and info
$stmt_history->execute();
$result_history = $stmt_history->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Reserve/Borrow</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/user_rsrv.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg">
    <div class="container">
    <div class="navbar">
        <div class="navbar-container">
            <img src="css/pics/logop.png" alt="Logo" class="logo">
            <p style="margin-left: 7%;">EasyLib: Library User Experience and Management Through Integrated Monitoring Systems</p>
        </div>
</div>

    <div class="sidebar">
        <div>
            <!-- Display Profile Image -->
            <a href="user_pf.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>"><img src="<?php echo $profile_image_path; ?>" alt="Profile Image" class="profile-image" style="width:100px; height:100px; border-radius:50%;"></a>
        </div>
        <div class="hell">Hello, <?php echo $user_username_display; ?>!</div>
        <a href="user_dash.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Dashboard</a>
        <a href="user_rsrv.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item active">Reserve/Borrow</a>
        <a href="user_ovrd.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Overdue</a>
        <a href="user_fav.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Favorites</a>
        <a href="user_sebk.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">E-Books</a>
        <a href="login.php" class="logout-btn">Logout</a>
    </div>

        <div class="content">
            <nav class="secondary-navbar">
                <a href="user_rsrv.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="secondary-navbar-item">Reserve Books</a>
                <a href="user_brwd.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="secondary-navbar-item active">Borrowed</a>
            </nav>
        </div>
    </div>

    <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
        <h2 style="color: black; margin-top: 1%; margin-bottom: -1.5%;">Current:</h2>
        <div class="search-bar">
        </div>
        
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Status:</th>
                        <th>Reservation Due:</th>
                        <th>Date Borrowed:</th>
                        <th>Due Date:</th>
                        <th>Action:</th> <!-- Added Action column -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display current reservations
                    if ($result_current && $result_current->num_rows > 0) {
                        $counter = 1;
                        // Output data of each row
                        while($row = $result_current->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td>" . $row["title"] . "</td>";
                            echo "<td>" . $row["status"] . "</td>";
                            echo "<td>" . $row["rsv_end"] . "</td>";
                            echo "<td>" . $row["date_rel"] . "</td>";
                            echo "<td>" . $row["due_date"] . "</td>";
                            echo '<td><button class="cancel-btn" data-rid="' . $row["rid"] . '" data-bid="' . $row["bid"] . '" data-status="' . $row["status"] . '">Cancel</button></td>';
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No current borrowed books.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <h2 style="color: black; margin-top: 2%; margin-bottom: -2.0%;">History:</h2>
            <div class="search-bar">
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Date Borrowed</th>
                        <th>Date Returned</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display history records
                    if ($result_history && $result_history->num_rows > 0) {
                        $counter = 1;
                        // Output data of each row
                        while($row_history = $result_history->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td>" . $row_history["title"] . "</td>";
                            echo "<td>" . $row_history["status"] . "</td>";
                            echo "<td>" . $row_history["date_rel"] . "</td>";
                            echo "<td>" . $row_history["date_ret"] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No history records found.</td></tr>";
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
    
    updateTime();// Call the function to update time immediately
    setInterval(updateTime, 1000);// Update time every second
</script>

<script>
// Add click event listener to cancel buttons
document.querySelectorAll('.cancel-btn').forEach(button => {
    button.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default form submission behavior
        
        // Retrieve rid, bid, and status from data attributes
        var rid = this.getAttribute('data-rid');
        var bid = this.getAttribute('data-bid');
        var status = this.getAttribute('data-status');
        
        console.log("Status:", status); // Debugging: Check the status value
        
        if (status == "Pending" || status == "Reserved") {
            console.log("Canceling reservation...");
            // Proceed to cancel the reservation
            $.ajax({
                url: 'cancel_reservation.php',
                type: 'POST',
                data: {rid: rid, bid: bid},
                success: function(response) {
                    console.log("Reservation cancelled successfully.");
                    console.log("Response:", response);
                    // Show SweetAlert confirmation
                    Swal.fire({
                        icon: 'success',
                        title: 'Reservation Cancelled',
                        text: 'Your reservation has been cancelled successfully.'
                    }).then((result) => {
                        // Reload the page after confirmation
                        if (result.isConfirmed || result.isDismissed) {
                            location.reload();
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error occurred during reservation cancelation:", error);
                }
            });
        } else {
            // Show message if status is not "Pending" or "Reserved"
            Swal.fire({
                icon: 'error',
                title: 'Unable to Cancel',
                text: 'Reservation cannot be canceled because its status is ' + status + '.'
            });
        }
    });
});
</script>

<?php
$alertMessage = '';

// Check if the current date is one day ahead of the rsv_end and update status to "Canceled" for the current user
$currentDate = date_create_from_format('m-d-Y', date("m-d-Y"));
if (!$currentDate) {
    // Handle error if date format is incorrect
    echo "Error creating current date.";
    exit; // Terminate script execution
}

$query = "SELECT rid, rsv_end, bid FROM rsv WHERE status = 'Reserved' AND uid = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rsv_end = $row['rsv_end'];
        $rsv_end_date = date_create_from_format('m-d-Y', $rsv_end);
        if (!$rsv_end_date) {
            // Handle error if date format is incorrect
            echo "Error creating reservation end date.";
            exit; // Terminate script execution
        }
        $rid = $row['rid'];
        $bid = $row['bid'];

        $dateDiff = date_diff($currentDate, $rsv_end_date);
        if ($dateDiff->days <= 1) {
            // Update status to "Canceled"
            $updateQuery = "UPDATE rsv SET status = 'Canceled' WHERE rid = ?";
            $stmtUpdate = $mysqli->prepare($updateQuery);
            $stmtUpdate->bind_param("i", $rid);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            // Update inventory status to "Available"
            $updateInventoryQuery = "UPDATE inventory SET status = 'Available' WHERE bid = ?";
            $stmtInventory = $mysqli->prepare($updateInventoryQuery);
            $stmtInventory->bind_param("i", $bid);
            $stmtInventory->execute();
            $stmtInventory->close();

            // Show SweetAlert confirmation dialog before canceling the reservation
            echo "<script>Swal.fire({
                    icon: 'warning',
                    title: 'Reservation Cancellation Confirmation',
                    text: 'Reservation has ended because the book(s) was not picked up. Click OK to cancel it.',
                    showCancelButton: false,
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload(); // Refresh the page after confirmation
                    }
                });</script>";
        }
    }
}
?>

<?php
// Check released reservations and update status to "Overdue" if due date is past
$query_overdue = "SELECT rid, due_date, bid FROM rsv WHERE status = 'Released' AND uid = ?";
$stmt_overdue = $mysqli->prepare($query_overdue);
$stmt_overdue->bind_param("i", $uid);
$stmt_overdue->execute();
$result_overdue = $stmt_overdue->get_result();

if ($result_overdue && $result_overdue->num_rows > 0) {
    while ($row_overdue = $result_overdue->fetch_assoc()) {
        $due_date = date_create_from_format('m-d-Y', $row_overdue['due_date']);
        if (!$due_date) {
            // Handle error if date format is incorrect
            echo "Error creating due date.";
            exit; // Terminate script execution
        }
        $rid_overdue = $row_overdue['rid'];
        $bid_overdue = $row_overdue['bid'];

        $currentDate = date_create_from_format('m-d-Y', date("m-d-Y"));
        if (!$currentDate) {
            // Handle error if date format is incorrect
            echo "Error creating current date.";
            exit; // Terminate script execution
        }

        // Check if due date is greater than current date
        if ($due_date < $currentDate) {
            // Update status to "Overdue"
            $update_overdue_query = "UPDATE rsv SET status = 'Overdue' WHERE rid = ?";
            $stmt_update_overdue = $mysqli->prepare($update_overdue_query);
            $stmt_update_overdue->bind_param("i", $rid_overdue);
            $stmt_update_overdue->execute();
            $stmt_update_overdue->close();

            // Show SweetAlert confirmation dialog for overdue reservations
            echo "<script>Swal.fire({
                    icon: 'warning',
                    title: 'Overdue Reservation Alert',
                    text: 'One of your reserved books is now overdue. Please return it as soon as possible.',
                    showCancelButton: false,
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload(); // Refresh the page after confirmation
                    }
                });</script>";
        }
    }
}
?>

</body>
</html>
