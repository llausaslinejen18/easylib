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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bid = $_POST['bid'];
    $title = $_POST['title'];
    $uid = $_POST['uid']; // Add this line to retrieve uid

    // Fetch status of selected book
    $status_query = "SELECT status FROM inventory WHERE bid = ?";
    $stmt_status = $mysqli->prepare($status_query);
    $stmt_status->bind_param("i", $bid);
    $stmt_status->execute();
    $result_status = $stmt_status->get_result();
    $status_row = $result_status->fetch_assoc();
    $status = $status_row['status'];

    // If status is "Available", insert into "rsv" table
    if ($status === "Available") {
        // Update status of selected book to "Reserved" in inventory table
        $update_query = "UPDATE inventory SET status = 'Reserved' WHERE bid = ?";
        $stmt_update = $mysqli->prepare($update_query);
        $stmt_update->bind_param("i", $bid);
        $stmt_update->execute();
        $stmt_update->close();

        // Insert user UID, book title, and "Pending" status into "rsv" table
        $insert_query = "INSERT INTO rsv (uid, bid, title, status) VALUES (?, ?, ?, 'Pending')";
        $stmt_insert = $mysqli->prepare($insert_query);
        $stmt_insert->bind_param("iis", $uid, $bid, $title);
        $stmt_insert->execute();
        $stmt_insert->close();

        echo "success";
    } else {
        echo "not_available";
    }
} 

$mysqli->close();
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
            <a href="user_fav.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item active">Favorites</a>
            <a href="user_sebk.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">E-Books</a>
            <a href="login.php" class="logout-btn">Logout</a>
        </div>

        <div class="content">
            <nav class="secondary-navbar">
                <a href="user_fav.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="secondary-navbar-item active">Favorites <i class='fas fa-heart'></i></a>
            </nav>
        </div>

            <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

        <div class="content-container">
        <h2 style="color: black; margin-top: 1%; margin-bottom: -1.5%;">Books:</h2>
            <div class="search-bar">
            </div>
            <form id="reserveForm" action="<?php echo $_SERVER['PHP_SELF'] . '?uid=' . $uid; ?>" method="POST">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Status:</th>
                        <th colspan='2'>Actions:</th>
                    </tr>
                </thead>
                <tbody>
                <?php
include 'config.php';

// Fetch data from fav table based on current uid
$query = "SELECT f.fid, f.bid, i.title, i.status 
          FROM fav f 
          LEFT JOIN inventory i ON f.bid = i.bid 
          WHERE f.uid = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $counter = 1;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $counter++ . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td style='text-align:center;'><button type='button' class='reserve-btn' data-bid='" . $row['bid'] . "' data-title='" . $row['title'] . "' data-uid='" . $uid . "'>Reserve</button></td>"; // Add data-uid attribute
        echo "<td style='text-align:center;'><button type='button' class='remove-favorite-btn' data-fid='" . $row['fid'] . "'><i class='fas fa-times-circle fa-lg'></i></button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No books in favorites.</td></tr>";
}

$stmt->close();
$mysqli->close();
?>
                </tbody>
            </table>
            </form>
    </div>

    <script>
        // Function to handle the click event of the Reserve button
        document.addEventListener('DOMContentLoaded', function() {
            const reserveButtons = document.querySelectorAll('.reserve-btn');
            reserveButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const bid = this.getAttribute('data-bid');
                    const title = this.getAttribute('data-title');
                    const status = this.parentNode.previousElementSibling.textContent.trim(); // Get the status from the previous cell
                    const uid = this.getAttribute('data-uid'); // Retrieve uid

                    // Check book status before reservation
                    if (status === 'Available') {
                        // Reserve the book
                        reserveBook(bid, title, uid);
                    } else {
                        // Display appropriate alert based on status
                        let alertMessage = '';
                        switch (status) {
                            case 'Not Reservable':
                                alertMessage = 'This book is not available for reservation.';
                                break;
                            case 'Reserved':
                                alertMessage = 'This book is already reserved.';
                                break;
                            case 'Overdued':
                                alertMessage = 'This book is currently overdued by another user.';
                                break;
                            default:
                                alertMessage = 'Unknown status. Cannot reserve the book.';
                        }

                        // Show alert
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: alertMessage,
                        });
                    }
                });
            });
        });

        function reserveBook(bid, title, uid) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'user_fav.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200 && xhr.responseText === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'Reservation Successful',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            window.location.reload();
                        });
                    } else if (xhr.status === 200 && xhr.responseText === "not_available") {
                        Swal.fire({
                            icon: 'success',
                            title: 'Reservation is now pending',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Reservation is now pending',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            window.location.reload();
                        });
                    }
                }
            };
            xhr.send('bid=' + bid + '&title=' + title + '&uid=' + uid); // Pass uid in the data
        }
    </script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
            const removeFavoriteButtons = document.querySelectorAll('.remove-favorite-btn');
            removeFavoriteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const fid = this.getAttribute('data-fid');
                    removeFavorite(fid);
                });
            });

            function removeFavorite(fid) {
                // Send AJAX request to remove the book from favorites
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'remove_favorite.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200 && xhr.responseText.trim() === "success") { // Updated this line
                            Swal.fire({
                                icon: 'success',
                                title: 'Removed from Favorites!',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function() {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Failed to reserve the book.',
                            });
                        }
                    }
                };
                xhr.send('fid=' + fid);
            }
        });
    </script>
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