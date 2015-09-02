<?php

	// Ecriture des logs utilisateurs concernant l'UPDATE/l'INSERT dans la table rooms

	$date_file = date('Y-m-d'); // récupération de la date actuelle
	$file_userlogname = "user_log/$date_file-usr.log"; // création du nom du fichier
	$date_log = date('Y-m-d-H-i-s e'); // date & heure actuelle
	$user_log = "\n" . $date_log . "\nModification de la salle : " . $room->_name . " (Site : " . $room->_siteShortName . " - Entité : " . $user->returnShortName() . ") par l'utilisateur (" . $user->retrieveFirstname() . " " . $user->retrieveLastname() . " - " . $user->retrieveLogin() . ")\r\nModification effectuée : " . $query_log . "\r\n"; // chaine à écrire
	error_log($user_log, 3, $file_userlogname); // création ou modification du fichier en y écrivant la chaine
?>
