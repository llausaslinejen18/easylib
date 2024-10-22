<?php
/**
 * using mysqli_connect for database connection
 */

$databaseHost = 'localhost';
$databaseName = 'easylib';
$databaseUsername = 'root';
$databasePassword = '';
$port = '3306';

$mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName, $port);
$mysqli->set_charset("utf8");

?>


<?php
/**
 * Using mysqli_connect for database connection for web version
 */

// $databaseHost = 'localhost';  // Hostname of the MySQL server
// $databaseName = 'easylib';     // Name of the database
// $databaseUsername = '';         // Username for database connection
// $databasePassword = '';         // Password for database connection
// $port = '3306'
// // Establishing the connection
// $mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName, $port);

// // Check if the connection was successful
// if (!$mysqli) {
//     die("Connection failed: " . mysqli_connect_error());
// }

// // Set the character set to utf8
// $mysqli->set_charset("utf8");
?>
