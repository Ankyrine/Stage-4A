<?php require('session.php'); // Inclusion de la session utilisateur, et de la connection à la BDD ?>
<?php require('class.rooms.php'); ?>
<?php require('class.entity.php'); ?>
<?php require('class.sites.php'); ?>
<?php 
	if($user->userAuth() == 1) { // Seul les employés des entités peuvent modifier les données de leurs salles respectives
 
		echo"<html>
			<head>
				<title> Gestion de vos salles </title>
				<link rel=\"stylesheet\" href=\"css/style_2.css\" />
				<meta charset=\"UTF-8\">
			</head>
			<body>";
		require('top.php');
		echo"<div id=\"body_content\">
			<div class=\"body\">";

		/* Traitement des données passé par le formulaire via la méthode POST */

		if(isset($_POST['surface_salle']) && isset($_POST['unusable_surface_salle']) && isset($_POST['baie_100p']) && isset($_POST['baie_75p']) && isset($_POST['baie_50p']) && isset($_POST['baie_25p']) && isset($_POST['baie_0p']) && isset($_POST['pw_salle']) && isset($_POST['pw_used_salle']) && isset($_POST['baies']) && isset($_POST['comment']) && isset($_GET['id'])) {

			$surface_salle = $_POST['surface_salle'];
			$unusable_surface_salle =  $_POST['unusable_surface_salle'];
			$id = $_GET['id'];

			$comment = htmlentities($_POST['comment']); // Traitement du champ texte pour éviter les injections de HTML/javascript en BDD

			$short_name = $user->returnShortName(); // On récupère le nom court de l'entité de l'utilisateur (objet user global (utilisé dans session.php))

			$room = new Room($id, null, null, null , null, null, null, null, null, null, null, null, null, null, null); // création de l'objet room
	
			if($room->checkID($db, $short_name)) { // On vérifie que la salle modifiée appartient bien a l'entité dont fait parti l'employé

				if($surface_salle >= $unusable_surface_salle) { // Si les données de surface sont cohérentes (par une plus grand surface inutile que de surface totale)

					$pw_salle = $_POST['pw_salle'];
					$pw_used_salle = $_POST['pw_used_salle'];

					if($pw_salle >= $pw_used_salle) { // Si les données de puissance sont cohérente (pas plus de puissance utilisée que de puissance disponible au total)

						$baies = $_POST['baies'];

						$nbr_baie = 0;

						foreach($baies as $baie) {

							$nbr_baie += $baie; // Compte total du nombre de baie de la pièce
						}

						$nbr_baie_100p = $_POST['baie_100p'];
						$nbr_baie_75p = $_POST['baie_75p'];
						$nbr_baie_50p = $_POST['baie_50p'];
						$nbr_baie_25p = $_POST['baie_25p'];
						$nbr_baie_0p = $_POST['baie_0p'];

						$nbr_baie_2 = 0;
						$nbr_baie_2 = $nbr_baie_100p + $nbr_baie_75p + $nbr_baie_50p + $nbr_baie_25p + $nbr_baie_0p;

						/* Calcul de la consommation energétique moyenne des baies */

						if($nbr_baie != 0)				
							$mean_pw_baie = number_format(($pw_used_salle / $nbr_baie), 2);
						else
							$mean_pw_baie = 0;

						if($nbr_baie == $nbr_baie_2) { // Si le nombre de baie renseignée dans le "taux d'occupation" est identique au nombre de baie renseigné dans les types de baie que possède la salle

							/* Calcul du taux de remplissage moyen */

							if($nbr_baie != 0)
								$taux_moyen_remplissage = number_format((($nbr_baie_100p + ($nbr_baie_75p * 0.75) + ($nbr_baie_50p * 0.5) + ($nbr_baie_25p * 0.25)) / $nbr_baie), 2);
							else
								$taux_moyen_remplissage = 0;

							$surface_disponible = $surface_salle - $unusable_surface_salle;

							try {
								if($db != null) {

									// On récupère la surface au sol du modèle type de baie pour de future installation

									$query = "SELECT ground_area FROM baies WHERE base_model=1";
									$sth = $db->prepare($query);
									$sth->execute();
									$datas = $sth->fetchAll();

									if(count($datas) == 1) { // Si la requête retourne un unique résultat

										foreach($datas as $data)
											$baie_ground_area = $data['ground_area']; // On stocke la donnée

										// On récupère les données sur l'ensemble des baies

										$query = "SELECT * FROM baies";
										$sth = $db->prepare($query);
										$sth->execute();
										$datas = $sth->fetchAll();

										$used_area = 0;
										$i = 0;

										if(count($datas) > 0) {

											// Calcul de la surface au sol occupée par les baies

											foreach($datas as $data) {

												$used_area += $data['ground_area'] * $baies[$i];
												$i++; 
											}

											if(($surface_disponible - $used_area) > 0) { // On calcul la surface restante pour la salle, et si c'est cohérent (Il reste encore de la place)

												/* On calcul le nombre de baie que l'on peut encore installer en ne prenant en compte uniquement le volume */

												$nbr_baie_total = floor((($surface_salle - $unusable_surface_salle - $used_area) / $baie_ground_area));

												/* On calcul le nombre de baie que l'on peut encore installer en ne prenant en compte uniquement la puissance */

												if($nbr_baie == 0) {

													$nbr_baie_total_power = 0;
												}
												else {

													$nbr_baie_total_power = floor((($pw_salle - $pw_used_salle) / $mean_pw_baie));
												}

												/* On détermine quel est le nombre réel de baie installable */

												if($nbr_baie_total > $nbr_baie_total_power)
													$nbr_baie_possible = $nbr_baie_total_power;
												else
													$nbr_baie_possible = $nbr_baie_total;

												/* On récupère le nom court de l'entité et du site pour l'afficher en clair dans les logs user */

												$room->retrieveShortNames($db);

												/*** Requete BDD ***/
		
												$query = "UPDATE rooms SET room_total_area=:surface_salle, room_unusable_area=:unusable_surface_salle, room_baie_area=:used_area, nbr_baies_possible=:nbr_baies_possible, nbr_baie_100=:nbr_baie_100p, nbr_baie_75=:nbr_baie_75p, nbr_baie_50=:nbr_baie_50p, nbr_baie_25=:nbr_baie_25p, nbr_baie_0=:nbr_baie_0p, taux_moyen_remplissage=:taux_moyen_remplissage, nbr_total_baie=:nbr_baie, nbr_baies_installable=:nbr_baies_installable, room_usable_power=:pw_salle, room_used_power=:pw_used_salle, room_baie_mean_power=:room_baie_mean_power, room_comment=:room_comment WHERE room_id=:id";
												/*******************/
												/*** Requête LOG ***/

												$query_log = "UPDATE rooms SET room_total_area='" . $surface_salle . "', room_unusable_area='" . $unusable_surface_salle . "', room_baie_area='" . $used_area . "', nbr_baies_possible='" . $nbr_baie_total . "', nbr_baie_100='" . $nbr_baie_100p . "', nbr_baie_75='" . $nbr_baie_75p . "', nbr_baie_50='" . $nbr_baie_50p . "', nbr_baie_25='" . $nbr_baie_25p . "', nbr_baie_0='" . $nbr_baie_0p . "', taux_moyen_remplissage='" . $taux_moyen_remplissage . "', nbr_total_baie='" . $nbr_baie . "', nbr_baies_installable='" . $nbr_baie_possible . "', room_usable_power='" . $pw_salle . "', room_used_power='" . $pw_used_salle . "', room_baie_mean_power='" . $mean_pw_baie . "', room_comment='" . $comment . "' WHERE room_id='" . $id . "'";
												/*******************/

												$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
												$sth->execute(array(':surface_salle' => $surface_salle, ':unusable_surface_salle' => $unusable_surface_salle, ':used_area' => $used_area,':nbr_baies_possible' => $nbr_baie_total, ':nbr_baie_100p' => $nbr_baie_100p, ':nbr_baie_75p' => $nbr_baie_75p, ':nbr_baie_50p' => $nbr_baie_50p, ':nbr_baie_25p' => $nbr_baie_25p, ':nbr_baie_0p' => $nbr_baie_0p, ':taux_moyen_remplissage' => $taux_moyen_remplissage, ':nbr_baie' => $nbr_baie, ':nbr_baies_installable' => $nbr_baie_possible, ':pw_salle' => $pw_salle, ':pw_used_salle' => $pw_used_salle, ':room_baie_mean_power' => $mean_pw_baie, ':room_comment' => $comment, ':id' => $id));
												require('user_log_room.php'); // On écrit dans les logs user la requête effectuée

												$query_4 = "SELECT baie_id FROM baies";
												$sth_4 = $db->prepare($query_4);
												$sth_4->execute();
												$datas_4 = $sth_4->fetchAll();

												$i = 0;

												foreach($datas_4 as $data_4) {

													$query_2 = "SELECT * FROM salle_possede WHERE room_id=:room_id AND baie_id=:baie_id";
													$sth_2 = $db->prepare($query_2, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
													$sth_2->execute(array(':room_id' => $id, ':baie_id' => $data['baie_id']));
													$datas_2 = $sth_2->fetchAll();

													if(count($datas_2) > 0) {

														$query_3 = "UPDATE salle_possede SET nbr_baie=:nbr_baie WHERE room_id=:room_id AND baie_id=:baie_id";
														$query_3_log = "UPDATE salle_possede SET nbr_baie='" . $baies[$i] . "' WHERE room_id='" . $id . "' AND baie_id='" . $data_4['baie_id'] . "'"; // requête pour les logs

														$sth_3 = $db->prepare($query_3, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
														$sth_3->execute(array(':nbr_baie' => $baies[$i], ':room_id' => $id, ':baie_id' => $data_4['baie_id']));
														$i++;

														require('user_log_salle_possede.php'); // On écrit dans les logs user la requête effectuée

													}
													else {

														$query_3 = "INSERT INTO salle_possede (room_id, baie_id, nbr_baie) VALUES (:room_id, :baie_id, :nbr_baie)";
														$query_3_log = "INSERT INTO salle_possede (room_id, baie_id, nbr_baie) VALUES ('" . $id . "', '" . $data_4['baie_id'] . "', '" . $baies[$i] . "'"; // requête pour les logs

														$sth_3 = $db->prepare($query_3, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
														$sth_3->execute(array(':room_id' => $id, ':baie_id' => $data_4['baie_id'], 'nbr_baie' => $baies[$i]));
														$i++;

														require('user_log_salle_possede.php'); // On écrit dans les logs user la requête effectuée
		
													}
												}											
											}
											else
												echo "<font color=\"FE2E2E\">Après calcul, la surface occupé par le total de baie que vous avez entré dépasse la surface totale disponible dans la salle</font>";
										}

										//header("Location: manage_rooms.php");
									}
									else
										echo "<font color=\"FE2E2E\">Plusieurs (ou aucune) baies sont utilisés comme modèle de base pour de future installation, cela peut fausser les calculs (contactez l'administrateur de l'application)</font>";
								}
							}
							catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
								require('error_log.php');
							}
						}
						else
							echo "<font color=\"FE2E2E\">L'addition du nombre de baie de chaque type et l'addition du nombre de baie par status de remplissage des baies est différent</font>";
					}
					else
						echo "<font color=\"FE2E2E\">Vous consommez plus d'énergie que l'energie totale disponible</font>";
				}
				else
					echo "<font color=\"FE2E2E\">Vous avez plus de surface inutilisable que la surface totale de la salle</font>";
			}
			else
				echo "<font color=\"FE2E2E\">Vous n'avez pas les droits de modifier les salles d'une autre entité</font>";

		}
		else if(isset($_GET['id'])) { // On veut modifier la salle d'id correspondante

			$id = $_GET['id'];

			$short_name = $user->returnShortName(); // récupération du nom court de l'entité de l'user pour vérification

			$room = new Room($id, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
			$room->seekData($db, $short_name); // On va chercher les données dans le formulaire après avoir vérifié que l'utilisateur a bien la légitimité de modifier cette salle
		}
		else { // Affichage des salles modifiables par l'utilisateur de cette entité

			$short_name = $user->returnShortName(); // récupération du nom court de l'entité de l'user pour vérification

			try {
				if($db != null) {

					// On récupère les informations relatives aux salles appartenant à l'entité de l'utilisateur

					$query = "SELECT * FROM rooms LEFT JOIN entity ON rooms.room_entity_id = entity.id LEFT JOIN sites ON rooms.room_entity_site_id = sites.site_id  WHERE short_name=:short_name ORDER BY short_name ASC, site_short_name ASC";
					$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':short_name' => $short_name));
					$datas = $sth->fetchAll();

					if(count($datas) > 0) {

						// Début de l'affichage du tableau

						echo "<div class=\"table\"><table border=\"1\"><tr><th> Entité </th><th> Site </th><th> Salle </th></tr>";

						foreach($datas as $data) {

							$rooms_array = new RoomArray(); // On créer un tableau d'objet room
							$rooms_array->setRoomData($data['room_id'], $data['room_name'], $data['site_short_name'], $data['short_name'], null, null, null, null, null, null, null, null, null, null, null); // On assigne les données aux attributs des objets
						}

						$rooms_array->showManageData();

						echo "</table></div><br />";

						// Fin de l'affichage du tableau
					}
				}
			}
			catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
				require('error_log.php');
			}
		}

		echo "</div>
		</div>";
		require('footer.php');
		echo"</body></html>";
	}
	else { // On refuse le droit d'accès à la gestion des salles à tout ceux qui ne sont pas employé d'une entité de gestion
		echo"<html><head><title>Erreur</title><link rel=\"stylesheet\" href=\"css/style_2.css\" /><meta charset=\"UTF-8\"></head><body>";
		require('top.php');
		echo"<p><font color=\"FE2E2E\">vous n'avez pas l'autorisation d'accéder à cette page</font></p><br />-><a href=\"index.php\"> retour</a>";
		require('footer.php');
		echo"</body></html>";
	}
?>
