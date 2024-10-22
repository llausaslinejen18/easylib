<?php
include 'config.php';

// Get the list of book IDs from the POST request
$bookIDs = $_POST['bookIDs'];

// Prepare and execute SQL query to update the status of overdue books
$query_update_status = "UPDATE rsv SET status = 'Overdue' WHERE rid = ?";
$stmt_update_status = $mysqli->prepare($query_update_status);

// Iterate over each book ID and update its status
foreach ($bookIDs as $bookID) {
    $stmt_update_status->bind_param("i", $bookID);
    $stmt_update_status->execute();
}

// Close the statement
$stmt_update_status->close();

// Send a response indicating success
echo json_encode(['success' => true]);
?>
