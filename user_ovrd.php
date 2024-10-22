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

// Initialize variables
$user_username_display = "";
$info = ""; // To store the user's info

// Check if 'uid' parameter is present in the URL
if (isset($_GET['uid'])) {
    $uid = $_GET['uid'];
    
    // Query to fetch the username and profile image corresponding to the uid
    $query = "SELECT username, profile_image FROM users WHERE uid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_username = $user['username'];
        $profile_image_path = $user['profile_image']; // Fetch profile image path
        
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

// Fetch overdue books for the user
$settled = array();
if (isset($uid)) {
    $ovrd_query = "SELECT i.title, r.due_date
                    FROM rsv r
                    JOIN inventory i ON r.bid = i.bid
                    WHERE r.status = 'Overdue' AND r.uid = 0 AND r.info = ?"; // Check for matching info
    $stmt_ovrd = $mysqli->prepare($ovrd_query);
    if ($stmt_ovrd) {
        $stmt_ovrd->bind_param("s", $info); // Bind info as a string
        $stmt_ovrd->execute();
        $result_ovrd = $stmt_ovrd->get_result();
        
        if ($result_ovrd && $result_ovrd->num_rows > 0) {
            while ($row = $result_ovrd->fetch_assoc()) {
                $settled[] = $row;
            }
        }
        $stmt_ovrd->close();
    } else {
        // If there's an error in preparing the statement
        echo "Error: " . $mysqli->error;
    }
}

// Function to convert date format
function format_date($date) {
    $timestamp = strtotime($date);
    if ($timestamp) {
        return date("m-d-Y", $timestamp);
    }
    return $date;
}

// Fetch settled data from 'ovrd' table with due_date from 'rsv'
$settled_books = array();

if (isset($info)) {
    // Query to fetch settled books data
    $ovrd_settled_query = "SELECT o.title, o.fines, o.date_set, r.due_date
                           FROM ovrd o
                           JOIN rsv r ON o.rid = r.rid
                           WHERE o.info = ?";

    $stmt_settled = $mysqli->prepare($ovrd_settled_query);
    if ($stmt_settled) {
        $stmt_settled->bind_param("s", $info);
        $stmt_settled->execute();
        $result_settled = $stmt_settled->get_result();

        if ($result_settled && $result_settled->num_rows > 0) {
            while ($row = $result_settled->fetch_assoc()) {
                $settled_books[] = $row;
            }
        }
        $stmt_settled->close();
    } else {
        echo "Error: " . $mysqli->error;
    }
}

// Additional query to fetch the data where uid matches
$additional_data = array();
if (isset($uid)) {
    $additional_query = "SELECT i.title, r.due_date
                         FROM rsv r
                         JOIN inventory i ON r.bid = i.bid
                         WHERE r.uid = ? AND r.status = 'Overdue'";

    $stmt_additional = $mysqli->prepare($additional_query);
    if ($stmt_additional) {
        $stmt_additional->bind_param("i", $uid); // Bind uid as an integer
        $stmt_additional->execute();
        $result_additional = $stmt_additional->get_result();

        if ($result_additional && $result_additional->num_rows > 0) {
            while ($row = $result_additional->fetch_assoc()) {
                $settled[] = $row; // Combine with the previous results
            }
        }
        $stmt_additional->close();
    } else {
        echo "Error: " . $mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Overdue</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/user_rsrv.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        <a href="user_dash.php<?php if (isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Dashboard</a>
        <a href="user_rsrv.php<?php if (isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Reserve/Borrow</a>
        <a href="user_ovrd.php<?php if (isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item active">Overdue</a>
        <a href="user_fav.php<?php if (isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Favorites</a>
        <a href="user_sebk.php<?php if (isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">E-Books</a>
        <a href="login.php" class="logout-btn">Logout</a>
    </div>

    <div class="content">
        <nav class="secondary-navbar">
            <a href="user_ovrd.php<?php if (isset($uid)) echo '?uid=' . $uid; ?>" class="secondary-navbar-item active">Overdued Books</a>
        </nav>
    </div>

    <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
        <h2 style="color: black; margin-top: 1%; margin-bottom: -2.5%;">Liabilities:</h2>
        <div class="search-bar"></div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Due Date:</th>
                    <th>Fines '₱':</th>
                </tr>
            </thead>
            <tbody id="overdue-books-body">
                <?php
                if (!empty($settled)) {
                    $counter = 1;
                    foreach ($settled as $row) {
                        echo "<tr>";
                        echo "<td>" . $counter . "</td>";
                        echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
                        echo "<td style='text-align:center;'>" . $row["due_date"] . "</td>";
                        echo "<td class='fines' style='text-align:center;'></td>"; // Placeholder for fines
                        echo "</tr>";
                        $counter++;
                    }
                } else {
                    echo "<tr><td colspan='4'>No overdue books found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <h2 style="color: black; margin-top: 2%; margin-bottom: -2.5%;">Settled:</h2>
        <div class="search-bar"></div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Due Date:</th>
                    <th>Fines:</th>
                    <th>Date Settled:</th>
                </tr>
            </thead>
            <tbody>
                <!-- Populate table rows with settled books data -->
                <?php if (!empty($settled_books)) { ?>
                    <?php foreach ($settled_books as $index => $set) { ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($set['title']); ?></td>
                            <td style="text-align:center;"><?php echo $set['due_date']; ?></td>
                            <td style="text-align:center;"><?php echo $set['fines']; ?></td>
                            <td style="text-align:center;"><?php echo format_date($set['date_set']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="5">No data available</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
        // Calculate fines for overdue books
        function calculateFines() {
            $('#overdue-books-body tr').each(function() {
                var dueDateStr = $(this).find('td:eq(2)').text(); // Due Date
                var dueDate = new Date(dueDateStr);
                var currentDate = new Date();
                var timeDiff = Math.abs(currentDate.getTime() - dueDate.getTime());
                var overdueDays = Math.ceil(timeDiff / (1000 * 3600 * 24)); // Calculate difference in days

                var fines = (overdueDays - 1) * 3; // Fines calculation
                $(this).find('.fines').text(fines); // Update fines in the table
            });
        }
        calculateFines(); // Calculate fines for overdue books on page load
    </script>
<script>
        // Function to update time
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
        updateTime(); // Call the function to update time immediately
        setInterval(updateTime, 1000); // Update time every second
</script>
</body>
</html>
