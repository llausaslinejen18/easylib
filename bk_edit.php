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

// Check if 'bid' parameter is present in the URL
if(isset($_GET['bid'])) {
    $bid = $_GET['bid'];
    // Query to fetch the book data corresponding to the bid
    $query = "SELECT * FROM inventory WHERE bid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $bid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $book = $result->fetch_assoc();
        // Assign book data to variables for display
        $title = $book['title'];
        $author = $book['author'];
        $year = $book['year'];
        $genre = $book['genre'];
        $dew_num = $book['dew_num'];
        $ISBN = $book['ISBN'];
        $shlf_num = $book['shlf_num'];
        $cndtn = $book['cndtn'];
        $additional_info = $book['add_info'];
        $status = $book['status'];
        $added_by = $book['added_by'];
    } else {
        // Redirect back to bk_inv.php if book data is not found
        header("Location: bk_inv.php?aid=" . $aid);
        exit();
    }
    // Close statement
    $stmt->close();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract data from the form
    $bid = $_POST['bid'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $year = $_POST['year'];
    $genre = $_POST['genre'];
    $dew_num = $_POST['dew_num'];
    $ISBN = $_POST['ISBN'];
    $shlf_num = $_POST['shlf_num'];
    $cndtn = $_POST['cndtn'];
    $additional_info = $_POST['additional_info'];
    $status = $_POST['status'];
    $added_by = $_POST['added_by'];

    // Update the book information in the database
    $query = "UPDATE inventory SET title=?, author=?, year=?, genre=?, dew_num=?, ISBN=?, shlf_num=?, cndtn=?, add_info=?, status=?, added_by=? WHERE bid=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssssssssssi", $title, $author, $year, $genre, $dew_num, $ISBN, $shlf_num, $cndtn, $additional_info, $status, $added_by, $bid);
    $stmt->execute();
    $stmt->close();

    // Redirect back to bk_inv.php
    header("Location: bk_inv.php?aid=" . $aid);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Info Edit</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/edit.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
</head>
<body class="bg">
<nav class="navbar">
    <div class="navbar-container">
        <img src="css/pics/logop.png" alt="Logo" class="logo">
    </div>
</nav>

<div class="wrap">
    <div class="form-container">
    <h2 style="text-align:center">Edit Book Information</h2>
        <!-- Form to add a book -->
        <form id="editForm" method="POST">
            <input type="hidden" name="bid" value="<?php echo $bid; ?>">
            <label for="title">Book Title:</label>
            <input type="text" id="title" name="title" placeholder="Enter book title" required value="<?php echo $title; ?>"><br>

            <label for="author">Book Author:</label>
            <input type="text" id="author" name="author" placeholder="Enter author" required value="<?php echo $author; ?>"><br>

            <label for="year">Year:</label>
            <input type="text" id="year" name="year" placeholder="Enter year" required value="<?php echo $year; ?>"><br>

            <label for="genre">Book Genre:</label>
            <input type="text" id="genre" name="genre" placeholder="Enter genre" value="<?php echo $genre; ?>"><br>

            <label for="dew_num">Dewey Decimal Number:</label>
            <input type="text" id="dew_num" name="dew_num" placeholder="Enter Dewey Decimal number" required value="<?php echo $dew_num; ?>"><br>

            <label for="ISBN">ISBN:</label>
            <input type="text" id="ISBN" name="ISBN" placeholder="Enter ISBN" required value="<?php echo $ISBN; ?>"><br>

            <label for="shlf_num">Shelf Number:</label>
            <input type="text" id="shlf_num" name="shlf_num" placeholder="Enter Shelf Number" required value="<?php echo $shlf_num; ?>"><br>

            <label for="cndtn">Condition:</label>
            <input type="text" id="cndtn" name="cndtn" placeholder="Enter Condition" required value="<?php echo $cndtn; ?>"><br>

            <label for="additional_info">Additional Information:</label>
            <input type="text" id="additional_info" name="additional_info" placeholder="Enter additional information" value="<?php echo $additional_info; ?>"><br>

            <label for="added_by">Added By:</label>
            <input type="text" id="added_by" name="added_by" placeholder="Enter who added the book" required value="<?php echo $added_by; ?>" readonly><br>

            <!-- Dropdown for book status -->
            <label for="status">Book Status:</label>
            <center>
            <select id="status" name="status">
                <option value="Available" <?php if($status == 'Available') echo 'selected'; ?>>Available</option>
                <option value="Not Reservable" <?php if($status == 'Not Reservable') echo 'selected'; ?>>Not Reservable</option>
                <option value="Reserved" <?php if($status == 'Reserved') echo 'selected'; ?>>Reserved</option>
                <option value="Overdue" <?php if($status == 'Overdue') echo 'selected'; ?>>Overdue</option>
            </select><br>
            </center>
            <div class="button-container" style="margin-top:3%;">
                <!-- Cancel button -->
                <button id="saveBtn" style="width: 100px;" type="button">Save</button>
                <a href="bk_inv.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>"><button style="background-color:red; width: 100px;" type="button">Cancel</button></a>
                
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('saveBtn').addEventListener('click', function() {
        Swal.fire({
            title:'Success!',
            text: "Book Information Updated!",
            icon: "success",
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('editForm').submit();
            }
        })
    });
</script>

</body>
</html>
