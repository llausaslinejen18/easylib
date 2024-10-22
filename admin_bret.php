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

// Handle check button click event
if(isset($_POST['checkButton'])) {
    // Check if reservation ID is set
    if(isset($_POST['reservation_id'])) {
        $reservation_id = $_POST['reservation_id'];
        
        // Insert current date into date_ret column
        $currentDate = date("m-d-Y");
        $status = "Returned"; // Set status to "Returned"

        $updateQuery = "UPDATE rsv SET date_ret = ?, status = ? WHERE rid = ? AND status = 'Released'";
        $stmt = $mysqli->prepare($updateQuery);
        $stmt->bind_param("ssi", $currentDate, $status, $reservation_id);
        $stmt->execute();
        
        // Check if any records were updated
        if ($stmt->affected_rows > 0) {
            // Records updated successfully
            $successMessage = "Records updated successfully!";
            
            // Update status in inventory table to "Available"
            $updateInventoryQuery = "UPDATE inventory SET status = 'Available' WHERE bid = (SELECT bid FROM rsv WHERE rid = ?)";
            $stmtInventory = $mysqli->prepare($updateInventoryQuery);
            $stmtInventory->bind_param("i", $reservation_id);
            $stmtInventory->execute();
            
            // Check if execute() succeeded
            if ($stmtInventory === false) {
                die("Error in execute(): " . $mysqli->error);
            }
            $stmtInventory->close();
        } else {
            // No records updated
            // You can optionally display an alert message here
        }
        
        $stmt->close();
    }
}

if(isset($_POST['due_date']) && isset($_POST['rid'])) {
    $new_due_date = $_POST['due_date'];
    $reservation_id = $_POST['rid'];

    // Convert date to required format if necessary
    // For example, if you need `m-d-Y` format
    $formatted_due_date = date("m-d-Y", strtotime($new_due_date));

    // Update the due date in the database
    $updateQuery = "UPDATE rsv SET due_date = ? WHERE rid = ?";
    $stmt = $mysqli->prepare($updateQuery);
    $stmt->bind_param("si", $formatted_due_date, $reservation_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $successMessage = "Due date updated successfully!";
    } else {
        $successMessage = "Failed to update due date.";
    }

    $stmt->close();
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
    <style>
    /* Modal background */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0,0,0);
        background-color: rgba(0,0,0,0.4);
        text-align:center;
    }

    /* Modal content */
    .modal-content {
        background-color: #fefefe;
        margin: 5% auto; /* Center the modal vertically with margin */
        padding: 20px;
        border: 1px solid #888;
        width: 80%; /* Adjust width as needed */
        max-width: 600px; /* Optional: Set a max width */
        position: relative;
        top: 20%;
        transform: translateY(-50%); /* Center vertically */
    }

    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close-btn:hover,
    .close-btn:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    .save-btn{
        border: none;
        background-color: gold;
        color: black;
        border-radius: 30px;
        padding: 8px;
        cursor: pointer;
        transition: background-color 0.3s; /* Smooth transition for background color */
        margin-right: 10px;
        text-decoration: none;
    }
    .save-btn:hover{
        background-color: green;
        color: white;
    }
</style>
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
            <a href="admin_brel.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Release</a>
            <a href="admin_bret.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Return</a>
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
            
            
                <table>
                <thead>
                        <tr>
                            <th>#</th>
                            <th>User Info</th>
                            <th>Contact Number</th>
                            <th>Book Title</th>
                            <th>Date Released</th>
                            <th>Return Due</th>
                            <th>Returned</th>
                            <th>Action:</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Query to fetch joined data from users and rsv tables
                    $query = "SELECT u.info, u.contact, r.title, r.date_rel, r.due_date, r.rid FROM users u JOIN rsv r ON u.uid = r.uid WHERE r.status = 'Released'";
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
                            echo "<td>" . $row["date_rel"] . "</td>";
                            echo "<td>" . $row["due_date"] . "</td>";
                            // Hidden input field to store reservation ID
                            echo "<td style='text-align: center;'><form method='post'><input type='hidden' name='reservation_id' value='" . $row["rid"] . "'><button type='submit' class='approve-btn' name='checkButton'><i class='fas fa-check'></i></button></form></td>";
                            // Add edit and delete buttons
                            echo "<td>";
                            echo '<a href="#" class="edit-btn" data-rid="' . $row["rid"] . '" data-due_date="' . $row["due_date"] . '"><i class="fas fa-edit"></i></a>';
                            echo '</td>';
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No data available.</td></tr>";
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
                        });
                      </script>";
            }
            ?>
    </div>
    <!-- Modal for Editing Due Date -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Edit Due Date</h2>
        <form id="editForm" method="post">
            <input type="hidden" name="rid" id="editRid">
            <label for="due_date">Due Date:</label>
            <input type="date" id="due_date" name="due_date" required>
            <button type="submit" class="save-btn">Save</button>
        </form>
    </div>
</div>

</body>
<!-- JavaScript code to update date and time -->
<script>
    // Function to show SweetAlert for overdue books
    function showOverdueAlert(title) {
        Swal.fire({
            icon: 'warning',
            title: 'Overdue Book',
            text: 'The book "' + title + '" is overdue!',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                // Reload the page
                location.reload();
            }
        });
    }

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
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById("editModal");
    var closeBtn = document.getElementsByClassName("close-btn")[0];
    var editButtons = document.getElementsByClassName("edit-btn");

    // Show the modal with the correct due date
    Array.from(editButtons).forEach(function(button) {
        button.addEventListener("click", function() {
            var rid = this.getAttribute("data-rid");
            var dueDate = this.getAttribute("data-due_date");

            document.getElementById("editRid").value = rid;
            document.getElementById("due_date").value = dueDate || ''; // Directly use dueDate as it's already in m-d-Y format

            modal.style.display = "block";
        });
    });

    // Close the modal
    closeBtn.onclick = function() {
        modal.style.display = "none";
    };

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };
});
</script>
</html>