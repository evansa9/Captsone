<?php

$dsn = 'mysql:host=localhost;dbname = zomato';
$username = 'root';
$password = "";

try {
	$db = new PDO($dsn, $username, $password);
}
catch (PDOException $e){
	print "Connection failed: " . $e->getMessage() . "<br/>";
	die();
}

?>