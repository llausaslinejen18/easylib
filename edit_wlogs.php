<?php
// Include database connection
include 'config.php';

// Initialize success message variable
$success = false;

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

// Function to convert date from mm-dd-yyyy to yyyy-mm-dd
function convertDateToHtml($date) {
    $dateArray = explode('-', $date);
    if(count($dateArray) == 3) {
        return $dateArray[2] . '-' . $dateArray[0] . '-' . $dateArray[1];
    }
    return $date;
}

// Function to convert date from yyyy-mm-dd to mm-dd-yyyy
function convertDateToDb($date) {
    $dateArray = explode('-', $date);
    if(count($dateArray) == 3) {
        return $dateArray[1] . '-' . $dateArray[2] . '-' . $dateArray[0];
    }
    return $date;
}

// Initialize variables to store reservation information
$rid = "";
$info = "";
$contact = "";
$title = "";
$date_rel = "";
$due_date = "";

// Check if 'rid' parameter is present in the URL
if(isset($_GET['rid'])) {
    $rid = $_GET['rid'];
    // Query to fetch reservation information based on rid
    $query = "SELECT r.info, r.contact, i.title, r.date_rel, r.due_date
              FROM rsv r 
              INNER JOIN inventory i ON r.bid = i.bid 
              WHERE r.rid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $rid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $reservation = $result->fetch_assoc();
        $info = $reservation['info'];
        $contact = $reservation['contact'];
        $title = $reservation['title'];
        // Convert dates from mm-dd-yyyy to yyyy-mm-dd for HTML input
        $date_rel = convertDateToHtml($reservation['date_rel']);
        $due_date = convertDateToHtml($reservation['due_date']);
    } else {
        // Redirect if reservation information is not found
        header("Location: admin_wlogs.php?aid=$aid");
        exit;
    }
    // Close statement
    $stmt->close();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $contact = $_POST['contact'];
    $title = $_POST['book-title'];
    // Convert dates from yyyy-mm-dd to mm-dd-yyyy for database storage
    $date_rel = convertDateToDb($_POST['date_rel']);
    $due_date = convertDateToDb($_POST['due_date']);

    // Update reservation information in the database
    $query = "UPDATE rsv 
              SET contact = ?, date_rel = ?, due_date = ? 
              WHERE rid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssi", $contact, $date_rel, $due_date, $rid);
    $success = $stmt->execute();
    $stmt->close();

    // Return JSON response
    echo json_encode(["success" => $success]);
    exit; // Terminate the script
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reservation Information</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/edit.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>
<body class="bg">
<nav class="navbar">
    <div class="navbar-container">
        <img src="css/pics/logop.png" alt="Logo" class="logo">
    </div>
</nav>

<div class="wrap">
    <div class="form-container">
        <h2 style="text-align:center">Edit Reservation Information</h2>
        <!-- Form to edit reservation information -->
        <form id="editForm" method="POST" action=""> <!-- Updated action to the same file -->
            <!-- Input to maintain the current user's aid -->
            <input type="hidden" name="aid" value="<?php echo $aid; ?>">
            <input type="hidden" name="rid" value="<?php echo $rid; ?>">

            <label for="info">Student Info:</label>
            <input type="text" id="info" name="info" value="<?php echo $info; ?>" readonly><br>

            <label for="contact">Contact Number:</label>
            <input type="text" id="contact" name="contact" value="<?php echo $contact; ?>"><br>

            <label for="book-title">Book Title:</label>
            <input type="text" id="book-title" name="book-title" value="<?php echo $title; ?>" required><br>

            <label for="date_rel">Date Released:</label>
            <input type="date" id="date_rel" name="date_rel" value="<?php echo $date_rel; ?>" required><br>

            <label for="due_date">Return Due:</label>
            <input type="date" id="due_date" name="due_date" value="<?php echo $due_date; ?>" required><br><br>

            <div class="button-container">
                <!-- Cancel button -->
                 <button id="saveBtn" style="width: 100px; background-color:green;" type="submit">Save</button>
                <a href="admin_wlogs.php?aid=<?php echo $aid; ?>"><button style="width: 100px; background-color:red;" type="button">Cancel</button></a>
                
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("editForm").addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent the default form submission
        
        // Perform an asynchronous (Ajax) form submission
        var formData = new FormData(this);

        fetch(this.action, {
            method: this.method,
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Reservation information updated successfully.',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "admin_wlogs.php?aid=<?php echo $aid; ?>";
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong! Please try again.'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong! Please try again.'
            });
        });
    });
});
</script>

</body>
</html>
