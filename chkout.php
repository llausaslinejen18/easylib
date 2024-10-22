<?php
include 'config.php';

// Initialize response array
$response = array('success' => false, 'message' => '', 'profile_image' => '');

// Check if the JSON data is received
$requestData = json_decode(file_get_contents('php://input'), true);

if ($requestData !== null && isset($requestData['data']) && isset($requestData['date']) && isset($requestData['currentTime'])) {
    $data = $requestData['data'];
    $date = $requestData['date'];
    $currentTime = $requestData['currentTime'];

    // Check if the scanned data and current date exist in the "chkin" table
    $query = "SELECT * FROM chkin WHERE info = ? AND date = ?";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        // Bind parameters to the statement
        $stmt->bind_param("ss", $data, $date);

        // Execute the statement
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Debugging: Check if any rows are returned
        if ($result->num_rows > 0) {
            // Loop through all matching rows
            while ($row = $result->fetch_assoc()) {
                // Get the ID of each matched entry
                $chkinID = $row['id'];

                // Check if the timeout is empty
                if ($row['timeout'] === "") {
                    // Update the entry in the "chkin" table with the current time in the "timeout" attribute
                    $updateQuery = "UPDATE chkin SET timeout = ? WHERE id = ?";
                    $updateStmt = $mysqli->prepare($updateQuery);

                    if ($updateStmt) {
                        // Bind parameters to the update statement
                        $updateStmt->bind_param("si", $currentTime, $chkinID);

                        // Execute the update statement
                        if ($updateStmt->execute()) {
                            // Check if there's a matching user in the "users" table
                            $userQuery = "SELECT profile_image FROM users WHERE info = ?";
                            $userStmt = $mysqli->prepare($userQuery);

                            if ($userStmt) {
                                $userStmt->bind_param("s", $data);
                                $userStmt->execute();
                                $userResult = $userStmt->get_result();

                                if ($userResult->num_rows > 0) {
                                    $user = $userResult->fetch_assoc();
                                    $profileImage = $user['profile_image'];
                                } else {
                                    // Use default image if no matching user is found
                                    $profileImage = 'uploads/default.jpg';
                                }

                                $userStmt->close();
                            } else {
                                $profileImage = 'uploads/default.jpg';
                            }

                            // Set success message and profile image
                            $response['success'] = true;
                            $response['message'] = "Checked out successfully. {$data}!";
                            $response['profile_image'] = $profileImage;
                        } else {
                            // Set error message if execution fails
                            $response['message'] = "Execution failed: " . $updateStmt->error;
                        }

                        // Close the update statement
                        $updateStmt->close();
                    } else {
                        // Set error message if preparation fails
                        $response['message'] = "Preparation failed: " . $mysqli->error;
                    }
                } else {
                    // Set error message if entry already has a timeout value
                    $response['message'] = "Error: Entry already has a timeout value.";
                }
            }
        } else {
            $response['message'] = "Error: No matching entry found for the scanned data and date.";
        }

        // Close the statement
        $stmt->close();
    } else {
        // Set error message if preparation fails
        $response['message'] = "Preparation failed: " . $mysqli->error;
    }
} else {
    // Set error message if scanned data, date, or current time parameters are not set
    $response['message'] = "Error: Missing scanned data, date, or current time parameter.";
}

// Return JSON response
echo json_encode($response);
?>
