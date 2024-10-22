<?php
// Include database connection
include 'config.php';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve cart items and uid
    $cart_items = $_POST['cart_items'];
    $uid = $_POST['uid'];

    // Check if uid is valid and not empty
    if(empty($uid) || !is_numeric($uid)) {
        // Handle invalid uid here (e.g., redirect to an error page)
        exit("Invalid user ID");
    }

    // Explode cart items to get individual book IDs
    $book_ids = explode(",", $cart_items);

    // Initialize alert message
    $alertMessage = '';

    // Update status of selected books to "Reserved" in inventory table
    $update_query = "UPDATE inventory SET status = 'Reserved' WHERE bid IN (" . implode(',', array_map('intval', $book_ids)) . ")";
    $update_result = $mysqli->query($update_query);

    if ($update_result) {
        // Insert user UID, book titles, and "Pending" status into "rsv" table
        $insert_query = "INSERT INTO rsv (uid, bid, title, status) SELECT ?, bid, title, 'Pending' FROM inventory WHERE bid IN (" . implode(',', array_map('intval', $book_ids)) . ")";
        $stmt = $mysqli->prepare($insert_query);
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->close();

        $alertMessage = "Reservation is now pending.";
    } else {
        $alertMessage = "Error updating status of selected books.";
    }

    // Close database connection
    $mysqli->close();

    // Redirect back to user_rsrv.php with the alert message and uid
    header("Location: user_rsrv.php?uid=" . urlencode($uid) . "&alert_message=" . urlencode($alertMessage));
    exit;
}
?>
