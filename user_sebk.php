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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search E-Books</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/user_rsrv.css">
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
        <a href="user_sebk.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item active">E-Books</a>
        <a href="login.php" class="logout-btn">Logout</a>
    </div>

        <div class="content">
            <nav class="secondary-navbar">
                <a href="user_sebk.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="secondary-navbar-item active">Search Ebooks</a>
            </nav>
        </div>

    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

        <div class="content-container">
        <div class="search-bar">
        <h1 style="color: black;">Search E-Books</h1>
        <input type="text" id="searchTitle" placeholder="Search by Title..." onkeyup="searchTable()">
        <input type="text" id="searchAuthor" placeholder="Search by Author..." onkeyup="searchTable()">
        <input type="text" id="searchYear" placeholder="Search by Year..." onkeyup="searchTable()">
        <input type="text" id="searchGenre" placeholder="Search by Genre..." onkeyup="searchTable()">
        <button class="cancel-btn" id="clearButton" onclick="clearSearch()">Clear</button>
    </div>

    <table id="pdfTable" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Author</th>
                <th>Year</th>
                <th>Genre</th>
                <th>Additional Info</th>
                <th>Link</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query to fetch all PDF records
            $query = "SELECT * FROM pdf";
            $result = $mysqli->query($query);

            // Counter for the first column
            $counter = 1;

            // Loop through each row
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                // Display the counter
                echo "<td hidden>" . $row['pdf_id'] . "</td>";
                echo "<td>" . $counter++ . "</td>";
                echo "<td>" . $row['title'] . "</td>";
                echo "<td>" . $row['author'] . "</td>";
                echo "<td>" . $row['year'] . "</td>";
                echo "<td>" . $row['genre'] . "</td>";
                echo "<td>" . $row['add_info'] . "</td>";
                echo "<td style='text-align:center;'><a href='" . $row['link'] . "' target='_blank'>Link to PDF <i class='fas fa-external-link-alt'></i></a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
    // Search functionality
function searchTable() {
    var titleInput = document.getElementById('searchTitle').value.toLowerCase();
    var authorInput = document.getElementById('searchAuthor').value.toLowerCase();
    var yearInput = document.getElementById('searchYear').value.toLowerCase();
    var genreInput = document.getElementById('searchGenre').value.toLowerCase();

    var table = document.getElementById('pdfTable');
    var tr = table.getElementsByTagName('tr');

    for (var i = 1; i < tr.length; i++) {
        var titleTd = tr[i].getElementsByTagName('td')[2];
        var authorTd = tr[i].getElementsByTagName('td')[3];
        var yearTd = tr[i].getElementsByTagName('td')[4];
        var genreTd = tr[i].getElementsByTagName('td')[5];

        if (titleTd && authorTd && yearTd && genreTd) {
            var titleValue = titleTd.textContent || titleTd.innerText;
            var authorValue = authorTd.textContent || authorTd.innerText;
            var yearValue = yearTd.textContent || yearTd.innerText;
            var genreValue = genreTd.textContent || genreTd.innerText;

            if (titleValue.toLowerCase().indexOf(titleInput) > -1 && 
                authorValue.toLowerCase().indexOf(authorInput) > -1 &&
                yearValue.toLowerCase().indexOf(yearInput) > -1 &&
                genreValue.toLowerCase().indexOf(genreInput) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }       
    }
}

function clearSearch() {
    document.getElementById('searchTitle').value = "";
    document.getElementById('searchAuthor').value = "";
    document.getElementById('searchYear').value = "";
    document.getElementById('searchGenre').value = "";
    searchTable(); // Call searchTable to reset the table display
}
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
