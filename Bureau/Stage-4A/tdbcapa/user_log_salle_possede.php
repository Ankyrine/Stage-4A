<?php

	// Ecriture des logs utilisateurs concernant l'UPDATE/l'INSERT dans la table salle_possede

	$date_file = date('Y-m-d'); // récupération de la date actuelle
	$file_userlogname = "user_log/$date_file-usr.log"; // création du nom du fichier
	$date_log = date('Y-m-d-H-i-s e'); // date & heure actuelle
	$user_log = "Modification effectuée : " . $query_3_log . "\r\n"; // chaine à écrire
	error_log($user_log, 3, $file_userlogname); // création ou modification du fichier en y écrivant la chaine
?>
