<?php 
$hostname = "localhost"; 
$username = "root"; 
$password = ""; 
$database = "easylib"; 

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) { 
    die("Connection failed: " . mysqli_connect_error()); 
}

// Insert data into the database
if (!empty($_POST['noise_lvl']) && !empty($_POST['rmrks'])) {
    $tbl_num = $_POST['tbl_num'];
    $noise_level = $_POST['noise_lvl'];
    $remarks = $_POST['rmrks'];

    $sql = "INSERT INTO noise(tbl_num, noise_lvl, mrks) VALUES ($tbl_num, $noise_level, '$remarks')"; 

    if ($conn->query($sql) === TRUE) {
        echo "Values inserted into MySQL database table.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close MySQL connection
$conn->close();
?>
