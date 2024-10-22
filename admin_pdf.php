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

// Delete functionality
if(isset($_POST['delete_pdf_id'])) {
    $delete_pdf_id = $_POST['delete_pdf_id'];
    
    // Query to delete PDF record
    $query = "DELETE FROM pdf WHERE pdf_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $delete_pdf_id);
    
    if($stmt->execute()) {
        // Deletion successful
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>"; // Include SweetAlerts library
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The PDF has been deleted successfully.',
                        icon: 'success'
                    }).then(() => {
                        window.location.href = 'admin_pdf.php?aid=$aid';
                    });
                });
              </script>";
    } else {
        // Deletion failed
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@10'></script>"; // Include SweetAlerts library
        echo "<script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete PDF.',
                        icon: 'error'
                    });
                });
              </script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Books</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_srch.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
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
        <a href="admin_pdf.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Search E-Books</a>
    </nav>
</div>

<div class="content-container">
    <div class="search-bar">
        <h1>Search E-Books</h1>
        <input type="text" id="searchTitle" placeholder="Search by Title..." onkeyup="searchTable()">
        <input type="text" id="searchAuthor" placeholder="Search by Author..." onkeyup="searchTable()">
        <input type="text" id="searchYear" placeholder="Search by Year..." onkeyup="searchTable()">
        <input type="text" id="searchGenre" placeholder="Search by Genre..." onkeyup="searchTable()">
        <button class="delete-btn" id="clearButton" onclick="clearSearch()">Clear</button>
    </div>
    <a href="admin_add_pdf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="add-lib"><i class='fas fa-plus'></i> Add E-Book</a>

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
                <th colspan="2">Actions:</th>
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
                // Add edit and delete buttons
                echo '<td style="text-align:center;">';
                echo '<button class="edit-btn" onclick="editPDF(' . $row['pdf_id'] . ', ' . $aid . ')"><i class="fas fa-edit"></i></button>';
                echo '</td>';
                echo '<td style="text-align:center;">';
                echo '<button class="delete-btn" onclick="deletePDF(' . $row['pdf_id'] . ')"><i class="fas fa-trash-alt"></i></button>';
                echo '</td>';
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>


<form id="editForm" action="edit_pdf.php" method="get">
    <input type="hidden" name="pdf_id" id="selected_pdf_id">
    <input type="hidden" name="aid" id="current_aid">
</form>

<!-- Form for delete -->
<form id="deleteForm" method="post">
    <input type="hidden" name="delete_pdf_id" id="delete_pdf_id">
</form>

<script>
function editPDF(pdfId, aid) {
    document.getElementById('selected_pdf_id').value = pdfId;
    document.getElementById('current_aid').value = aid;
    document.getElementById('editForm').submit();
}

function deletePDF(deletePdfId) {
    Swal.fire({
        title: 'Are you sure you want to delete this PDF?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_pdf_id').value = deletePdfId;
            document.getElementById('deleteForm').submit();
        }
    });
}

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
</body>
</html>
