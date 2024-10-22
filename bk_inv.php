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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if 'delete_bid' parameter is present in the POST data
    if (isset($_POST['delete_bid'])) {
        // Delete the book from the database
        $delete_bid = $_POST['delete_bid'];
        $delete_query = "DELETE FROM inventory WHERE bid = ?";
        $delete_stmt = $mysqli->prepare($delete_query);
        $delete_stmt->bind_param("i", $delete_bid);
        $delete_stmt->execute();
        $delete_stmt->close();

        // Redirect back to book inventory page
        header("Location: bk_inv.php?aid=" . $aid);
        exit();
    }
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
    <title>Books Inventory</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_srch.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        <div class="sidebar-item dropdown <?php if (strpos($currentFile, 'bk_inv.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'show'; ?>">
            <a href="#" class="dropdown-link <?php if (strpos($currentFile, 'bk_inv.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'active'; ?>" onclick="toggleDropdown(event)">Inventory</a>
            <div class="dropdown-content <?php if (strpos($currentFile, 'bk_inv.php') !== false || strpos($currentFile, 'add_bk_inv.php') !== false) echo 'show'; ?>">
                <a href="bk_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item <?php if ($currentFile == 'bk_inv.php') echo 'active'; ?>">Books</a>
                <a href="admin_asts_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item <?php if ($currentFile == 'admin_asts_inv.php') echo 'active'; ?>">Assets</a>
            </div>
        </div>
        <a href="login.php" class="sidebar-item logout-btn">Logout</a>
    </div>

    <div class="content">
        <nav class="secondary-navbar">
            <a href="bk_inv.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Books</a>
        </nav>
    </div>
    
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>
    
    <div class="content-container">
        <div class="search-bar">
            <h1>Books Inventory</h1>
            <!-- Search Form -->
            <form action="bk_inv.php" method="GET">
                <input type="hidden" name="aid" value="<?php echo isset($_GET['aid']) ? $_GET['aid'] : ''; ?>">

                <label for="searchTitle" class="label"></label>
                <input type="text" id="searchTitle" name="title" class="input" placeholder="Enter title..." value="<?php echo isset($_GET['title']) ? $_GET['title'] : ''; ?>">

                <label for="searchAuthor" class="label"></label>
                <input type="text" id="searchAuthor" name="author" class="input" placeholder="Enter author..." value="<?php echo isset($_GET['author']) ? $_GET['author'] : ''; ?>">

                <label for="searchGenre" class="label"></label>
                <input type="text" id="searchGenre" name="genre" class="input" placeholder="Enter genre..." value="<?php echo isset($_GET['genre']) ? $_GET['genre'] : ''; ?>">

                <label for="minDewey" class="label"></label>
                <input type="number" id="minDewey" name="minDewey" class="input" placeholder="Min Dewey..." value="<?php echo isset($_GET['minDewey']) ? $_GET['minDewey'] : ''; ?>">

                <label for="maxDewey" class="label"></label>
                <input type="number" id="maxDewey" name="maxDewey" class="input" placeholder="Max Dewey..." value="<?php echo isset($_GET['maxDewey']) ? $_GET['maxDewey'] : ''; ?>">
                
                <button type="submit" style="margin-left:15px;">Search</button>
                <a href="bk_inv.php<?php if(isset($_GET['aid'])) echo '?aid=' . $_GET['aid']; ?>" class="delete-btn">Clear</a> <!-- Added Clear button -->
            </form>
        </div>
        <a href="add_bk_inv.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="add-lib"><i class='fas fa-plus'></i> Add Book(s)</a>
