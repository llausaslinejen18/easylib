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
function displayTopInfo($mysqli, $fromDate = null, $toDate = null) {
    $query = "SELECT info, user_type, COUNT(info) AS count FROM chkin ";

    if ($fromDate && $toDate) {
        $query .= "WHERE date >= ? AND date <= ? ";
    }

    $query .= "GROUP BY info, user_type ORDER BY count DESC LIMIT 8";

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

            echo '<div class="portrait" data-info="' . $info . '">';
            echo '<img src="' . $profile_image . '" alt="Profile Image" class="profile-image">';
            echo '<p class="info-text">' . $truncated_info . '</p>';
            echo '<p>User Type: ' . htmlspecialchars($row['user_type']) . '</p>';
            echo '<p>Checked In ' . htmlspecialchars($row['count']) . ' time(s)</p>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>No data found in the "info" column.</p>';
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
    /* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    padding-top: 100px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    text-align: center;
    border-radius: 10px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
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
            <a href="admin_win.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Top Library User</a>
            <a href="admin_win2.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Top Book Borrrower</a>
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
                echo "Top Library Users from $fromDate to $toDate";
            } else {
                echo "Library Users of All Time";
            }
        ?>
        </h1>
    </div>
        <!-- place content here -->
        <?php 
        if (isset($_GET['fromDate']) && isset($_GET['toDate'])) {
            $fromDate = date("m-d-Y", strtotime($_GET['fromDate']));
            $toDate = date("m-d-Y", strtotime($_GET['toDate']));
            displayTopInfo($mysqli, $fromDate, $toDate);
        } else {
            displayTopInfo($mysqli);
        }
        ?>
    </div>

<div id="infoModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <img id="modalProfileImage" src="" alt="Profile Image" class="profile-image">
        <p id="modalInfoText"></p>
        <table id="modalDetailsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Purpose</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

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

<script>
    // Get the modal
var modal = document.getElementById("infoModal");
var modalImg = document.getElementById("modalProfileImage");
var modalInfoText = document.getElementById("modalInfoText");
var modalDetailsTable = document.getElementById("modalDetailsTable").getElementsByTagName('tbody')[0];

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// Function to show modal with data
function showModal(profileImage, info, details) {
    modal.style.display = "block";
    modalImg.src = profileImage;
    modalInfoText.textContent = info;

    // Clear any previous details
    modalDetailsTable.innerHTML = '';

    // Populate table with details
    details.forEach(detail => {
        var row = modalDetailsTable.insertRow();
        row.insertCell(0).textContent = detail.date;
        row.insertCell(1).textContent = detail.timein;
        row.insertCell(2).textContent = detail.timeout;
        row.insertCell(3).textContent = detail.purpose;
    });
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// Add click event to each portrait
document.querySelectorAll('.portrait').forEach(function(portrait) {
    portrait.addEventListener('click', function() {
        var profileImage = this.querySelector('.profile-image').src;
        var info = this.getAttribute('data-info'); // Get full info from data attribute

        var fromDate = document.getElementById('fromDate').value;
        var toDate = document.getElementById('toDate').value;

        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_details.php?info=' + encodeURIComponent(info) + '&fromDate=' + encodeURIComponent(fromDate) + '&toDate=' + encodeURIComponent(toDate), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var details = JSON.parse(xhr.responseText);
                showModal(profileImage, info, details);
            } else {
                console.error('Failed to fetch details');
            }
        };
        xhr.send();
    });
});

function showModal(profileImage, info, details) {
    modal.style.display = "block";
    modalImg.src = profileImage;
    modalInfoText.textContent = info;

    // Clear any previous details
    modalDetailsTable.innerHTML = '';

    // Populate table with details and add counter
    details.forEach((detail, index) => {
        var row = modalDetailsTable.insertRow();
        row.insertCell(0).textContent = index + 1; // Counter
        row.insertCell(1).textContent = detail.date;
        row.insertCell(2).textContent = detail.timein;
        row.insertCell(3).textContent = detail.timeout;
        row.insertCell(4).textContent = detail.purpose;
    });
}
</script>
</body>
</html>
