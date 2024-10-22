<?php
// Include database connection
include 'config.php';

// Prepare and execute SQL query to count pending requests
$query_pending = "SELECT COUNT(*) AS pending_count FROM rsv WHERE status = 'Pending'";
$stmt_pending = $mysqli->prepare($query_pending);
$stmt_pending->execute();
$result_pending = $stmt_pending->get_result();

// Initialize pending count
$pending_count = 0;

// Check if there are results
if ($result_pending && $result_pending->num_rows > 0) {
    $pending_data = $result_pending->fetch_assoc();
    $pending_count = $pending_data['pending_count'];
}

// Free the result set
if ($result_pending) {
    $result_pending->free();
}

// Return the count as JSON
echo json_encode(array('count' => $pending_count));
?>
