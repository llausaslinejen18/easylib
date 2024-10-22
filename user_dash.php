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

// Check if 'uid' parameter is present in the URL
if(isset($_GET['uid'])) {
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

// Query to count pending reservations for the current user
$query_pending_reservations = "SELECT COUNT(*) AS pending_count FROM rsv WHERE uid = ? AND status = 'Pending'";
$stmt_pending_reservations = $mysqli->prepare($query_pending_reservations);
$stmt_pending_reservations->bind_param("i", $uid);
$stmt_pending_reservations->execute();
$result_pending_reservations = $stmt_pending_reservations->get_result();

// Initialize pending reservations count
$pending_count = 0;

// Check if the result is not empty
if ($result_pending_reservations && $result_pending_reservations->num_rows > 0) {
    $row_pending_reservations = $result_pending_reservations->fetch_assoc();
    $pending_count = $row_pending_reservations['pending_count'];
}

// Query to count borrowed books (status="Returned") for the current user
$query_borrowed_books = "SELECT COUNT(*) AS borrowed_count FROM rsv WHERE uid = ? AND status = 'Returned'";
$stmt_borrowed_books = $mysqli->prepare($query_borrowed_books);
$stmt_borrowed_books->bind_param("i", $uid);
$stmt_borrowed_books->execute();
$result_borrowed_books = $stmt_borrowed_books->get_result();

// Initialize borrowed books count
$borrowed_count = 0;

// Check if the result is not empty
if ($result_borrowed_books && $result_borrowed_books->num_rows > 0) {
    $row_borrowed_books = $result_borrowed_books->fetch_assoc();
    $borrowed_count = $row_borrowed_books['borrowed_count'];
}

// Query to count overdued books for the current user
$query_overdued_books = "SELECT COUNT(*) AS overdued_count FROM rsv WHERE uid = ? AND status = 'Overdue'";
$stmt_overdued_books = $mysqli->prepare($query_overdued_books);
$stmt_overdued_books->bind_param("i", $uid);
$stmt_overdued_books->execute();
$result_overdued_books = $stmt_overdued_books->get_result();

// Initialize overdued books count
$overdued_count = 0;

// Check if the result is not empty
if ($result_overdued_books && $result_overdued_books->num_rows > 0) {
    $row_overdued_books = $result_overdued_books->fetch_assoc();
    $overdued_count = $row_overdued_books['overdued_count'];
}

// Query to count favorites for the current user
$query_favorites = "SELECT COUNT(*) AS favorites_count FROM fav WHERE uid = ?";
$stmt_favorites = $mysqli->prepare($query_favorites);
$stmt_favorites->bind_param("i", $uid);
$stmt_favorites->execute();
$result_favorites = $stmt_favorites->get_result();

// Initialize favorites count
$favorites_count = 0;

// Check if the result is not empty
if ($result_favorites && $result_favorites->num_rows > 0) {
    $row_favorites = $result_favorites->fetch_assoc();
    $favorites_count = $row_favorites['favorites_count'];
}

// Prepare and execute SQL query to count total PDFs
$query_total_pdfs = "SELECT COUNT(*) AS total_pdfs FROM pdf";
$result_total_pdfs = $mysqli->query($query_total_pdfs);

// Initialize total PDFs count
$totalPdfsCount = 0;

// Check if the query executed successfully
if ($result_total_pdfs) {
    $total_pdfs_data = $result_total_pdfs->fetch_assoc();
    $totalPdfsCount = $total_pdfs_data['total_pdfs'];
}

// Free the result set
if ($result_total_pdfs) {
    $result_total_pdfs->free();
}

// Function to count check-ins based on user info
function getTotalCheckins($uid, $mysqli) {
    // Initialize check-ins count
    $total_checkins = 0;

    // Query to get the 'info' of the current user from 'users' table
    $query_info = "SELECT info FROM users WHERE uid = ?";
    $stmt_info = $mysqli->prepare($query_info);
    $stmt_info->bind_param("i", $uid);
    $stmt_info->execute();
    $result_info = $stmt_info->get_result();

    // Check if the user exists and fetch the 'info'
    if ($result_info && $result_info->num_rows > 0) {
        $user_info = $result_info->fetch_assoc()['info'];

        // Query to count the total check-ins with the same 'info', excluding archived entries
        $query_checkins = "SELECT COUNT(*) AS total_checkins FROM chkin WHERE info = ? AND archived != 'Yes'";
        $stmt_checkins = $mysqli->prepare($query_checkins);
        $stmt_checkins->bind_param("s", $user_info);
        $stmt_checkins->execute();
        $result_checkins = $stmt_checkins->get_result();

        // Check if the result is not empty
        if ($result_checkins && $result_checkins->num_rows > 0) {
            $total_checkins = $result_checkins->fetch_assoc()['total_checkins'];
        }

        // Close the statement
        $stmt_checkins->close();
    }

    // Close the statement
    $stmt_info->close();

    // Return the total check-ins count
    return $total_checkins;
}
// Get the total check-ins for the current user
$total_checkins = getTotalCheckins($uid, $mysqli);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/user_dash.css">
</head>
<body class="bg">
    <nav class="navbar">
        <div class="navbar-container">
            <img src="css/pics/logop.png" alt="Logo" class="logo">
            <p style="margin-left: 7%;">EasyLib: Library User Experience and Management Through Integrated Monitoring Systems</p>
        </div>
    </nav>

    <div class="sidebar">
        <div>
            <!-- Display Profile Image -->
            <a href="user_pf.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>"><img src="<?php echo $profile_image_path; ?>" alt="Profile Image" class="profile-image" style="width:100px; height:100px; border-radius:50%;"></a>
        </div>
        <div class="hell">Hello, <?php echo $user_username_display; ?>!</div>
        <a href="user_dash.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item active">Dashboard</a>
        <a href="user_rsrv.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Reserve/Borrow</a>
        <a href="user_ovrd.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Overdue</a>
        <a href="user_fav.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Favorites</a>
        <a href="user_sebk.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">E-Books</a>
        <a href="login.php" class="logout-btn">Logout</a>
    </div>

<!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span style="font-weight:bold;" id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span style="font-weight:bold;" id="current-time"></span></p>
    </div>

<div class="content-container">
<div class="box-container">

    <div class="box reserved-books"> <a style="text-decoration:none;" href="user_brwd.php<?php if(isset($uid)) echo '?uid='. $uid;?>">
        <div class="box-content">
            <div class="box-icon">
                <img src="css/pics/information.png" alt="Pending">
            </div>
            <div class="box-text">
                <div><span style="font-weight:bold; font-size: 30px;"><?php echo $pending_count;?></span> <br> Pending Reservations</div>
            </div>
        </div>
    </a>
    </div>

    <div class="box borrowed-books"> <a style="text-decoration:none;" href="user_brwd.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>">
    <div class="box-content">
        <div class="box-icon">
            <img src="css/pics/read.png" alt="Borrowed">
        </div>
        <div class="box-text">
            <div><span style="font-weight:bold; font-size: 30px;"><?php echo $borrowed_count; ?></span> <br> Borrowed Books</div>
        </div>
        </div>
     </a>
    </div>

    <div class="box overdued-books"> <a style="text-decoration:none;" href="user_ovrd.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>">
    <div class="box-content">
        <div class="box-icon">
            <img src="css/pics/questions.png" alt="Overdue">
        </div>
        <div class="box-text">
            <div><span style="font-weight:bold; font-size: 30px;"><?php echo $overdued_count; ?></span> <br> Overdue Books</div>
        </div>
        </div>
     </a>
    </div>
    
    <div class="box favorites"> <a style="text-decoration:none;" href="user_fav.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>">
    <div class="box-content">
        <div class="box-icon">
            <img src="css/pics/reading-book.png" alt="Favorites">
        </div>
        <div class="box-text">
            <div><span style="font-weight:bold; font-size: 30px;"><?php echo $favorites_count; ?></span><br>Favorites</div>
        </div>
        </div>
     </a>
    </div>
    
    <div class="box pdf"> <a style="text-decoration:none;" href="user_sebk.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>">
        <div class="box-content margin-right:11%;">
        <div class="box-icon">
            <img src="css/pics/pdf.png" style="width:70px; height:65px;" alt="Total PDFs">
        </div>
        <div class="box-text">
                <div><span style="font-weight:bold; font-size: 30px;"><?php echo $totalPdfsCount; ?></span> <br>Total E-Books</div>
        </div>
        </div>
     </a>
    </div>

    <div class="box checkin"> <a style="text-decoration:none;" href="user_logs.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>">
    <div class="box-content">
        <div class="box-icon">
            <img src="css/pics/chkin.png" style="width:70px; height:65px;" alt="Total Check-ins">
        </div>
        <div class="box-text">
            <div><span style="font-weight:bold; font-size: 30px;"><?php echo $total_checkins; ?></span> <br>Total Check-Ins</div>
        </div>
    </div>
</a>
</div>

</div>

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
