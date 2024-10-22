<?php
// Include database connection
include 'config.php';

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
?>
<?php
function getCurrentFileName() {
    return basename($_SERVER['PHP_SELF']);
}
$currentFile = getCurrentFileName();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/add_bk_inv.css">
    <!-- Include SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
        <a href="admin_brel.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Borrowed Books</a>
        <a href="admin_ob.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Overdue Books</a>
        <div class="sidebar-item dropdown <?php if (strpos($currentFile, 'admin_add_asts.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'show'; ?>">
            <a href="#" class="dropdown-link <?php if (strpos($currentFile, 'admin_add_asts.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'active'; ?>" onclick="toggleDropdown(event)">Inventory</a>
            <div class="dropdown-content <?php if (strpos($currentFile, 'admin_add_asts.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'show'; ?>">
                <a href="add_bk_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item <?php if ($currentFile == 'add_bk_inv.php') echo 'active'; ?>">Books</a>
                <a href="admin_add_asts.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item <?php if ($currentFile == 'admin_add_asts.php') echo 'active'; ?>">Assets</a>
            </div>
        </div>
        <a href="login.php" class="sidebar-item logout-btn">Logout</a>
    </div>

    <div class="content">
        <nav class="secondary-navbar">
            <a href="add_bk_inv.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Add</a>
        </nav>
    </div>
    
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
        <div class="form-container">
            <center><h2>Add Book to Inventory</h2></center>
            <!-- PHP code for handling form submission and displaying alerts -->
            <?php
            // Initialize alert message
            $alertMessage = '';

            // Check if form is submitted
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Retrieve form data
                $title = $_POST['title'];
                $author = $_POST['author'];
                $year = $_POST['year'];
                $genre = $_POST['genre'];
                $dew_num = $_POST['dew_num'];
                $ISBN = $_POST['ISBN'];
                $shlf_num = $_POST['shlf_num'];
                $condition = $_POST['condition'];
                $count = $_POST['count'];
                $add_info = $_POST['additional_info'];
                $status = $_POST['status'];

                // Prepare SQL insert statement
                $query = "INSERT INTO inventory (title, author, year, genre, dew_num, ISBN, shlf_num, cndtn, add_info, status, added_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $mysqli->prepare($query);

                // Bind parameters and execute query for each copy
                $stmt->bind_param("sssssssssss", $title, $author, $year, $genre, $dew_num, $ISBN, $shlf_num, $condition, $add_info, $status, $admin_username);
                $successful_inserts = 0;

                for ($i = 0; $i < $count; $i++) {
                    $stmt->execute();
                    if ($stmt->affected_rows > 0) {
                        $successful_inserts++;
                    }
                }

                // Check if all insertions were successful
                if ($successful_inserts == $count) {
                    $alertMessage = "Book(s) added successfully.";
                    // Show success message using SweetAlert
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: '{$alertMessage}',
                                });
                            });
                        </script>";
                } else {
                    $alertMessage = "Error adding books: " . $mysqli->error;
                    // Show error message using SweetAlert
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: '{$alertMessage}',
                                });
                            });
                        </script>";
                }

                // Close statement
                $stmt->close();
            }
            ?>
            <!-- Form to add a book -->
            <form action="add_bk_inv.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" method="POST">
                <label for="title"></label>
                <input type="text" id="title" name="title" placeholder="Enter Book Title" required><br>

                <label for="author"></label>
                <input type="text" id="author" name="author" placeholder="Enter Author" required><br>

                <label for="year"></label>
                <input type="text" id="year" name="year" placeholder="Enter Year Published" required><br>

                <label for="genre"></label>
                <input type="text" id="genre" name="genre" placeholder="Enter Genre"><br>

                <label for="dew_num"></label>
                <input type="text" id="dew_num" name="dew_num" placeholder="Enter Dewey Decimal Number" required><br>

                <label for="ISBN"></label>
                <input type="text" id="ISBN" name="ISBN" placeholder="Enter ISBN" required><br>

                <label for="shlf_num"></label>
                <input type="text" id="shlf_num" name="shlf_num" placeholder="Enter Shelf Number" required><br>

                <label for="condition"></label>
                <input type="text" id="condition" name="condition" placeholder="Enter Book Condition" required><br>

                <label for="count"></label>
                <input type="number" id="count" name="count" placeholder="Enter Number of Copies" required><br>

                <label for="additional_info"></label>
                <input type="text" id="additional_info" name="additional_info" placeholder="Enter Additional Information"><br>

                <!-- Dropdown for book status -->
                <label for="status">Book Status:</label>
                <select id="status" name="status">
                    <option value="Available">Available</option>
                    <option value="Not Reservable">Not Reservable</option>
                </select><br>

                <div class="button-container" style="margin-top:3%;">
                    <!-- Cancel button -->
                    <button style="width: 100px;" type="submit">Add Book</button>
                    <a href="bk_inv.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>"><button style="width: 100px; background-color:red;" type="button">Cancel</button></a>
                </div>
            </form>
        </div>
    </div>
    <!-- Include SweetAlert JavaScript library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
