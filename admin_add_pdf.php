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
include('config.php');
$alertMessage = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $year = $_POST['year'];
    $genre = $_POST['genre'];
    $add_info = $_POST['add_info'];
    $link = $_POST['link'];

    // Insert data into the database
    $insert_query = "INSERT INTO pdf (title, author, year, genre, add_info, link) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($insert_query);
    $stmt->bind_param("ssssss", $title, $author, $year, $genre, $add_info, $link);
    
    if ($stmt->execute()) {
        $alertMessage = "E-Book added successfully!";
        echo "<script>
        Swal.fire({
            icon: 'success',
            title: '{$alertMessage}',
            showConfirmButton: false,
            timer: 2000
        }).then(() => {
            // Redirect to admin_pdf.php with aid parameter
            window.location.href = 'admin_pdf.php" . (isset($aid) ? "?aid={$aid}" : "") . "';
        });
        </script>";
    } else {
        $alertMessage = "Error: " . $mysqli->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add E-Book</title>
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
    <a href="admin_pf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Profile</a>
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
        <a href="admin_add_pdf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Add E-Book</a>
    </nav>
</div>

<div class="content-container">
    <div class="form-container">
        <center><h1>E-Book Registration</h1></center>
        <?php if (!empty($alertMessage)) : ?>
            <script>
                Swal.fire({
                    icon: '<?php echo strpos($alertMessage, "Error") === false ? "success" : "error"; ?>',
                    title: '<?php echo $alertMessage; ?>',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    // Redirect to admin_pdf.php with aid parameter
                    window.location.href = 'admin_pdf.php<?php if(isset($aid)) echo "?aid=" . $aid; ?>';
                });
            </script>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><?php if(isset($aid)) echo "?aid=" . $aid; ?>" method="POST">
            <label for="title"></label>
            <input type="text" id="title" name="title" placeholder="Title" required><br>

            <label for="author"></label>
            <input type="text" id="author" name="author" placeholder="Author" required><br>

            <label for="year"></label>
            <input type="text" id="year" name="year" placeholder="Year" required><br>

            <label for="genre"></label>
            <input type="text" id="genre" name="genre" placeholder="Genre" required><br>

            <label for="add_info"></label>
            <input type="text" id="add_info" name="add_info" placeholder="Additional Information" required><br>

            <label for="link"></label>
            <input type="text" id="link" name="link" placeholder="Link of the E-Book" required><br><br>

            <div class="button-container">
                <!-- Cancel button -->
                <button style="width: 100px; background-color:green;" type="submit">Register</button>
                <a href="admin_pdf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>"><button style="width: 100px; background-color:red;" type="button">Cancel</button></a>
            </div>
        </form>
    </div>
</div>
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
