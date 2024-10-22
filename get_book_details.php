<?php
// Include database connection
include 'config.php';

// Check if 'bid' parameter is present in the URL
if (isset($_GET['bid'])) {
    $bid = $_GET['bid'];
    
    // Prepare and execute the query
    $query = "SELECT * FROM inventory WHERE bid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $bid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch the book details
    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        echo json_encode($book);
    } else {
        echo json_encode(['error' => 'No details found']);
    }
    
    // Close the statement
    $stmt->close();
}
?>
