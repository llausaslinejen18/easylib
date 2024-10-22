<?php
include 'config.php';

// Check if 'fid' parameter is present in the POST request
if(isset($_POST['fid'])) {
    $fid = $_POST['fid'];

    // Delete the favorite book from the 'fav' table
    $delete_query = "DELETE FROM fav WHERE fid = ?";
    $stmt = $mysqli->prepare($delete_query);
    $stmt->bind_param("i", $fid);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // If deletion is successful, send a success message
        echo "success";
    } else {
        // If deletion fails, send an error message
        echo "error";
    }

    // Close statement and database connection
    $stmt->close();
    $mysqli->close();
} else {
    // If 'fid' parameter is not present, send an error message
    echo "error";
}
?>