<?php

include_once('config.php');
include_once('getPotw.php');

try {
    $conn = new PDO("firebird:host=".SERVER_FB.";dbname=".DATABASE_FB.";charset=UTF8", USER_DB_FB, PASS_DB_FB);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	//$conn -> query ('SET NAMES WIN1250');
	//$conn -> query ('SET CHARACTER_SET WIN1250');
    echo "<p>Connected successfully</p>";
    }
catch(PDOException $e)
    {
    echo "<p>Connection failed: " . $e->getMessage."</p>"();
    }

?>