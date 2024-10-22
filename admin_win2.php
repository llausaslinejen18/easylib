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
if (isset($_GET['aid'])) {
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

function convertDateFormat($date) {
    return date("m-d-Y", strtotime($date));
}

// Function to fetch and display the top 8 most frequent 'info' values along with 'user_type'
function displayTopBorrowers($mysqli, $fromDate = null, $toDate = null) {
    $query = "SELECT 
                  IFNULL(u.info, r.info) AS info, 
                  u.user_type,  
                  COUNT(*) AS count 
              FROM rsv r
              LEFT JOIN users u ON r.uid = u.uid AND r.info = ''
              WHERE r.status = 'returned'";

    if ($fromDate && $toDate) {
        $query .= " AND r.date_ret >= ? AND r.date_ret <= ?";
    }

    $query .= " GROUP BY info ORDER BY count DESC LIMIT 8";

    $stmt = $mysqli->prepare($query);

    if ($fromDate && $toDate) {
        $stmt->bind_param("ss", $fromDate, $toDate);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        echo '<div class="portrait-container">';
        while ($row = $result->fetch_assoc()) {
            $info = htmlspecialchars($row['info']);
            $truncated_info = (strlen($info) > 30) ? substr($info, 0, 30) . '...' : $info;

            // Check if user_type is empty, and if so, set to "Library User"
            $user_type = !empty($row['user_type']) ? htmlspecialchars($row['user_type']) : "Library User";

            $user_query = "SELECT profile_image FROM users WHERE info = ?";
            $user_stmt = $mysqli->prepare($user_query);
            $user_stmt->bind_param("s", $row['info']);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();

            if ($user_result && $user_result->num_rows > 0) {
                $user = $user_result->fetch_assoc();
                $profile_image = htmlspecialchars($user['profile_image']);
            } else {
                $profile_image = 'uploads/default.jpg';
            }
            $user_stmt->close();

            // Add an onclick event to trigger SweetAlert
            echo '<div class="portrait" onclick="showSweetAlert(\'' . $profile_image . '\', \'' . $info . '\', \'' . $user_type . '\', ' . $row['count'] . ')">';
            echo '<img src="' . $profile_image . '" alt="Profile Image" class="profile-image">';
            echo '<p class="info-text">' . $truncated_info . '</p>';
            echo '<p>User Type: ' . $user_type . '</p>';
            echo '<p>Borrowed ' . htmlspecialchars($row['count']) . ' time(s)</p>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>No data found.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Statistics</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_stat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Add some styling for portrait display */
        .portrait-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .portrait {
            width: 200px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            text-align: center;
            background-color: #f9f9f9;
        }
        .portrait p {
            margin: 10px 0;
            font-size: 16px;
            font-weight: bold;
        }
        .info-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        }
        .profile-image {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 5px;
    }

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table, th, td {
    border: 1px solid black;
}

th, td {
    padding: 10px;
    text-align: center;
}

/* Popup container - can be positioned anywhere */
.popup {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 9; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0, 0, 0, 0.5); /* Black w/ opacity */
}

/* Popup content */
.popup-content {
    background-color: #fefefe;
    margin: 15% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 80%; /* Could be more or less, depending on screen size */
    max-width: 500px;
    text-align: center;
    border-radius: 10px;
}

/* Close button */
.close-btn {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-btn:hover,
.close-btn:focus {
    color: black;
    text-decoration: none;
}

.popup-profile-image {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 15px;
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
        <a href="admin_dash.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Dashboard</a>
        <a href="admin_pf.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Profile</a>
        <a href="admin_srch.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Accounts</a>
        <a href="admin_attd.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Library Logs</a>
        <a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">User Statistics</a>
        <a href="admin_wres.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Walk-in-Borrow</a>
        <a href="admin_preq.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Pending Requests</a>
        <a href="admin_brel.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Borrowed Books</a>
        <a href="admin_ob.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Overdue Books</a>
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
            <a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Charts</a>
            <a href="admin_win.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Top Library User</a>
            <a href="admin_win2.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Top Book Borrrower</a>
        </nav>
    </div>

    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
        <div class="search-bar">
            <form method="GET">
                <input type="hidden" name="aid" value="<?php echo htmlspecialchars($aid); ?>">
                <label for="fromDate">From:</label>
                <input type="date" id="fromDate" name="fromDate" value="<?php echo isset($_GET['fromDate']) ? htmlspecialchars($_GET['fromDate']) : ''; ?>" required>
                <label for="toDate">To:</label>
                <input type="date" id="toDate" name="toDate" value="<?php echo isset($_GET['toDate']) ? htmlspecialchars($_GET['toDate']) : ''; ?>" required>
                <button type="submit">Search</button>
            </form>

            <h1>
            <?php 
                if (isset($_GET['fromDate']) && isset($_GET['toDate'])) {
                    $fromDate = date("F j, Y", strtotime($_GET['fromDate']));
                    $toDate = date("F j, Y", strtotime($_GET['toDate']));
                    echo "Top Book Borrowers from $fromDate to $toDate";
                } else {
                    echo "Top Book Borrowers of All Time";
                }
            ?>
            </h1>
        </div>

        <?php 
        if (isset($_GET['fromDate']) && isset($_GET['toDate'])) {
            $fromDate = date("m-d-Y", strtotime($_GET['fromDate']));
            $toDate = date("m-d-Y", strtotime($_GET['toDate']));
            displayTopBorrowers($mysqli, $fromDate, $toDate);
        } else {
            displayTopBorrowers($mysqli);
        }
        ?>

        <!-- Popup HTML -->
<div id="profilePopup" class="popup">
    <div class="popup-content">
        <span class="close-btn" onclick="closePopup()">&times;</span>
        <img id="popupProfileImage" src="" alt="Profile Image" class="popup-profile-image">
        <p id="popupInfoText"></p>
    </div>
</div>
    </div>

<script>
    function showSweetAlert(profileImage, info, userType, count) {
        Swal.fire({
            title: 'User Info',
            html: `
                <img src="${profileImage}" alt="Profile Image" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 15px;">
                <p><strong>Full Info:</strong> ${info}</p>
                <p><strong>User Type:</strong> ${userType}</p>
                <p><strong>Borrowed:</strong> ${count} time(s)</p>
            `,
            showCloseButton: true,
            focusConfirm: false,
            confirmButtonText: 'Close'
        });
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
