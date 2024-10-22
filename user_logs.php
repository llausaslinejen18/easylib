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

// Initialize variables for username and profile image
$user_username_display = "";
$profile_image_path = "";
$user_info = ""; // Variable to hold user's info

// Check if 'uid' parameter is present in the URL
if(isset($_GET['uid'])) {
    $uid = $_GET['uid'];
    
    // Query to fetch the username, profile image, and info corresponding to the uid
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
        $user_info = $user['info']; // Fetch user info
        
        // Set the username for display
        $user_username_display = $user_username;
    } else {
        // Display default values if user data is not found
        $user_username_display = "Username";
        $profile_image_path = "uploads/default.jpg"; // Default profile image
    }
    
    // Close statement
    $stmt->close();
}

// Query to fetch data from chkin table for this user's info, excluding rows with archived = 'Yes'
$query = "SELECT date, timein, timeout, purpose FROM chkin WHERE info = ? AND archived != 'Yes'";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $user_info);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
if ($result && $result->num_rows > 0) {
    // Fetch all rows as an associative array
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
} else {
    // Handle case where no matching data is found
    $rows = [];
}

// Close statement
$stmt->close();

// Variables to hold search input values
$search_date = isset($_GET['search_date']) ? $_GET['search_date'] : '';
$search_purpose = isset($_GET['search_purpose']) ? $_GET['search_purpose'] : '';

// Convert the search date from yyyy-mm-dd to mm-dd-yyyy
if (!empty($search_date)) {
    $formatted_search_date = date("m-d-Y", strtotime($search_date));
} else {
    $formatted_search_date = '';
}

// Query to fetch data from chkin table for this user's info, excluding rows with archived = 'Yes'
$query = "SELECT date, timein, timeout, purpose FROM chkin WHERE info = ? AND archived != 'Yes'";

// Add search filters for date and purpose
if (!empty($formatted_search_date)) {
    $query .= " AND date = ?";
}
if (!empty($search_purpose)) {
    $query .= " AND purpose LIKE ?";
}

$stmt = $mysqli->prepare($query);

// Bind parameters based on search conditions
if (!empty($formatted_search_date) && !empty($search_purpose)) {
    $search_purpose = "%" . $search_purpose . "%"; // For partial matching
    $stmt->bind_param("sss", $user_info, $formatted_search_date, $search_purpose);
} elseif (!empty($formatted_search_date)) {
    $stmt->bind_param("ss", $user_info, $formatted_search_date);
} elseif (!empty($search_purpose)) {
    $search_purpose = "%" . $search_purpose . "%"; // For partial matching
    $stmt->bind_param("ss", $user_info, $search_purpose);
} else {
    $stmt->bind_param("s", $user_info);
}

$stmt->execute();
$result = $stmt->get_result();

$rows = [];
if ($result && $result->num_rows > 0) {
    // Fetch all rows as an associative array
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
} else {
    // Handle case where no matching data is found
    $rows = [];
}

// Close statement
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/user_rsrv.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg">
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
            <a href="user_rsrv.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Reserve/Borrow</a>
            <a href="user_ovrd.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Overdue</a>
            <a href="user_fav.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Favorites</a>
            <a href="user_sebk.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">E-Books</a>
            <a href="login.php" class="logout-btn">Logout</a>
        </div>

        <div class="content">
            <nav class="secondary-navbar">
            <a href="user_logs.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="secondary-navbar-item active">User Logs</a>
            </nav>
        </div>

            <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
            <div class="search-bar">
                <!-- Search bar needed for Date and Purpose -->
        <form method="GET" action="user_logs.php">
            <input type="hidden" name="uid" value="<?php if(isset($uid)) echo $uid; ?>">
            
            <label for="search-date">Search by Date:</label>
            <input type="date" id="search-date" name="search_date" value="<?php echo isset($_GET['search_date']) ? $_GET['search_date'] : ''; ?>">
            
            <label for="search-purpose">Search by Purpose:</label>
            <input type="text" id="search-purpose" name="search_purpose" placeholder="Enter purpose" value="<?php echo isset($_GET['search_purpose']) ? $_GET['search_purpose'] : ''; ?>">
            
            <button type="submit">Search</button>
            <a class="cancel-btn" href="user_logs.php<?php if(isset($uid)) echo '?uid=' . $uid; ?> " >Clear</a>
        </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date:</th>
                        <th>Time in:</th>
                        <th>Time in:</th>
                        <th>Purpose:</th>
                    </tr>
                </thead>
                <tbody>
            <?php
            // Display each row of data
            if (!empty($rows)) {
                $index = 1; // To keep track of row numbers
                foreach ($rows as $row) {
                    echo "<tr>";
                    echo "<td style='text-align:center;'>" . $index . "</td>";
                    echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['timein']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['timeout']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['purpose']) . "</td>";
                    echo "</tr>";
                    $index++;
                }
            } else {
                echo "<tr><td colspan='5'>No data available</td></tr>";
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
</body>
</html>