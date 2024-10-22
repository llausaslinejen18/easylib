<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rid = $_POST['rid'];
    $uid = $_POST['uid'];
    $bid = $_POST['bid'];
    $info = $_POST['info'];
    $title = $_POST['title'];
    $fines = $_POST['fines'];
    $date_set = $_POST['date_set'];

    // Convert date format from m-d-Y to Y-m-d
    $date_set = DateTime::createFromFormat('m-d-Y', $date_set)->format('Y-m-d');

    // Check if date conversion is successful
    if ($date_set === false) {
        $response['message'] = 'Date conversion failed.';
        echo json_encode($response);
        exit;
    }

    // Start a transaction to ensure all queries succeed or fail together
    $mysqli->begin_transaction();

    try {
        // Insert data into ovrd table
        $insertOvrd = $mysqli->prepare("INSERT INTO ovrd (rid, uid, bid, info, title, fines, date_set) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertOvrd->bind_param("iiissss", $rid, $uid, $bid, $info, $title, $fines, $date_set);
        $insertOvrd->execute();
        
        // Update the status in rsv table to "Settled"
        $updateRsv = $mysqli->prepare("UPDATE rsv SET status = 'Settled' WHERE rid = ?");
        $updateRsv->bind_param("i", $rid);
        $updateRsv->execute();
        
        // Update the status in inventory table to "Available"
        $updateInventory = $mysqli->prepare("UPDATE inventory SET status = 'Available' WHERE bid = ?");
        $updateInventory->bind_param("i", $bid);
        $updateInventory->execute();

        // Commit the transaction
        $mysqli->commit();

        $response['success'] = true;
    } catch (Exception $e) {
        // Rollback the transaction if something went wrong
        $mysqli->rollback();
        $response['message'] = 'Database update failed: ' . $e->getMessage();
    }

    // Close statements
    $insertOvrd->close();
    $updateRsv->close();
    $updateInventory->close();
}

// Send the response back as JSON
header('Content-Type: application/json');
echo json_encode($response);

header('Content-Type: application/json');
$output = json_encode($response);
file_put_contents('response_log.txt', $output);  // Log the output to a file
echo $output;

?>
