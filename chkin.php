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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Logs</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/qrscan.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.purpose-container {
    display: flex;
    flex-direction: column;   /* Arrange buttons vertically */
    align-items: center;      /* Center the buttons */
}
.purpose-container h3 {
    margin-top: 0;
    margin-bottom: 10px;      /* Add some space below the heading */
}
.purpose-buttons {
    display: flex;
    justify-content: center;  /* Center the buttons horizontally */
    gap: 10px;                /* Add some space between the buttons */
}
.purpose-btn {
    font-size: 17px;
    padding: 10px 20px;
    margin: 5px;
    border: none;
    border-radius: 5px;
    background-color: #ddd;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.purpose-btn.active {
    background-color: gold;
    color: black;
}
.purpose-btn:hover {
    background-color: gold;
    color: black;
}
</style>
</head>
<body class="bg">
    <div class="content">
        <nav class="secondary-navbar">
        <img src="css/pics/logop.png" alt="Logo" class="logo" style="margin-right:20px; cursor: pointer;" onclick="confirmLogout()">
            <a href="chkin.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Time In/Out</a>
        </nav>
    </div>

        <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>
    
    <div class="content-container">
        <div class="container-inner">
            <h1>Scan Here</h1>
            <h2>ID QR Code</h2>
            <video id="qr-video" width="450px"></video>
            <h2>Time In/Out</h2>
        </div>
    </div>
<div class="purpose-container">
    <h3>Select Purpose:</h3>
    <div class="purpose-buttons">
    <button class="purpose-btn" data-purpose="Study">Study</button>
    <button class="purpose-btn" data-purpose="Research">Research</button>
    <button class="purpose-btn" data-purpose="Printing">Printing</button>
    <button class="purpose-btn" data-purpose="Clearance">Clearance</button>
    <button class="purpose-btn" data-purpose="Borrow Book(s)">Borrow Book(s)</button>
    <button class="purpose-btn" data-purpose="Return Book(s)">Return Book(s)</button>
    </div>
</div>
<button id="toggle-camera-btn" class="purpose-btn">Switch Camera</button>

    <div hidden id="scanned-results"></div>

<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let scanner = new Instascan.Scanner({ video: document.getElementById('qr-video') });
    let cameras = [];
    let currentCameraIndex = 0; // Start with the first camera

    scanner.addListener('scan', function (content) {
        handleScannedResult(content);
    });

    Instascan.Camera.getCameras().then(function (availableCameras) {
        cameras = availableCameras;
        if (cameras.length > 0) {
            // Start the scanner with the first available camera
            scanner.start(cameras[currentCameraIndex]);
        } else {
            console.error('No cameras found.');
        }
    }).catch(function (e) {
        console.error(e);
    });

    // Toggle camera button functionality
    document.getElementById('toggle-camera-btn').addEventListener('click', function () {
        if (cameras.length > 1) {
            // Stop the current camera
            scanner.stop();

            // Switch to the next camera
            currentCameraIndex = (currentCameraIndex + 1) % cameras.length;

            // Start the scanner with the new camera
            scanner.start(cameras[currentCameraIndex]);
        } else {
            console.error('No other cameras available.');
        }
    });

    function handleScannedResult(content) {
        const resultContainer = document.createElement('div');
        resultContainer.textContent = 'Scanned: ' + content;
        document.getElementById('scanned-results').appendChild(resultContainer);
        insertScannedData(content);
    }

    let selectedPurpose = '';

    document.querySelectorAll('.purpose-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.purpose-btn').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            selectedPurpose = this.getAttribute('data-purpose');
        });
    });

    function insertScannedData(data) {
        const now = new Date();
        const day = ("0" + now.getDate()).slice(-2);
        const month = ("0" + (now.getMonth() + 1)).slice(-2);
        const year = now.getFullYear();
        const currentDate = `${month}-${day}-${year}`;
        const currentTime = now.toLocaleTimeString();

        fetch('insert_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'info=' + encodeURIComponent(data) + 
                  '&date=' + encodeURIComponent(currentDate) + 
                  '&timein=' + encodeURIComponent(currentTime) + 
                  '&purpose=' + encodeURIComponent(selectedPurpose),
        })
        .then(response => response.text())
        .then(text => {
            const data = JSON.parse(text);
            if (data.success) {
                let titleMessage = '';
                if (data.message.includes("Time-out recorded successfully!")) {
                    titleMessage = 'Time-out recorded successfully. Have a nice day!';
                } else {
                    titleMessage = 'Welcome to the Library!';
                }
                Swal.fire({
                    icon: 'success',
                    title: `<img src="${data.profile_image}" alt="Profile Image" style="border-radius:50%;" width="200" height="200"><br>${titleMessage}`,
                    text: data.info,
                    timer: 5000,
                    showConfirmButton: false
                });
                resetPurposeSelection();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    timer: 5000,
                    showConfirmButton: false
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Fetch Error',
                text: 'An error occurred while sending the data to the server.',
                timer: 5000,
                showConfirmButton: false
            });
        });
    }

    function resetPurposeSelection() {
        document.querySelectorAll('.purpose-btn').forEach(btn => btn.classList.remove('active'));
        selectedPurpose = '';
    }
});
</script>
<script>
        function updateTime() {
            var currentDate = new Date();
            var month = (currentDate.getMonth() + 1).toString().padStart(2, '0'); // Adding 1 to month since it's zero-based index
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

<script>
    function confirmLogout() {
        // Show a confirmation dialog
        Swal.fire({
            title: 'Are you sure you want to logout?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to index.php if the user confirms
                window.location.href = 'index.php';
            }
        });
    }
</script>
</body>
</html>
