<?php
// Set the timezone to your local timezone
date_default_timezone_set('Asia/Manila'); // Change to your local timezone

// Include database connection
include 'config.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the posted data
    $userInfo = $_POST['userInfo'] ?? '';
    $userType = $_POST['userType'] ?? '';
    $purpose = $_POST['purpose'] ?? '';

    // Validate that all necessary data is present
    if (empty($userInfo) || empty($userType) || empty($purpose)) {
        echo 'error';
        exit;
    }

    // Get the current date and time
    $currentDate = date("m-d-Y");
    $currentTime = date("h:i:s A");

    // Prepare the SQL statement to insert data
    $query = "INSERT INTO chkin (info, user_type, date, timein, purpose) VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param("sssss", $userInfo, $userType, $currentDate, $currentTime, $purpose);

        // Execute the statement and check if it was successful
        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }

        // Close the statement
        $stmt->close();
    } else {
        echo 'error';
    }
} else {
    echo 'error';
}

// Close the database connection
$mysqli->close();
?>
