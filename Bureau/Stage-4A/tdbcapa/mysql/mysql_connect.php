<?php

try {

	$db = new PDO('mysql:host=[];dbname=[]', '[]', '[]'); // Création de l'objet PDO
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

catch(Exception $e) { // Catch des erreures de connection à la base de donnée

	require('error_log.php');
	$db = null;
}

?>

