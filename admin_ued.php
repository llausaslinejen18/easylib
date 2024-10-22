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

// Check if 'uid' parameter is present in the URL
if (isset($_GET['uid'])) {
    $uid = $_GET['uid'];
    // Query to fetch user data corresponding to the uid
    $query = "SELECT * FROM users WHERE uid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        // Redirect if no user found
        header("Location: admin_users.php?aid=$aid");
        exit();
    }
    // Close statement
    $stmt->close();
} else {
    // Redirect if no uid is provided
    header("Location: admin_users.php?aid=$aid");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_adu.css">
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
<a href="admin_dash.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Dashboard</a>
<a href="admin_pf.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Profile</a>
<a href="admin_ued.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Accounts</a>
<a href="admin_attd.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Library Logs</a>
<a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">User Statistics</a>
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
        <a href="" class="secondary-navbar-item active">Edit User</a>
    </nav>
</div>
<div class="content-container">
<div class="wrapper">
    <div class="container">
        <h1 style="text-align: center;">Edit User</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="qr-info"></label>
            <input type="text" id="qr-info" name="qr-info" placeholder="QR Info" value="<?php echo htmlspecialchars($user['info']); ?>" required><br>

            <label for="idnum"></label>
            <input type="text" id="idnum" name="idnum" placeholder="ID Number" value="<?php echo htmlspecialchars($user['idnum']); ?>" required><br>
            
            <label for="contact"></label>
            <input type="text" id="contact" name="contact" placeholder="Contact Number" value="<?php echo htmlspecialchars($user['contact']); ?>" required><br>

            <label for="username"></label>
            <input type="text" id="username" name="username" placeholder="Username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br>

            <label for="password"></label>
            <input type="password" id="password" name="password" placeholder="Password"><br>

            <label for="repeat_password"></label>
            <input type="password" id="repeat_password" name="repeat_password" placeholder="Repeat Password"><br>

            <label for="user_type"></label>
            <center>
            <select id="user_type" name="user_type">
                <option value="">Choose User Type</option>
                <option value="Student" <?php if ($user['user_type'] == 'Student') echo 'selected'; ?>>Student</option>
                <option value="Faculty" <?php if ($user['user_type'] == 'Faculty') echo 'selected'; ?>>Faculty</option>
                <option value="Staff" <?php if ($user['user_type'] == 'Staff') echo 'selected'; ?>>Staff</option>
            </select><br>
            </center>

            <label for="status"></label>
            <center>
                <select id="status" name="status">
                    <option value="Pending" <?php if ($user['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Active" <?php if ($user['status'] == 'Active') echo 'selected'; ?>>Active</option>
                    <option value="Disabled" <?php if ($user['status'] == 'Disabled') echo 'selected'; ?>>Disabled</option>
                </select><br>
            </center>

            <label for="profile_image">Profile Image (Optional)</label>
            <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="previewImage(event)"><br>
            <center>
            <img id="image_preview" src="<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : ''; ?>" style="display: <?php echo !empty($user['profile_image']) ? 'block' : 'none'; ?>; width: 200px; height: 200px; object-fit: cover; margin-top: 10px;">
            </center>
            <br>
            <div class="button-container">
                <button style="width: 100px; background-color:green;" type="submit">Save</button>
                <a href="admin_users.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>"><button style="width: 100px; background-color:red;" type="button">Cancel</button></a>
            </div>
        </form>
    </div>
    <div class="qr-scanner-container">
        <video id="qr-video" width="360px" style="margin-left: 20px;"></video>
        <p class="qr-code-text">Scan ID QR Code</p>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script>
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function () {
        var output = document.getElementById('image_preview');
        output.src = reader.result;
        output.style.display = 'block';
        output.style.width = '200px';
        output.style.height = '200px';
        output.style.objectFit = 'cover';
    };
    reader.readAsDataURL(event.target.files[0]);
}

document.addEventListener('DOMContentLoaded', function () {
    let scanner = new Instascan.Scanner({ video: document.getElementById('qr-video') });
    scanner.addListener('scan', function (content) {
        document.getElementById('qr-info').value = content;
        Swal.fire({
            icon: 'success',
            title: 'Scan Successful',
            text: 'ID QR Code scanned successfully!',
        });
    });

    Instascan.Camera.getCameras().then(function (cameras) {
        if (cameras.length > 0) {
            scanner.start(cameras[0]);
        } else {
            console.error('No cameras found.');
        }
    }).catch(function (e) {
        console.error(e);
    });
});
</script>

<?php
// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $info = $_POST['qr-info'];
    $idnum = $_POST['idnum'];
    $contact = $_POST['contact'];
    $username = $_POST['username'];
    $user_type = $_POST['user_type'];
    $status = $_POST['status'];
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];

    // Check if passwords match
    if ($password !== $repeat_password) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Passwords Do Not Match',
                text: 'Please make sure the passwords match.',
            });
            </script>";
    } else {
        // Prepare to update user information
        $query = "UPDATE users SET info = ?, idnum = ?, contact = ?, username = ?, user_type = ?, status = ?";

        // Add password to query if it's set
        if (!empty($password)) {
            $query .= ", password = ?";
        }

        // Handle profile image upload
        if (!empty($_FILES['profile_image']['name'])) {
            $profile_image = 'uploads/' . basename($_FILES['profile_image']['name']);
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $profile_image);
            $query .= ", profile_image = ?";
        }

        $query .= " WHERE uid = ?";

        // Prepare the statement
        $stmt = $mysqli->prepare($query);

        // Bind parameters based on conditions
        if (!empty($password) && !empty($_FILES['profile_image']['name'])) {
            $stmt->bind_param("ssssssssi", $info, $idnum, $contact, $username, $user_type, $status, $password, $profile_image, $uid);
        } elseif (!empty($password)) {
            $stmt->bind_param("sssssssi", $info, $idnum, $contact, $username, $user_type, $status, $password, $uid);
        } elseif (!empty($_FILES['profile_image']['name'])) {
            $stmt->bind_param("sssssssi", $info, $idnum, $contact, $username, $user_type, $status, $profile_image, $uid);
        } else {
            $stmt->bind_param("ssssssi", $info, $idnum, $contact, $username, $user_type, $status, $uid);
        }

        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Profile Updated',
                    text: 'Your profile has been updated successfully.',
                }).then(function() {
                    window.location = 'admin_users.php?aid=$aid';
                });
                </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: 'There was an error updating your profile.',
                });
                </script>";
        }

        // Close statement
        $stmt->close();
    }
}
?>
</body>
</html>
