<?php
// Content of database.php
 
//$mysqli = new mysqli('localhost', 'damonwy', '644277wY', 'mytest');
$mysqli = new mysqli('localhost', 'newsite', '1120', 'newsite');
if($mysqli->connect_errno) {
	printf("Connection Failed: %s\n", $mysqli->connect_error);
	exit;
}
?>
