<?php
// Include database connection
include 'config.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve UID and BID from the request
    $uid = $_POST['uid'];
    $bid = $_POST['bid'];

    // Prepare and execute the SQL statement to insert into the fav table
    $stmt = $mysqli->prepare("INSERT INTO fav (uid, bid) VALUES (?, ?)");
    $stmt->bind_param("ii", $uid, $bid);
    $stmt->execute();

    // Check if insertion was successful
    if ($stmt->affected_rows > 0) {
        // Return a success response
        http_response_code(200);
    } else {
        // Return an error response
        http_response_code(500);
    }

    // Close statement and database connection
    $stmt->close();
    $mysqli->close();
}
?>
