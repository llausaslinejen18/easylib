<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

// Initialize response array
$response = array('success' => false, 'message' => '', 'info' => '', 'profile_image' => '');

// Check if the required parameters are set in the POST request
if (isset($_POST['info']) && isset($_POST['date']) && isset($_POST['timein']) && isset($_POST['purpose'])) {
    // Extract data from POST request
    $info = $_POST['info'];
    $date = $_POST['date'];
    $timein = $_POST['timein'];
    $purpose = $_POST['purpose'];

    // Check if the user has already checked in on the same date
    $check_query = "SELECT id, timeout FROM chkin WHERE info = ? AND date = ? ORDER BY timein DESC LIMIT 1";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param("ss", $info, $date);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result && $check_result->num_rows > 0) {
        // Fetch the last check-in record
        $last_checkin = $check_result->fetch_assoc();

        if ($last_checkin['timeout'] === '') {
            // User has checked in but not timed out, so update the timeout

            // Set default profile image, user type, and idnum
            $profile_image = 'uploads/default.jpg';
            $user_type = '';
            $idnum = '';

            // Check the users table for a matching info
            $user_query = "SELECT profile_image, user_type, idnum FROM users WHERE info = ?";
            $user_stmt = $mysqli->prepare($user_query);
            $user_stmt->bind_param("s", $info);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();

            if ($user_result && $user_result->num_rows > 0) {
                // Match found, fetch profile_image, user_type, and idnum
                $user = $user_result->fetch_assoc();
                $profile_image = $user['profile_image'];
                $user_type = $user['user_type'];
                $idnum = $user['idnum'];
            }

            // Now update the timeout
            $update_query = "UPDATE chkin SET timeout = ? WHERE id = ?";
            $update_stmt = $mysqli->prepare($update_query);
            $update_stmt->bind_param("si", $timein, $last_checkin['id']);

            if ($update_stmt->execute()) {
                // Set success message after updating timeout
                $response['success'] = true;
                $response['message'] = "Time-out recorded successfully!";
                $response['info'] = $info; // Include the scanned info in the response
                $response['profile_image'] = $profile_image; // Include the profile image
            } else {
                // Set error message if execution fails
                $response['message'] = "Timeout update failed: " . $update_stmt->error;
            }
            $update_stmt->close();
        } else {
            // User has already timed out, allow a new check-in
            insertNewCheckin($mysqli, $info, $date, $timein, $purpose, $response);
        }
    } else {
        // No prior check-in found today, allow a new check-in
        insertNewCheckin($mysqli, $info, $date, $timein, $purpose, $response);
    }

    // Close check statement
    $check_stmt->close();
} else {
    // Set error message if required parameters are missing
    $response['message'] = "Error: Missing required parameters.";
}

// Return JSON response
header('Content-Type: application/json'); // Ensure the correct content type is set
echo json_encode($response);

// Function to handle new check-ins
function insertNewCheckin($mysqli, $info, $date, $timein, $purpose, &$response) {
    // Check the users table for a matching info
    $user_query = "SELECT profile_image, user_type, idnum FROM users WHERE info = ?";
    $user_stmt = $mysqli->prepare($user_query);
    $user_stmt->bind_param("s", $info);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    // Set default values
    $profile_image = 'uploads/default.jpg'; // Default image
    $user_type = ''; 
    $idnum = '';

    if ($user_result && $user_result->num_rows > 0) {
        // Match found, fetch profile_image, user_type, and idnum
        $user = $user_result->fetch_assoc();
        $profile_image = $user['profile_image'];
        $user_type = $user['user_type'];
        $idnum = $user['idnum'];
    }

    // Insert the data into the "chkin" table, including the idnum and purpose
    $query = "INSERT INTO chkin (info, date, timein, user_type, purpose, idnum) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        // Bind parameters to the statement
        $stmt->bind_param("ssssss", $info, $date, $timein, $user_type, $purpose, $idnum);

        // Execute the statement
        if ($stmt->execute()) {
            // Set success message
            $response['success'] = true;
            $response['message'] = "Welcome to the Library!";
            $response['info'] = $info; // Include the scanned info in the response
            $response['profile_image'] = $profile_image;
        } else {
            // Set error message if execution fails
            $response['message'] = "Execution failed: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        // Set error message if preparation fails
        $response['message'] = "Preparation failed: " . $mysqli->error;
    }

    // Close user statement
    $user_stmt->close();
}
?>
