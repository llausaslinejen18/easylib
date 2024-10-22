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

// Initialize the start and end date variables
$start_date = $end_date = null;
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

// User statistics query
$user_counts = [
    "Student" => 0,
    "Faculty" => 0,
    "Staff" => 0,
    "Visitor" => 0
];

$course_counts = [
    "BSIT" => 0,
    "BSNAME" => 0,
    "BSTCM" => 0,
    "BSESM" => 0,
    "BSMET" => 0
];

$query = "SELECT info, user_type, COUNT(*) as count FROM chkin";
if ($start_date && $end_date) {
    $query .= " WHERE STR_TO_DATE(`date`, '%m-%d-%Y') BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d')";
}
$query .= " GROUP BY info, user_type";
$stmt = $mysqli->prepare($query);

if ($stmt === false) {
    // Log the error message
    error_log('MySQL prepare error: ' . $mysqli->error);
    die('Prepare failed: ' . htmlspecialchars($mysqli->error));
}

if ($start_date && $end_date) {
    $stmt->bind_param("ss", $start_date, $end_date);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (array_key_exists($row['user_type'], $user_counts)) {
            $user_counts[$row['user_type']] += $row['count'];
        }
        
        // Extract course from info field using regular expression
        $info = $row['info'];
        if (preg_match('/\b(BSIT|BSNAME|BSTCM|BSESM|BSMET)\b/', $info, $matches)) {
            $course = $matches[1];
            if (array_key_exists($course, $course_counts)) {
                $course_counts[$course] += $row['count'];
            }
        }
    }
}

// Total user count
$total_users = array_sum($user_counts);
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
    /* Existing styles for charts and printing */
    .chart-container {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        padding: 10px; /* Added padding to create space around charts */
        
        background-color: white;
        border-radius: 15px;
        width: 96%;
    }
    .chart {
        width: 350px; /* Adjust as necessary */
        height: 350px; /* Ensure aspect ratio is maintained */
    }
    .chart-counts {
        text-align: center;
        color: black;
    }
    .chart-counts div {
        margin-top: 10px;
    }
    #date-range-heading{
        text-align: center;
        
    }

