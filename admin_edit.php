<?php
// Include database connection
include 'config.php';

// Function to update librarian information
function updateAdmin($selected_aid, $name, $contact, $username, $password, $mysqli) {
    // Query to update librarian information
    $query = "UPDATE libr SET name = ?, contact = ?, username = ?";
    
    // Check if a new password is provided
    if (!empty($password)) {
        $query .= ", password = ?";
    }

    $query .= " WHERE aid = ?";
    
    $stmt = $mysqli->prepare($query);

    if (!empty($password)) {
        $stmt->bind_param("ssssi", $name, $contact, $username, $password, $selected_aid);
    } else {
        $stmt->bind_param("sssi", $name, $contact, $username, $selected_aid);
    }

    if ($stmt->execute()) {
        $stmt->close();
        return true; // Return true if update successful
    } else {
        $stmt->close();
        return false; // Return false if update fails
    }
}

// Initialize variables
$selected_aid = $current_aid = "";
$update_success = false; // Flag to track if the update was successful
$password_match = true; // Flag to track if the old password matches
$new_password_match = true; // Flag to track if the new password matches the repeated password

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if 'selected_aid' and 'current_aid' parameters are present in the POST data
    if (isset($_POST['selected_aid'], $_POST['current_aid'])) {
        $selected_aid = $_POST['selected_aid'];
        $current_aid = $_POST['current_aid'];

        // Check if name, contact, and username are set
        if (isset($_POST['name'], $_POST['contact'], $_POST['username'], $_POST['old-password'])) {
            $name = $_POST['name'];
            $contact = $_POST['contact'];
            $username = $_POST['username'];
            $old_password = $_POST['old-password']; // Get the old password

            // Query to fetch the librarian information based on the selected aid
            $query = "SELECT * FROM libr WHERE aid = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $selected_aid);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if the result is not empty
            if ($result && $result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                // Check if old password matches the stored password
                if ($old_password == $admin['password']) {
                    // Old password matches
                    if (isset($_POST['new-password'], $_POST['repeat_password'])) {
                        $new_password = $_POST['new-password'];
                        $repeat_password = $_POST['repeat_password'];
                        // Check if new password matches the repeated password
                        if ($new_password == $repeat_password) {
                            // New password matches, update admin information
                            $update_success = updateAdmin($selected_aid, $name, $contact, $username, $new_password, $mysqli);
                        } else {
                            $new_password_match = false; // New password does not match the repeated password
                        }
                    } else {
                        // New password fields not set
                        $update_success = updateAdmin($selected_aid, $name, $contact, $username, $old_password, $mysqli);
                    }
                } else {
                    $password_match = false; // Old password does not match
                }
            } else {
                // Admin not found
                header("Location: error.php");
                exit();
            }
            $stmt->close();
        }
    } else {
        // If 'selected_aid' and 'current_aid' are not in POST data, redirect to a proper error page
        header("Location: error.php");
        exit();
    }
}

// Fetch librarian information based on selected aid
$query = "SELECT * FROM libr WHERE aid = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $selected_aid);
$stmt->execute();
$result = $stmt->get_result();

// Check if the result is not empty
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
} else {
    // If admin information not found, handle the error
    header("Location: error.php");
    exit();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin Information</title>
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
        <h2 style="text-align:center">Edit Librarian Information</h2>
        <!-- Form to edit librarian information -->
        <form id="editForm" method="POST">
            <!-- Input to maintain the current user's aid -->
            <input type="hidden" name="current_aid" value="<?php echo $current_aid; ?>">
            <!-- Input to pass selected aid -->
            <input type="hidden" name="selected_aid" value="<?php echo $selected_aid; ?>">

            <label for="name"></label>
            <!-- You can pre-fill the form fields with fetched data -->
            <input type="text" id="name" name="name" placeholder="Name" value="<?php echo isset($admin['name']) ? $admin['name'] : ''; ?>" required><br>

            <label for="contact"></label>
            <input type="text" id="contact" name="contact" placeholder="Contact Number" value="<?php echo isset($admin['contact']) ? $admin['contact'] : ''; ?>" required><br>

            <label for="username"></label>
            <input type="text" id="username" name="username" placeholder="Username" value="<?php echo isset($admin['username']) ? $admin['username'] : ''; ?>" required><br>

            <label for="old-password"></label>
            <input type="password" id="old-password" name="old-password" placeholder="Old Password" required>

            <p style="font-size: 14px;"><i>Input new password for updating a new password</i></p>
            <label for="new-password"></label>
            <input type="password" id="new-password" name="new-password" placeholder="New Password"><br>

            <label for="repeat_password"></label>
            <input type="password" id="repeat_password" name="repeat_password" placeholder="Repeat Password"><br><br>

            <div class="button-container">
                <!-- Cancel button -->
                <button id="saveBtn" style="width: 100px; background-color:green;" type="submit">Save</button>
                <a href="admin_srch.php?aid=<?php echo $current_aid; ?>"><button style="width: 100px; background-color:red;" type="button">Cancel</button></a>
            </div>
        </form>
    </div>
</div>

<!-- Script for SweetAlert -->
<script>
    <?php
    if ($update_success) {
        echo 'Swal.fire({
                icon: "success",
                title: "Update Successful",
                showConfirmButton: false,
                timer: 1500
            }).then(function() {
                window.location.href = "admin_srch.php?aid=' . $current_aid . '";
            });';
    }

    if (!$password_match) {
        echo 'Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Old password does not match.",
            });';
    }

    if (!$new_password_match) {
        echo 'Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "New password does not match the repeated password.",
            });';
    }
    ?>
</script>

</body>
</html>
