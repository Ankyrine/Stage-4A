#!/usr/bin/php

<?php
		/* Script d'archivage des tables rooms et salle_possede et enregistrement dans la tableau rooms_archive */ 

		try {

			$db = new PDO('mysql:host=[];dbname=[]', '[]', '[]'); // Création de l'objet PDO
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			/* On selectionne toute les infos brutes sur la salles (nom court des sites et entités) */

			$query = "SELECT * FROM rooms LEFT JOIN entity ON rooms.room_entity_id = entity.id LEFT JOIN sites ON rooms.room_entity_site_id = sites.site_id";
			$sth = $db->prepare($query);
			$sth->execute();
			$datas = $sth->fetchAll();

			if(count($datas) > 0) {

				foreach($datas as $data) {

					/* Ecriture des données dans la table rooms_archives */

					$query_2 = "INSERT INTO archives_rooms (archives_room_entity_name, archives_room_site_name, archives_room_room_name, archives_room_surface_totale, archives_room_surface_unusable, archives_room_surface_baie, archives_room_nbr_total_baie, archives_room_nbr_baies_possible, archives_room_puissance_totale, archives_room_puissance_utilise, archives_room_puissance_moyenne_baie, archives_room_nbr_baie_installable, archives_room_taux_moyen_remplissage, archives_room_comment) VALUES (:archives_room_entity_name, :archives_room_site_name, :archives_room_room_name, :archives_room_surface_totale, :archives_room_surface_unusable, :archives_room_surface_baie, :archives_room_nbr_total_baie, :archives_room_nbr_baies_possible, :archives_room_puissance_totale, :archives_room_puissance_utilise, :archives_room_puissance_moyenne_baie, :archives_room_nbr_baie_installable, :archives_room_taux_moyen_remplissage, :archives_room_comment)";
					$sth_2 = $db->prepare($query_2, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth_2->execute(array(':archives_room_entity_name' => $data['short_name'], ':archives_room_site_name' => $data['site_short_name'], ':archives_room_room_name' => $data['room_name'], ':archives_room_surface_totale' => $data['room_total_area'], ':archives_room_surface_unusable' => $data['room_unusable_area'], ':archives_room_surface_baie' => $data['room_baie_area'], ':archives_room_nbr_total_baie' => $data['nbr_total_baie'], ':archives_room_nbr_baies_possible' => $data['nbr_baies_possible'], ':archives_room_puissance_totale' => $data['room_usable_power'], ':archives_room_puissance_utilise' => $data['room_used_power'], ':archives_room_puissance_moyenne_baie' => $data['room_baie_mean_power'], ':archives_room_nbr_baie_installable' => $data['nbr_baies_installable'], ':archives_room_taux_moyen_remplissage' => $data['taux_moyen_remplissage'], ':archives_room_comment' => $data['room_comment']));

				}

			echo "Archivage réussi";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
				echo "Une erreur est survenue à l'utilisation de cette fonctionalité, l'erreur a été sauvegardée, contactez l'administrateur de l'application\r\n";
				//echo "Échec : " . $e->getMessage(); // Affichage de l'erreur
				$date_file = date('Y-m-d');
				$file_logname = "../error_log/$date_file-err.log";
				$date_log = date('Y-m-d-H-i-s e');
				$error_log = $date_log . " - " . $e . "\r\n";
				error_log($error_log, 3, $file_logname);
		}
?>
