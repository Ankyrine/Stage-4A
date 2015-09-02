<?php require('session.php'); ?>
<?php require('class.rooms.php'); ?>
<?php require('class.sites.php'); ?>
<html>
	<head>
		<title> Données brutes des sites </title>
		<link rel="stylesheet" href="css/style_2.css" />
		<meta charset="UTF-8">
	</head>
	<body>
		<?php require ('top.php'); ?>

		<?php
			if($user->userAuth() == 1) { // Si l'utilisateur connecté est un employé d'une entité de gestion

				try {
					echo "<p> Données brutes </p><br />";

					if($db != null) {

						// Récupération des informations relatives à l'entité en question

						$query = "SELECT site_id, site_short_name FROM sites LEFT JOIN entity ON sites.site_entity_id=entity.id WHERE short_name=:short_name";
						$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
						$sth->execute(array(':short_name' => $user->returnShortName()));
						$datas = $sth->fetchAll();

						if($nbr_row = count($datas) > 0) { // Si il y a un résultat à cette requête

							echo "<form action=\"\" method=\"POST\"><select name=\"site\">"; // Début du formulaire de selection du site

							foreach($datas as $data) { 

								echo "<option>" . $data['site_short_name'] . "</option>"; // Affichage des sites disponible				
							}

							echo "</select>\t<input type=\"submit\" value=\"Consulter\"></form>"; // Fin formulaire

							if(isset($_POST['site'])) { // Si le formulaire a été envoyé et qu'il existe la variable site transmise par la méthode POST

								$site = $_POST['site']; // récupération de la donnée

								// On récupère les données relatives à ce site

								$query = "SELECT * FROM rooms LEFT JOIN entity ON rooms.room_entity_id = entity.id LEFT JOIN sites ON rooms.room_entity_site_id = sites.site_id WHERE site_short_name=:site";
								$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
								$sth->execute(array(':site' => $site));
								$datas = $sth->fetchAll();

								$verif_entity = 0; /* Variable binaire de vérification que l'id du site transmise
								appartient bien à l'entité de l'utilisateur courant, celà évite l'utilisation d'un
								formulaire distant pointant sur une id de site d'une autre entité, les données des autres
								entité sont donc protégées */

								$nbr_row = count($datas); // récupération du nombre de salle appartenant à ce site

								if(count($datas) > 0) {

									foreach($datas as $data) {

										if($data['short_name'] == $user->returnShortName()) { /* Si l'entité du site demandé 
										correspond à l'entité de l'utilisateur */

											// Création du tableau d'objet rooms

											if($data['room_total_area'] == null) {

												$rooms = new RoomArray();
												$rooms->setRoomData($data['room_id'], $data['room_name'], $data['site_short_name'], $data['short_name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, $data['room_comment']);
											}
											else {

												$rooms = new RoomArray();
												$rooms->setRoomData($data['room_id'], $data['room_name'], $data['site_short_name'], $data['short_name'], $data['room_total_area'], $data['room_unusable_area'], $data['room_baie_area'], $data['room_usable_power'], $data['room_used_power'], $data['nbr_total_baie'], $data['taux_moyen_remplissage'], $data['nbr_baies_possible'], $data['room_baie_mean_power'], $data['nbr_baies_installable'], $data['room_comment']);
											}								
										}
										else { // Sinon le site demandé appartient à une autre entité

											// Affichage du message d'erreur correspondant

											echo "Vous n'avez pas accès aux données brutes des autres entitées <br />";
											$verif_entity = 1;
											break;
										}

									}
									
									if($verif_entity == 0) // Si la requête a été effectué (et est donc légitime)
										$rooms->showRawData($db, $nbr_row); // On affiche les données brutes du site demandé
								}
								else // S'il n'y a aucun résultat
									echo "Aucune salle pour ce site"; // Il n'y a aucune salle pour ce site
							}
						}
						else // Sinon l'entité transmise en formulaire n'existe pas
							echo "Aucun site trouvé pour cette entité";
					}
				}
				catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
					require('error_log.php');
				}
			}
			else if($user->userAuth() == 2) { // Si l'utilisateur actuel est administrateur

				try {
					echo "<p> Données brutes </p><br />";

					if($db != null) {

						// On récupère la liste des sites de toutes les entités

						$query = "SELECT site_id, site_short_name, short_name FROM sites LEFT JOIN entity ON sites.site_entity_id=entity.id";
						$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
						$sth->execute(array(':short_name' => $user->returnShortName()));
						$datas = $sth->fetchAll();

						if($nbr_row = count($datas) > 0) { 

							echo "<form action=\"\" method=\"POST\"><select name=\"site\">"; // début formulaire

							foreach($datas as $data) {

								echo "<option>" . $data['short_name'] . " - " . $data['site_short_name'] . "</option>"; // affichage du couple "entité - site"				
							}

							echo "</select>\t<input type=\"submit\" value=\"Consulter\"></form>"; // fin formulaire

							if(isset($_POST['site'])) { // Si la variable site a été transmise par le formulaire via la méthode GET

								$array = explode(" - ", $_POST['site']); // on récupère le nom du site à traiter
								$site = $array[1];

								// récupération des données relatives au site demandé

								$query = "SELECT * FROM rooms LEFT JOIN entity ON rooms.room_entity_id = entity.id LEFT JOIN sites ON rooms.room_entity_site_id = sites.site_id WHERE sites.site_short_name=:site_short_name";
								$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
								$sth->execute(array(':site_short_name' => $site));
								$datas = $sth->fetchAll();

								$nbr_row = count($datas);

								if(count($datas) > 0) {

									foreach($datas as $data) {

										// Création du tableau d'objet rooms

										$rooms = new RoomArray();
										$rooms->setRoomData($data['room_id'], $data['room_name'], $data['site_short_name'], $data['short_name'], $data['room_total_area'], $data['room_unusable_area'], $data['room_baie_area'], $data['room_usable_power'], $data['room_used_power'], $data['nbr_total_baie'], $data['taux_moyen_remplissage'], $data['nbr_baies_possible'], $data['room_baie_mean_power'], $data['nbr_baies_installable'], $data['room_comment']);
									}
									
									$rooms->showRawData($db, $nbr_row); // Affichage des données brutes du site selectionné
								}
								else
									echo "Aucune salle pour ce site";
							}

						}
					}
				}
				catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
					require('error_log.php');
				}
			}
			else
				echo "Vous n'avez pas accès aux données brutes des sites";

		?>

		<div id="body_content">
			<div class="body">
				-><a href="index.php"> retour</a>
			</div>
		</div>
		<?php require('footer.php'); ?>
	</body>
</html>
