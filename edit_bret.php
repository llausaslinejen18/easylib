<?php
// Include database connection
include 'config.php';

// Initialize success message variable
$success = false;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract POST data
    $aid = $_POST['aid'];
    $rid = $_POST['rid'];
    $title = $_POST['book-title'];
    $date_rel = DateTime::createFromFormat('Y-m-d', $_POST['date_rel'])->format('m-d-Y');
    $due_date = DateTime::createFromFormat('Y-m-d', $_POST['due_date'])->format('m-d-Y');

    // Update reservation query
    $query = "UPDATE rsv SET title = ?, date_rel = ?, due_date = ? WHERE rid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sssi", $title, $date_rel, $due_date, $rid);

    if ($stmt->execute()) {
        $response = ['success' => true];
    } else {
        $response = ['success' => false];
    }

    $stmt->close();
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // Terminate the script after sending the response
}

// Check if 'aid' and 'rid' parameters are present in the URL
if(isset($_GET['aid']) && isset($_GET['rid'])) {
    $aid = $_GET['aid'];
    $rid = $_GET['rid'];
    
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
    
    // Query to fetch reservation information
    $query = "SELECT u.info, u.contact, r.title, r.date_rel, r.due_date FROM users u JOIN rsv r ON u.uid = r.uid WHERE r.rid = ?";
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

        // Convert dates to YYYY-MM-DD format for HTML input
        $date_rel = DateTime::createFromFormat('m-d-Y', $reservation['date_rel']);
        $due_date = DateTime::createFromFormat('m-d-Y', $reservation['due_date']);

        if ($date_rel === false || $due_date === false) {
            // Handle the error gracefully
            die("Error: Invalid date format in the database.");
        }

        $date_rel = $date_rel->format('Y-m-d');
        $due_date = $due_date->format('Y-m-d');
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
        <form id="editForm" method="POST" action="edit_bret.php">
            <!-- Input to maintain the current user's aid -->
            <input type="hidden" name="aid" value="<?php echo htmlspecialchars($aid); ?>">
            <input type="hidden" name="rid" value="<?php echo htmlspecialchars($rid); ?>">

            <label for="info">User Info:</label>
            <input type="text" id="info" name="info" value="<?php echo htmlspecialchars($info); ?>" readonly><br>

            <label for="contact">Contact Number:</label>
            <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($contact); ?>" readonly><br>

            <label for="book-title">Book Title:</label>
            <input type="text" id="book-title" name="book-title" value="<?php echo htmlspecialchars($title); ?>" required><br>

            <label for="date_rel">Date Released:</label>
            <input type="date" id="date_rel" name="date_rel" value="<?php echo htmlspecialchars($date_rel); ?>" required><br>

            <label for="due_date">Due Date:</label>
            <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($due_date); ?>"><br><br>

            <div class="button-container">
                <!-- Cancel button -->
                <button id="saveBtn" style="width: 100px;" type="submit">Save</button>
                <a href="admin_bret.php?aid=<?php echo htmlspecialchars($aid); ?>"><button style="width: 100px; background-color:red;" type="button">Cancel</button></a>
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
                    window.location.href = "admin_bret.php?aid=<?php echo htmlspecialchars($aid); ?>";
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
