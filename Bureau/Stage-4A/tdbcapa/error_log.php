<?php
	echo "<font color=\"FE2E2E\">Une erreur est survenue à l'utilisation de cette fonctionalité, l'erreur a été sauvegardée, contactez l'administrateur de l'application</font>\r\n<br />";
	//echo "Échec : " . $e->getMessage(); // Affichage de l'erreur
	$date_file = date('Y-m-d'); // récupération de la date actuelle
	$file_logname = "error_log/$date_file-err.log"; // création du nom du fichier
	$date_log = date('Y-m-d-H-i-s e'); // date & heure actuelle
	$error_log = $date_log . " - " . $e . "\r\n\n"; // chaine à écrire
	error_log($error_log, 3, $file_logname); // création ou modification du fichier en y écrivant la chaine
?>
