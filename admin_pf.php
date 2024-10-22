<?php
function checkAdminSession() {
    if (!isset($_GET['aid']) || empty($_GET['aid'])) {
        header("Location: admin_login.php");
        exit;
    }
}

// Call the function at the top of your files
checkAdminSession();
?>

<?php
// Include database connection
include 'config.php';

// Function to update admin data
function updateAdminData($mysqli, $aid) {
    // Check if all required fields are provided
    if(isset($_POST['name']) && isset($_POST['contact']) && isset($_POST['username'])) {
        $name = $_POST['name'];
        $contact = $_POST['contact'];
        $username = $_POST['username'];

        // Initialize update query
        $updateQuery = "UPDATE admin SET name = ?, contact = ?, username = ?";
        $params = array("s", "s", "s");
        $values = array($name, $contact, $username);

        // Check if new password is provided
        if(!empty($_POST['password'])) {
            $oldPassword = $_POST['old_password'];
            $newPassword = $_POST['password'];
            $repeatPassword = $_POST['repeat_password'];

            // Check if old password matches
            $checkPasswordQuery = "SELECT password FROM admin WHERE aid = ?";
            $stmt = $mysqli->prepare($checkPasswordQuery);
            $stmt->bind_param("i", $aid);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result && $result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                $storedPassword = $admin['password'];
                if($oldPassword === $storedPassword) {
                    // Check if new password matches repeat password
                    if($newPassword === $repeatPassword) {
                        // Add new password to update query
                        $updateQuery .= ", password = ?";
                        $params[] = "s";
                        $values[] = $newPassword;
                    } else {
                        // New passwords don't match, return without updating
                        return "password_mismatch";
                    }
                } else {
                    // Old password doesn't match, return without updating
                    return "incorrect_old_password";
                }
            }
        }

        // Add WHERE clause for specific admin (aid)
        $updateQuery .= " WHERE aid = ?";
        $params[] = "i";
        $values[] = $aid;

        // Prepare and bind parameters
        $stmt = $mysqli->prepare($updateQuery);
        $stmt->bind_param(implode("", $params), ...$values);

        // Execute the update statement
        $stmt->execute();

        // Check if any rows were affected (update successful)
        if($stmt->affected_rows > 0) {
            // Close statement
            $stmt->close();
            return true;
        }
    }
    return false; // If any required field is missing or update fails
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

// Initialize variables
$name = "";
$contact = "";
$username = "";

// Check if 'aid' parameter is present in the URL
if(isset($_GET['aid'])) {
    $aid = $_GET['aid'];
    // Query to fetch the admin data corresponding to the aid
    $query = "SELECT name, contact, username FROM admin WHERE aid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $aid);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $name = $admin['name'];
        $contact = $admin['contact'];
        $username = $admin['username'];
    }
    // Close statement
    $stmt->close();
}

// Initialize variable to track update success
$updateStatus = "";

// Process form submission when save button is clicked
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['old_password'])) {
    // Check old password
    $oldPassword = $_POST['old_password'];

    $checkPasswordQuery = "SELECT password FROM admin WHERE aid = ?";
    $stmt = $mysqli->prepare($checkPasswordQuery);
    $stmt->bind_param("i", $aid);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $storedPassword = $admin['password'];
        if($oldPassword === $storedPassword) {
            // Call the function to update admin data
            $updateStatus = updateAdminData($mysqli, $aid);
        } else {
            $updateStatus = "incorrect_old_password";
        }
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
    <title>Admin Profile</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_pf.css">
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
    <a href="admin_pf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Profile</a>
    <a href="admin_srch.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Accounts</a>
    <a href="admin_attd.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Library Logs</a>
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
        <a href="admin_pf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Profile</a>
    </nav>
</div>

    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

<div class="content-container">
    <center><h1>Admin Profile</h1></center>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?aid=<?php echo $aid; ?>" method="POST">
        <!-- Populate input fields with fetched data -->
        <label for="name"></label>
        <input type="text" id="name" name="name" placeholder="Name" value="<?php echo $name; ?>" required><br>

        <label for="contact"></label>
        <input type="text" id="contact" name="contact" placeholder="Contact Number" value="<?php echo $contact; ?>" required><br>

        <label for="username"></label>
        <input type="text" id="username" name="username" placeholder="Username" value="<?php echo $username; ?>" required><br>

        <label for="old_password"></label>
        <input type="password" id="old_password" name="old_password" placeholder="Old Password"><br>

        <p style="font-size: 14px;"><i>Input new password for updating a new password</i></p>
        <label for="password"></label>
        <input type="password" id="password" name="password" placeholder="New Password"><br>

        <label for="repeat_password"></label>
        <input type="password" id="repeat_password" name="repeat_password" placeholder="Repeat New Password"><br><br>

        <div class="button-container">
            <!-- Cancel button -->
            <button style="width: 100px; background-color: green;" type="submit">Save</button>
            <a href="admin_dash.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>"><button style="width: 100px; background-color: red;" type="button">Cancel</button></a>
        </div>
    </form>
</div>

<?php
// Check if update was successful and show alert
if($updateStatus === true) {
    echo '<script>
            Swal.fire({
                title: "Profile Updated",
                text: "Your profile has been updated successfully!",
                icon: "success",
                confirmButtonText: "OK"
            }).then(() => {
                location.reload();
            });
          </script>';
} elseif($updateStatus === "incorrect_old_password") {
    // Show error alert if old password is incorrect
    echo '<script>
            Swal.fire({
                title: "Error",
                text: "Old password is incorrect!",
                icon: "error",
                confirmButtonText: "OK"
            });
          </script>';
} elseif($updateStatus === "password_mismatch") {
    // Show error alert if new passwords don't match
    echo '<script>
            Swal.fire({
                title: "Error",
                text: "New passwords do not match!",
                icon: "error",
                confirmButtonText: "OK"
            });
          </script>';
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
