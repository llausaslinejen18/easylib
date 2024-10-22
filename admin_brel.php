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

// Function to add 3 days to the current date
function addSevenDaysToCurrentDate() {
    return date("m-d-Y", strtotime("+7 days"));
}

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

// Function to display SweetAlert for reservation cancellation
function displayReservationCancellationAlert($alertMessage) {
    echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Reservation Cancellation Confirmation',
                text: '{$alertMessage}',
                showCancelButton: false,
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload(); // Refresh the page after confirmation
                }
            });
          </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books</title>
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
            <a href="admin_brel.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Release</a>
            <a href="admin_bret.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Return</a>
            <a href="admin_blogs.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Borrow Logs</a>
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
    <form id="bookReleaseForm" method="post" action="">
    <input type="hidden" name="aid" value="<?php echo isset($aid) ? $aid : ''; ?>">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User Info</th>
                    <th>Contact Number</th>
                    <th>Book Title</th>
                    <th>Reservation Due</th>
                    <th>Release:</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query to fetch joined data from users and rsv tables with status "Reserved"
                $query = "SELECT u.info, u.contact, r.title, r.rsv_end, r.rid FROM users u JOIN rsv r ON u.uid = r.uid WHERE r.status = 'Reserved'";
                $result = $mysqli->query($query);

                if ($result && $result->num_rows > 0) {
                    $counter = 1;
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $counter++ . "</td>";
                        echo "<td>" . $row["info"] . "</td>";
                        echo "<td>" . $row["contact"] . "</td>";
                        echo "<td>" . $row["title"] . "</td>";
                        echo "<td>" . $row["rsv_end"] . "</td>";
                        // Add a hidden input field to store the reservation ID
                        echo "<td style='text-align: center;'>
                                <input type='hidden' name='reservation_id' value='" . $row["rid"] . "'>
                                <button type='button' class='approve-btn' onclick='submitForm(" . $row["rid"] . ")'><i class='fas fa-check'></i></button>
                              </td>"; // Check icon from FontAwesome
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No data available.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </form> <!-- Close form tag -->
</div>


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
    function submitForm(reservationId) {
        var form = document.getElementById("bookReleaseForm");
        var input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("name", "reservation_id");
        input.setAttribute("value", reservationId);
        form.appendChild(input);
        form.submit();
    }

    <?php
// Include database connection
include 'config.php';

// Initialize alert message
$alertMessage = '';

// Check if the current date is past the rsv_end and update status to "Canceled"
$currentDate = date("m-d-Y");
$query = "SELECT rid, rsv_end, bid FROM rsv WHERE status = 'Reserved' AND date_rel ='' ";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rid = $row['rid'];
        $bid = $row['bid'];
        $dueDateStr = $row['rsv_end'];
        $dueDate = date_create_from_format('m-d-Y', $dueDateStr);
        $currentDate = date_create();

        if ($currentDate > $dueDate) {
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

            // Set the alert message
            $alertMessage = "Reservation has been canceled because the book(s) were not picked up.";
            
            // Display SweetAlert confirmation dialog if reservation is canceled
            displayReservationCancellationAlert($alertMessage);
        }
    }
}
// Close statement
$stmt->close();

    // Display SweetAlert confirmation dialog if reservation is canceled
    if (!empty($alertMessage)) {
        displayReservationCancellationAlert($alertMessage);
    }
?>
</script>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reservation_id'])) {
    // Retrieve reservation ID from the form
    $reservation_id = $_POST['reservation_id'];

    // Calculate the current date and the due date (current date + 7 days)
    $current_date = date("m-d-Y");
    $due_date = date("m-d-Y", strtotime("+7 days"));

    // Update the date_rel and due_date columns in the rsv table
    $update_query = "UPDATE rsv SET date_rel = ?, due_date = ?, status = 'Released' WHERE rid = ?";
    $stmt = $mysqli->prepare($update_query);
    $stmt->bind_param("ssi", $current_date, $due_date, $reservation_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to admin_brel.php with aid parameter
    $redirect_url = "admin_brel.php";
    if (isset($aid)) {
        $redirect_url .= "?aid=$aid";
    }
    
    // Display SweetAlert2 notification for successful release
    echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Book Released',
                text: 'The book has been successfully released.',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '$redirect_url'; // Redirect to admin_brel.php
                }
            });
          </script>";
}
?>
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
