<?php
function checkAdminSession() {
    if (!isset($_GET['uid']) || empty($_GET['uid'])) {
        header("Location: login.php");
        exit;
    }
}

// Call the function at the top of your files
checkAdminSession();
?>
<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include 'config.php';

// Initialize variables for username and profile image
$user_username_display = "";
$profile_image_path = "";

// Check if 'uid' parameter is present in the URL
if(isset($_GET['uid'])) {
    $uid = $_GET['uid'];
    
    // Query to fetch the username and profile image corresponding to the uid
    $query = "SELECT username, profile_image FROM users WHERE uid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_username = $user['username'];
        $profile_image_path = $user['profile_image']; // Fetch profile image path
        
        // Set the username for display
        $user_username_display = $user_username;
    } else {
        // Display default values if user data is not found
        $user_username_display = "Username";
        $profile_image_path = "uploads/default.jpg"; // Default profile image
    }
    
    // Close statement
    $stmt->close();
}

// Fetch user data again to populate the form with the latest data
$query = "SELECT * FROM users WHERE uid = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    header("Location: user_dash.php");
    exit();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/user_pf.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
</head>
<body class="bg">
<div class="navbar">
        <div class="navbar-container">
            <img src="css/pics/logop.png" alt="Logo" class="logo">
            <p style="margin-left: 7%;">EasyLib: Library User Experience and Management Through Integrated Monitoring Systems</p>
        </div>
</div>

    <div class="sidebar">
        <div>
            <!-- Display Profile Image -->
            <a href="user_pf.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>"><img src="<?php echo $profile_image_path; ?>" alt="Profile Image" class="profile-image" style="width:100px; height:100px; border-radius:50%;"></a>
        </div>
        <div class="hell">Hello, <?php echo $user_username_display; ?>!</div>
        <a href="user_dash.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Dashboard</a>
        <a href="user_rsrv.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Reserve/Borrow</a>
        <a href="user_ovrd.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Overdue</a>
        <a href="user_fav.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Favorites</a>
        <a href="user_sebk.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">E-Books</a>
        <a href="login.php" class="logout-btn">Logout</a>
    </div>

    <div class="content">
        <nav class="secondary-navbar">
            <a href="user_pf.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="secondary-navbar-item active">Profile</a>
        </nav>
    </div>

    <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span style="font-weight:bold;" id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span style="font-weight:bold;" id="current-time"></span></p>
    </div>

    <div class="content-container">
        <div class="wrapper">
            <div class="form-container">
                <form action="" method="POST" enctype="multipart/form-data" onsubmit="return validateForm();">
                    <h1 style="text-align: center;">Edit Profile</h1>
                    <label for="qr-info"></label>
                    <input type="text" id="qr-info" name="qr-info" placeholder="QR Info" value="<?php echo htmlspecialchars($user['info']); ?>" required><br>

                    <label for="idnum"></label>
                    <input type="text" id="idnum" name="idnum" placeholder="ID Number" value="<?php echo htmlspecialchars($user['idnum']); ?>" required><br>
                    
                    <label for="contact"></label>
                    <input type="text" id="contact" name="contact" placeholder="Contact Number" value="<?php echo htmlspecialchars($user['contact']); ?>" required><br>

                    <label for="username"></label>
                    <input type="text" id="username" name="username" placeholder="Username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br>

                    <label for="password"></label>
                    <input type="password" id="password" name="password" placeholder="New Password"><br>

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

                    <label for="profile_image">Profile Image (Optional)</label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="previewImage(event)"><br>
                    <center>
                    <img id="image_preview" src="<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : ''; ?>" style="display: <?php echo !empty($user['profile_image']) ? 'block' : 'none'; ?>; width: 200px; height: 200px; object-fit: cover; margin-top: 10px;">
                    </center>
                    <br>

                    <div class="button-container">
                        <!-- Save button -->
                        <button style="width: 100px; background-color:green;" type="submit">Save</button>
                        <!-- Cancel button -->
                        <a href="user_dash.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>"><button style="width: 100px; background-color:red;" type="button">Cancel</button></a>
                    </div>
                </form>
                <br>
                <button style="width: 150px;" type="button" onclick="openQRScanner()">Update QR Code</button>
            </div>
        </div>
    </div>

    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
    <script>
        function validateForm() {
            var password = document.getElementById('password').value;
            var repeatPassword = document.getElementById('repeat_password').value;

            if (password !== repeatPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Passwords Do Not Match',
                    text: 'Please make sure the passwords match.',
                });
                return false;
            }

            return true;
        }

        function openQRScanner() {
            Swal.fire({
                title: 'Scan QR Code',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                html: '<video id="qr-video" width="100%" style="max-width: 400px;"></video>',
                didOpen: () => {
                    let scanner = new Instascan.Scanner({ video: document.getElementById('qr-video') });

                    const handleScan = function (content) {
                        // Handle the scanned result as needed
                        handleScannedResult(content);
                        scanner.removeListener('scan', handleScan); // Remove the event listener
                    };

                    scanner.addListener('scan', handleScan);

                    Instascan.Camera.getCameras().then(function (cameras) {
                        if (cameras.length > 0) {
                            // Start the scanner with the first available camera
                            scanner.start(cameras[0]);
                        } else {
                            console.error('No cameras found.');
                            Swal.fire({
                                icon: 'error',
                                title: 'No Cameras Found',
                                text: 'No cameras are available for scanning.',
                            });
                        }
                    }).catch(function (e) {
                        console.error(e);
                        Swal.fire({
                            icon: 'error',
                            title: 'Camera Error',
                            text: 'An error occurred while accessing the camera.',
                        });
                    });
                },
                willClose: () => {
                    // Stop the scanner when the Swal closes
                    let scanner = new Instascan.Scanner({ video: document.getElementById('qr-video') });
                    scanner.stop();
                },
            });
        }

        function handleScannedResult(content) {
            // Do something with the scanned content
            Swal.fire({
                icon: 'success',
                title: 'QR Code Scanned',
                text: 'Scanned content: ' + content,
            });
        }

        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var imagePreview = document.getElementById('image_preview');
                imagePreview.src = reader.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
<?php
// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $info = $_POST['qr-info'];
    $idnum = $_POST['idnum'];
    $contact = $_POST['contact'];
    $username = $_POST['username'];
    $user_type = $_POST['user_type'];
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
        $query = "UPDATE users SET info = ?, contact = ?, username = ?, user_type = ?";

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
            $stmt->bind_param("sssssssi", $info, $idnum, $contact, $username, $user_type, $password, $profile_image, $uid);
        } elseif (!empty($password)) {
            $stmt->bind_param("ssssssi", $info, $idnum, $contact, $username, $user_type, $password, $uid);
        } elseif (!empty($_FILES['profile_image']['name'])) {
            $stmt->bind_param("ssssssi", $info, $idnum, $contact, $username, $user_type, $profile_image, $uid);
        } else {
            $stmt->bind_param("sssssi", $info, $idnum, $contact, $username, $user_type, $uid);
        }

        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Profile Updated',
                    text: 'Your profile has been updated successfully.',
                }).then(function() {
                    window.location = 'user_dash.php?uid=$uid';
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
<script>
// Function to update time
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
    updateTime(); // Call the function to update time immediately
    setInterval(updateTime, 1000); // Update time every second
</script>
</html>
