<?php
include 'config.php';

if (isset($_GET['info'])) {
    $info = $_GET['info'];
    $fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : null;
    $toDate = isset($_GET['toDate']) ? $_GET['toDate'] : null;

    // Base query to select all data associated with the given info
    $query = "SELECT date, timein, timeout, purpose FROM chkin WHERE info = ?";

    // Append date filtering if both dates are provided
    if ($fromDate && $toDate) {
        // Convert dates to mm-dd-yyyy format for database query
        $fromDate = date("m-d-Y", strtotime($fromDate));
        $toDate = date("m-d-Y", strtotime($toDate));
        $query .= " AND date >= ? AND date <= ?";
    }

    $stmt = $mysqli->prepare($query);

    // Bind parameters based on the presence of date filters
    if ($fromDate && $toDate) {
        $stmt->bind_param("sss", $info, $fromDate, $toDate);
    } else {
        $stmt->bind_param("s", $info);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $details = [];
    while ($row = $result->fetch_assoc()) {
        $details[] = $row;
    }

    // Return the details as JSON
    header('Content-Type: application/json');
    echo json_encode($details);
}
?>