/* CSS for printing */
@media print {
    @page {
        size: A4 landscape; /* A4 paper in landscape mode */
        margin: 0mm 45mm 0mm 0mm; /* Adjust margins as needed */
    }

    .sidebar, .fixed-date-time, .search-bar, .print-button, .secondary-navbar {
        display: none !important; /* Hide non-essential elements */
    }

    .content-container {
        padding: 0 !important; /* Remove padding for printing */
    }

    .chart-container {
        display: flex !important;
        flex-wrap: nowrap !important;
        justify-content: center !important; /* Center the charts */
        align-items: center; /* Vertically align the charts */
        padding: 0 !important;
        margin: 0 !important;
        background-color: white !important;
        max-width: 100%; /* Ensure no overflow */
        overflow: hidden; /* Remove the scrollbar */
    }

    .chart {
        width: 45%; /* Adjust to fit both charts side by side */
        max-width: 380px; /* Ensure the charts fit side by side */
        height: auto; /* Maintain aspect ratio */
        margin: 0 10px; /* Add margin between charts */
    }

    .chart-counts {
        display: block !important;
        color: black !important;
        text-align: center;
    }

    body {
        background-color: white !important;
        justify-content: center !important;
        margin: 0 !important;
    }

    #date-range-heading {
        text-align: center;
        width: 100%;
        margin: 0;
        padding: 10px 0;
    }
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
            <a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Charts</a>
            <a href="admin_win.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Top Library User</a>
            <a href="admin_win2.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Top Book Borrrower</a>
        </nav>
    </div>

    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
        <div class="search-bar">
            <!-- Date Filter Form -->
            <form method="get" action="admin_stat.php">
                <?php if (isset($aid)) echo '<input type="hidden" name="aid" value="' . $aid . '">'; ?>
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo isset($start_date) ? $start_date : ''; ?>">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo isset($end_date) ? $end_date : ''; ?>">
                <button type="submit">Filter</button>
            </form>
        </div>

        <div style="background-color: white; width:96%; border-radius: 20px; padding: 10px; margin-top:10px;">
        <div id="date-range-heading">
            <?php
            if ($start_date && $end_date) {
                echo "<h2>Library Users from " . date("F j, Y", strtotime($start_date)) . " to " . date("F j, Y", strtotime($end_date)) . "</h2>";
            } else {
                echo "<h2>All Library Users</h2>";
            }
            ?>
        </div>
        
        <div class="chart-container">
            <div>
                <canvas id="userTypeChart" class="chart"></canvas>
                <div id="userTypeCounts" class="chart-counts">
                    <div>Students: <?php echo $user_counts["Student"]; ?></div>
                    <div>Faculty: <?php echo $user_counts["Faculty"]; ?></div>
                    <div>Staff: <?php echo $user_counts["Staff"]; ?></div>
                    <div>Visitors: <?php echo $user_counts["Visitor"]; ?></div>
                    <div><span style="font-weight: bold;">Total Users: <?php echo $total_users; ?></span></div>
                </div>
            </div>
            <br><br><br><br><br>
            <div>
                <canvas id="courseChart" class="chart"></canvas>
                <div id="courseCounts" class="chart-counts">
                    <div>BSIT: <?php echo $course_counts["BSIT"]; ?></div>
                    <div>BSNAME: <?php echo $course_counts["BSNAME"]; ?></div>
                    <div>BSTCM: <?php echo $course_counts["BSTCM"]; ?></div>
                    <div>BSESM: <?php echo $course_counts["BSESM"]; ?></div>
                    <div>BSMET: <?php echo $course_counts["BSMET"]; ?></div>
                </div>
            </div>
        </div>

        <button class="print-button" onclick="window.print()">Print Charts</button>

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

        document.addEventListener("DOMContentLoaded", function() {
    var ctx1 = document.getElementById('userTypeChart').getContext('2d');
    var userTypeChart = new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: ['Student', 'Faculty', 'Staff', 'Visitor'],
            datasets: [{
                data: [
                    <?php echo $user_counts["Student"]; ?>,
                    <?php echo $user_counts["Faculty"]; ?>,
                    <?php echo $user_counts["Staff"]; ?>,
                    <?php echo $user_counts["Visitor"]; ?>
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderColor: [
                    'rgba(255, 255, 255, 1)',
                    'rgba(255, 255, 255, 1)',
                    'rgba(255, 255, 255, 1)',
                    'rgba(255, 255, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 10,
                    right: 10
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: 'black',
                        font: {
                            size: 16 // Adjust as needed
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'User Type Distribution',
                    color: 'black',
                    font: {
                        size: 20 // Adjust as needed
                    }
                }
            }
        }
    });

    var ctx2 = document.getElementById('courseChart').getContext('2d');
    var courseChart = new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: ['BSIT', 'BSNAME', 'BSTCM', 'BSESM', 'BSMET'],
            datasets: [{
                data: [
                    <?php echo $course_counts["BSIT"]; ?>,
                    <?php echo $course_counts["BSNAME"]; ?>,
                    <?php echo $course_counts["BSTCM"]; ?>,
                    <?php echo $course_counts["BSESM"]; ?>,
                    <?php echo $course_counts["BSMET"]; ?>
                ],
                backgroundColor: [
                    'rgba(54, 54, 54, 1)',   // A shade from black
                    'rgba(64, 130, 255, 1)', // A shade from blue
                    'rgba(148, 75, 175, 1)', // A shade from purple
                    'rgba(80, 190, 120, 1)', // A shade from green
                    'rgba(220, 20, 60, 1)'   // A shade from red
                ],
                borderColor: [
                    'rgba(255, 255, 255, 1)',
                    'rgba(255, 255, 255, 1)',
                    'rgba(255, 255, 255, 1)',
                    'rgba(255, 255, 255, 1)',
                    'rgba(255, 255, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 10,
                    right: 10
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: 'black',
                        font: {
                            size: 16 // Adjust as needed
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Course Distribution',
                    color: 'black',
                    font: {
                        size: 20 // Adjust as needed
                    }
                }
            }
        }
    });
});
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
