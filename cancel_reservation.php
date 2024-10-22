<?php
// Include database connection
include 'config.php';

// Check if rid and bid parameters are present
if(isset($_POST['rid']) && isset($_POST['bid'])) {
    // Retrieve rid and bid from the request
    $rid = $_POST['rid'];
    $bid = $_POST['bid'];

    // Update rsv table status to "Cancelled"
    $stmt = $mysqli->prepare("UPDATE rsv SET status = 'Cancelled' WHERE rid = ?");
    $stmt->bind_param("i", $rid);
    $stmt->execute();
    $stmt->close();

    // Update inventory table status to "Available"
    $stmt = $mysqli->prepare("UPDATE inventory SET status = 'Available' WHERE bid = ?");
    $stmt->bind_param("i", $bid);
    $stmt->execute();
    $stmt->close();
}
?>
