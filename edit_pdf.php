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
include 'config.php';

$alertMessage = '';
$aid = isset($_GET['aid']) ? $_GET['aid'] : '';
$pdf_id = isset($_GET['pdf_id']) ? $_GET['pdf_id'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pdf_id = $_POST['pdf_id'];
    $aid = $_POST['aid'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $year = $_POST['year'];
    $genre = $_POST['genre'];
    $add_info = $_POST['add_info'];
    $link = $_POST['link'];

    $update_query = "UPDATE pdf SET title=?, author=?, year=?, genre=?, add_info=?, link=? WHERE pdf_id=?";
    $stmt = $mysqli->prepare($update_query);
    $stmt->bind_param("ssssssi", $title, $author, $year, $genre, $add_info, $link, $pdf_id);
    
    if ($stmt->execute()) {
        $alertMessage = "E-Book updated successfully!";
    } else {
        $alertMessage = "Error: " . $mysqli->error;
    }
    $stmt->close();
}

$query = "SELECT * FROM pdf WHERE pdf_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $pdf_id);
$stmt->execute();
$result = $stmt->get_result();
$pdf = $result->fetch_assoc();
$stmt->close();
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
        <a href="#" class="secondary-navbar-item active">Update E-Book</a>
    </nav>
</div>

<body class="bg">
<div class="content-container">
    <div class="form-container">
        <center><h1>Edit E-Book Information</h1></center>
        <?php if (!empty($alertMessage)) : ?>
            <script>
                Swal.fire({
                    title: 'Notification',
                    text: '<?php echo $alertMessage; ?>',
                    icon: '<?php echo strpos($alertMessage, "success") !== false ? "success" : "error"; ?>'
                }).then(() => {
                    window.location.href = 'admin_pdf.php?aid=<?php echo $aid; ?>';
                });
            </script>
        <?php endif; ?>
        <form method="POST" action="edit_pdf.php">
            <input type="hidden" name="pdf_id" value="<?php echo $pdf_id; ?>">
            <input type="hidden" name="aid" value="<?php echo $aid; ?>">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" value="<?php echo $pdf['title']; ?>" required>
            <label for="author">Author:</label>
            <input type="text" name="author" id="author" value="<?php echo $pdf['author']; ?>" required>
            <label for="year">Year:</label>
            <input type="text" name="year" id="year" value="<?php echo $pdf['year']; ?>" required>
            <label for="genre">Genre:</label>
            <input type="text" name="genre" id="genre" value="<?php echo $pdf['genre']; ?>" required>
            <label for="add_info">Additional Info:</label>
            <input type="text" name="add_info" id="add_info" value="<?php echo $pdf['add_info']; ?>" required>
            <label for="link">Link:</label>
            <input type="text" name="link" id="link" value="<?php echo $pdf['link']; ?>" required>

            <div class="button-container">
                <!-- Cancel button -->
                <button style="width: 100px; background-color:green;" type="submit">Update</button>
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
<br><br>
</body>
</html>
