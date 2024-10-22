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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Accounts</title>
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
<a href="admin_adu.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Accounts</a>
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
        <a href="admin_adu.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Add User</a>
    </nav>
</div>

<div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

<div class="content-container">
<div class="wrapper">
        <div class="container">
            <h1 style="text-align: center;">Register</h1>
            <form action="" method="POST" enctype="multipart/form-data">
                <label for="qr-info"></label>
                <input type="text" id="qr-info" name="qr-info" placeholder="QR Info" required><br>

                <label for="idnum"></label>
                <input type="text" id="idnum" name="idnum" placeholder="ID Number" required><br>
                
                <label for="contact"></label>
                <input type="text" id="contact" name="contact" placeholder="Contact Number" required><br>

                <label for="username"></label>
                <input type="text" id="username" name="username" placeholder="Username" required><br>

                <label for="password"></label>
                <input type="password" id="password" name="password" placeholder="Password" required><br>

                <label for="repeat_password"></label>
                <input type="password" id="repeat_password" name="repeat_password" placeholder="Repeat Password" required><br>

                <label for="user_type"></label>
                <center>
                <select id="user_type" name="user_type">
                <option value="">Choose User Type</option>
                    <option value="Student">Student</option>
                    <option value="Faculty">Faculty</option>
                    <option value="Staff">Staff</option>
                </select><br>
                </center>

                <label for="profile_image">Profile Image (Optional)</label>
                <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="previewImage(event)"><br>
                <center>
                <img id="image_preview" style="display: none; max-width: 100%; margin-top: 10px;">
                </center>
                <br>
                <div class="button-container">
                    <button style="width: 100px; background-color:green;" type="submit">Register</button>
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
include 'config.php';

$alertMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contact = $_POST['contact'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];
    $qr_info = $_POST['qr-info'];
    $idnum = $_POST['idnum'];
    $user_type = $_POST['user_type'];
    $status = 'Pending';

    if ($password !== $repeat_password) {
        $alertMessage = "Passwords do not match. Please try again.";
        echo "<script>Swal.fire({icon: 'error', title: 'Error', text: '{$alertMessage}'});</script>";
    } else {
        $check_query = "SELECT * FROM users WHERE username = ?";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result && $check_result->num_rows > 0) {
            $alertMessage = "Username already exists. Please choose a different username.";
            echo "<script>Swal.fire({icon: 'error', title: 'Error', text: '{$alertMessage}'});</script>";
        } else {
            $profile_image_path = 'uploads/default.jpg'; // Set default image path

            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $target_dir = "uploads/";
                $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);

                // Check if the file is an image
                $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
                if ($check !== false) {
                    $image_type = $check[2]; // Get image type
                    $image = null;

                    // Create image resource based on type
                    if ($image_type == IMAGETYPE_JPEG) {
                        $image = imagecreatefromjpeg($_FILES["profile_image"]["tmp_name"]);
                    } elseif ($image_type == IMAGETYPE_PNG) {
                        $image = imagecreatefrompng($_FILES["profile_image"]["tmp_name"]);
                    } elseif ($image_type == IMAGETYPE_GIF) {
                        $image = imagecreatefromgif($_FILES["profile_image"]["tmp_name"]);
                    }

                    if ($image !== null) {
                        $width = imagesx($image);
                        $height = imagesy($image);

                        // Create a new true color image with transparency
                        $resized_image = imagecreatetruecolor(200, 200);

                        // Preserve transparency for PNG images
                        if ($image_type == IMAGETYPE_PNG) {
                            imagealphablending($resized_image, false);
                            imagesavealpha($resized_image, true);
                            $transparent = imagecolorallocatealpha($resized_image, 0, 0, 0, 127);
                            imagefill($resized_image, 0, 0, $transparent);
                        }

                        // Resize the image
                        imagecopyresampled($resized_image, $image, 0, 0, 0, 0, 200, 200, $width, $height);

                        // Save the resized image
                        if ($image_type == IMAGETYPE_JPEG) {
                            if (imagejpeg($resized_image, $target_file)) {
                                $profile_image_path = $target_file;
                            }
                        } elseif ($image_type == IMAGETYPE_PNG) {
                            if (imagepng($resized_image, $target_file)) {
                                $profile_image_path = $target_file;
                            }
                        } elseif ($image_type == IMAGETYPE_GIF) {
                            if (imagegif($resized_image, $target_file)) {
                                $profile_image_path = $target_file;
                            }
                        }

                        imagedestroy($image);
                        imagedestroy($resized_image);
                    } else {
                        $alertMessage = "Unsupported image type.";
                        echo "<script>Swal.fire({icon: 'error', title: 'Error', text: '{$alertMessage}'});</script>";
                    }
                } else {
                    $alertMessage = "File is not an image.";
                    echo "<script>Swal.fire({icon: 'error', title: 'Error', text: '{$alertMessage}'});</script>";
                }
            }

            $insert_query = "INSERT INTO users (contact, username, password, info, idnum, user_type, profile_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $mysqli->prepare($insert_query);
            $insert_stmt->bind_param("ssssssss", $contact, $username, $password, $qr_info, $idnum, $user_type, $profile_image_path, $status);

            if ($insert_stmt->execute()) {
                $alertMessage = "Registration successful.";
                echo "<script>Swal.fire({icon: 'success', title: 'Success', text: '{$alertMessage}'}).then((result) => {if (result.isConfirmed) {window.location.href = 'admin_users.php?aid=$aid';}});</script>";
            } else {
                $alertMessage = "Error occurred. Please try again.";
                echo "<script>Swal.fire({icon: 'error', title: 'Error', text: '{$alertMessage}'});</script>";
            }

            $insert_stmt->close();
        }

        $check_stmt->close();
    }
}
?>
</body>
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
