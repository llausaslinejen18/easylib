<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Registration</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/reg.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
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

                <label for="profile_image">Profile Image</label>
                <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="previewImage(event)" required><br>
                <center>
                <img id="image_preview" style="display: none; max-width: 100%; margin-top: 10px;">
                </center>
                <div class="button-container">
                    <button style="width: 100px; background-color:green;" type="submit">Register</button>
                    <a href="login.php"><button style="width: 100px; background-color:red;" type="button">Cancel</button></a>
                </div>
            </form>
        </div>
        <div class="qr-scanner-container">
            <video id="qr-video" width="360px" style="margin-left: 20px;"></video>
            <p class="qr-code-text">Scan ID QR Code</p>
        </div>
    </div>

    <!-- Switch Camera Button -->
    <button id="switch-camera" style="position: absolute; bottom: 10px; right: 10px; padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px;">Switch Camera</button>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
    <script>
        // Preview uploaded image
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

        // QR Scanner and Camera Switcher
        document.addEventListener('DOMContentLoaded', function () {
            let scanner = new Instascan.Scanner({ video: document.getElementById('qr-video') });
            let cameras = [];
            let currentCameraIndex = 0;

            scanner.addListener('scan', function (content) {
                document.getElementById('qr-info').value = content;
                Swal.fire({
                    icon: 'success',
                    title: 'Scan Successful',
                    text: 'ID QR Code scanned successfully!',
                });
            });

            Instascan.Camera.getCameras().then(function (availableCameras) {
                cameras = availableCameras;
                if (cameras.length > 0) {
                    scanner.start(cameras[currentCameraIndex]); // Start with the first camera
                } else {
                    console.error('No cameras found.');
                }
            }).catch(function (e) {
                console.error(e);
            });

            // Switch Camera Logic
            document.getElementById('switch-camera').addEventListener('click', function () {
                if (cameras.length > 1) {
                    currentCameraIndex = (currentCameraIndex + 1) % cameras.length;
                    scanner.start(cameras[currentCameraIndex]);
                }
            });
        });
    </script>
</body>
</html>

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
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $target_dir = "uploads/";
                $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);

                // Resize image to 200x200
                $image = $_FILES['profile_image']['tmp_name'];
                $img_info = getimagesize($image);
                $img_type = $img_info[2];

                if ($img_type == IMAGETYPE_JPEG) {
                    $src_img = imagecreatefromjpeg($image);
                } elseif ($img_type == IMAGETYPE_PNG) {
                    $src_img = imagecreatefrompng($image);
                } else {
                    $alertMessage = "Invalid image format. Please upload a JPEG or PNG image.";
                    echo "<script>Swal.fire({icon: 'error', title: 'Error', text: '{$alertMessage}'});</script>";
                    exit();
                }

                $dst_img = imagecreatetruecolor(200, 200);

                if ($img_type == IMAGETYPE_PNG) {
                    // Preserve transparency for PNG
                    imagealphablending($dst_img, false);
                    imagesavealpha($dst_img, true);
                    $transparent = imagecolorallocatealpha($dst_img, 255, 255, 255, 127);
                    imagefill($dst_img, 0, 0, $transparent);
                }

                imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, 200, 200, $img_info[0], $img_info[1]);

                if ($img_type == IMAGETYPE_JPEG) {
                    imagejpeg($dst_img, $target_file);
                } elseif ($img_type == IMAGETYPE_PNG) {
                    imagepng($dst_img, $target_file);
                }

                imagedestroy($src_img);
                imagedestroy($dst_img);

                if (file_exists($target_file)) {
                    $insert_query = "INSERT INTO users (contact, username, password, info, idnum, user_type, profile_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $insert_stmt = $mysqli->prepare($insert_query);
                    $insert_stmt->bind_param("ssssssss", $contact, $username, $password, $qr_info, $idnum, $user_type, $target_file, $status);

                    if ($insert_stmt->execute()) {
                        $alertMessage = "Registration successful.";
                        echo "<script>Swal.fire({icon: 'success', title: 'Success', text: '{$alertMessage}'}).then((result) => {if (result.isConfirmed) {window.location.href = 'login.php';}});</script>";
                    } else {
                        $alertMessage = "Error occurred. Please try again.";
                        echo "<script>Swal.fire({icon: 'error', title: 'Error', text: '{$alertMessage}'});</script>";
                    }

                    $insert_stmt->close();
                } else {
                    $alertMessage = "There was an error uploading the profile image.";
                    echo "<script>Swal.fire({icon: 'error', title: 'Error', text: '{$alertMessage}'});</script>";
                }
            } else {
                $alertMessage = "Please upload a profile image.";
                echo "<script>Swal.fire({icon: 'error', title: 'Error', text: '{$alertMessage}'});</script>";
            }
        }

        $check_stmt->close();
    }
}
?>
