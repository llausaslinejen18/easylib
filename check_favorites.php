<?php
// Include database connection
include 'config.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve UID and BID from the request
    $uid = $_POST['uid'];
    $bid = $_POST['bid'];

    // Prepare and execute the SQL statement to check if the combination exists in fav table
    $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM fav WHERE uid = ? AND bid = ?");
    $stmt->bind_param("ii", $uid, $bid);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there is a result
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $exists = $row['count'] > 0;
        echo json_encode(array('exists' => $exists));
    } else {
        // Return an error response
        http_response_code(500);
    }

    // Close statement and database connection
    $stmt->close();
    $mysqli->close();
}
?>