<br><br>
        <!-- Display Table -->
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Year</th>
                    <th>Genre</th>
                    <th>Dewey Number</th>
                    <th>ISBN</th>
                    <th>Shelf Number</th>
                    <th>Condition</th>
                    <th>Additional Info</th>
                    <th>Status</th>
                    <th>Copies</th>
                    <th colspan="2">Actions:</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $query = "SELECT bid, title, author, year, genre, dew_num, ISBN, shlf_num, cndtn, add_info, status, 
                      (SELECT COUNT(*) FROM inventory i2 WHERE i2.title = i1.title AND i2.author = i1.author AND i2.year = i1.year AND i2.genre = i1.genre AND i2.dew_num = i1.dew_num AND i2.ISBN = i1.ISBN AND i2.shlf_num = i1.shlf_num AND i2.cndtn = i1.cndtn AND i2.add_info = i1.add_info AND i2.status = i1.status) AS copies 
                      FROM inventory i1 WHERE 1";

            if (isset($_GET['title']) && !empty($_GET['title'])) {
                $title = $_GET['title'];
                $query .= " AND title LIKE '%$title%'";
            }
            if (isset($_GET['author']) && !empty($_GET['author'])) {
                $author = $_GET['author'];
                $query .= " AND author LIKE '%$author%'";
            }
            if (isset($_GET['genre']) && !empty($_GET['genre'])) {
                $genre = $_GET['genre'];
                $query .= " AND genre LIKE '%$genre%'";
            }
            if (isset($_GET['minDewey']) && !empty($_GET['minDewey'])) {
                $minDewey = $_GET['minDewey'];
                $query .= " AND dew_num >= $minDewey";
            }
            if (isset($_GET['maxDewey']) && !empty($_GET['maxDewey'])) {
                $maxDewey = $_GET['maxDewey'];
                $query .= " AND dew_num <= $maxDewey";
            }

            $result = $mysqli->query($query);

            // Initialize variables to store the last displayed row's values
            $last_row = [];

            if ($result && $result->num_rows > 0) {
                $counter = 1;
                while ($row = $result->fetch_assoc()) {
                    // Store current row values in an array
                    $current_row = [
                        'title' => $row['title'],
                        'author' => $row['author'],
                        'year' => $row['year'],
                        'genre' => $row['genre'],
                        'dew_num' => $row['dew_num'],
                        'isbn' => $row['ISBN'],
                        'shelf_num' => $row['shlf_num'],
                        'condition' => $row['cndtn'],
                        'add_info' => $row['add_info'],
                        'status' => $row['status']
                    ];

                    // Compare current row with the last displayed row
                    if ($current_row !== $last_row) {
                        echo '<tr>';
                        echo '<td>' . $counter++ . '</td>';
                        echo '<td>' . $row['title'] . '</td>';
                        echo '<td>' . $row['author'] . '</td>';
                        echo '<td>' . $row['year'] . '</td>';
                        echo '<td>' . $row['genre'] . '</td>';
                        echo '<td>' . $row['dew_num'] . '</td>';
                        echo '<td>' . $row['ISBN'] . '</td>';
                        echo '<td>' . $row['shlf_num'] . '</td>';
                        echo '<td>'; // Start condition column
                        if ($row['cndtn'] == 'Good') {
                            echo '<span style="color: green;">Good</span>';
                        } elseif ($row['cndtn'] == 'Fair') {
                            echo '<span style="color: orange;">Fair</span>';
                        } elseif ($row['cndtn'] == 'Poor') {
                            echo '<span style="color: red;">Poor</span>';
                        } else {
                            echo $row['cndtn'];
                        }
                        echo '</td>'; // End condition column
                        echo '<td>' . $row['add_info'] . '</td>';
                        echo '<td>' . $row['status'] . '</td>';
                        echo '<td>' . $row['copies'] . '</td>';
                        echo '<td>';
                        echo '<button class="edit-btn" onclick="editBook(' . $row['bid'] . ')"><i class="fas fa-edit"></i></button>';
                        echo '</td>';
                        echo '<td>';
                        echo '<form id="delete_form_' . $row['bid'] . '" method="POST">';
                        echo '<input type="hidden" name="delete_bid" value="' . $row['bid'] . '">';
                        echo '<button class="delete-btn" type="button" onclick="deleteBook(' . $row['bid'] . ')"><i class="fas fa-trash-alt"></i></button>';
                        echo '</form>';
                        echo '</td>';
                        echo '</tr>';

                        // Update last displayed row values
                        $last_row = $current_row;
                    }
                }
            } else {
                echo '<tr><td colspan="13">No data found matching the search criteria.</td></tr>';
            }

            if ($result) {
                $result->free();
            }

            $mysqli->close();
            ?>
            </tbody>
        </table>
    </div>
    <br><br>
</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    // Function to handle book deletion confirmation
    function deleteBook(bid) {
        Swal.fire({
            title: 'Are you sure you want to delete this book?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                    'Deleted!',
                    'The book has been deleted successfully.',
                    'success'
                ).then(() => {
                    // Submit the form for deletion
                    document.getElementById('delete_form_' + bid).submit();
                });
            }
        });
    }

    function editBook(bid) {
        // Get the current URL
        var url = window.location.href;
        // Find the last occurrence of '/'
        var lastSlashIndex = url.lastIndexOf('/');
        // Extract the base URL
        var baseUrl = url.substring(0, lastSlashIndex);
        // Redirect to bk_edit.php with the parameters
        window.location.href = baseUrl + '/bk_edit.php?aid=<?php echo isset($aid) ? $aid : ''; ?>&bid=' + bid;
    }
</script>
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
</html>
