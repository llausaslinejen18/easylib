<?php
include 'config.php';

// Get current date in the same format as stored in the database (mm-dd-yyyy)
$currentDate = date("m-d-Y");

// Prepare and execute SQL query to get overdue books
$query_overdue = "
    SELECT rid, title, due_date FROM rsv 
    WHERE status = 'Released' AND STR_TO_DATE(due_date, '%m-%d-%Y') < STR_TO_DATE(?, '%m-%d-%Y')
";
$stmt_overdue = $mysqli->prepare($query_overdue);
$stmt_overdue->bind_param("s", $currentDate);
$stmt_overdue->execute();
$result_overdue = $stmt_overdue->get_result();

// Initialize array to store overdue books
$overdueBooks = [];

// Check if there are results
if ($result_overdue && $result_overdue->num_rows > 0) {
    // Fetch overdue books and store them in the array
    while ($row = $result_overdue->fetch_assoc()) {
        $overdueBooks[] = [
            'rid' => $row['rid'],
            'title' => $row['title'],
            'due_date' => $row['due_date']
        ];
    }
}

// Close the statement
$stmt_overdue->close();

// Return JSON response with overdue books
echo json_encode($overdueBooks);
?>
