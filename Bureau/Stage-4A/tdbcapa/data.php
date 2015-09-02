<?php require('session.php'); // Inclusion de la session utilisateur, et de la connection à la BDD ?>
<?php require('class.rooms.php'); ?>
<html>
	<head>
		<title> Données consolidées des sites </title>
		<link rel="stylesheet" href="css/style_2.css" />
		<meta charset="UTF-8">
	</head>
	<body>
		<?php require ('top.php'); ?>

		<p> Récapitulation des données </p>

		<?php
			/* Pas de restriction d'accès sur les données consolidées donc pas de conditions d'autorisation pour accéder à l'affichage */

			try {
				if($db != null) {

					$query = "SELECT DISTINCT archives_room_date FROM archives_rooms ORDER BY archives_room_date ASC"; // On récupère la liste des archives (mois-année)
					$sth = $db->prepare($query);
					$sth->execute();
					$datas = $sth->fetchAll();

					if(count($datas) > 0) {

						$dates = array();

						foreach($datas as $data) {

							$array = explode(" ", $data['archives_room_date']); // On sépare la chaine de caractere "entité - site" du formulaire
							$dates[] = $array[0]; // On récupère uniquement la date du TIMESTAMP
						}

						$previous_date = null; // vérification de la date précédente, pour éviter de mettre deux fois la même date dans le bandeau déroulant si par exemple un archivages s'est effectué sur plusieurs seconde (donc plusieur resultat à cette date dans la BDD)

						echo "<form action=\"\" method=\"POST\"><select name=\"date_archive\">"; // Début du formulaire de selection de la date de l'archives à consulter

						foreach($dates as $id => $date_archive) {

							if($date_archive != $previous_date) {

								// Affichage des dates dans le bandeau déroulant

								echo "<option "; 
								if(isset($_POST['date_archive']) && $date_archive == $_POST['date_archive']) 
									echo "selected"; // Si la date donné en POST, on selectionne cette option par défaut
								echo">" . $date_archive . "</option>";
								$previous_date = $date_archive;
							}
						}

						echo "<option ";
						if(!isset($_POST['date_archive']) || $_POST['date_archive'] == "Donnée actuelle")
							echo "selected"; // Si la date donné en POST, on selectionne cette option par défaut
						echo "> Donnée actuelle </option>"; // Option pour consulter les données actuelles
						echo "</select>\t<input type=\"submit\" value=\"Consulter\"></form>"; // Fin formulaire
					}

					if(isset($_POST['date_archive'])) {

						$consult_date = $_POST['date_archive']; // On récupère la date de l'archive a consulter ou les données actuelles

						// Récupération de la surface au sol de la baie par défaut pour les futures installations

						if($consult_date == "Donnée actuelle") {

							$query = "SELECT ground_area FROM baies WHERE base_model=1";
							$sth = $db->prepare($query);
							$sth->execute();
							$datas = $sth->fetchAll();

							if(count($datas) == 1) { // Si la requête retourne un unique résultat

								// On récupère toutes les informations sur les salles existantes

								$query = "SELECT * FROM rooms LEFT JOIN entity ON rooms.room_entity_id = entity.id LEFT JOIN sites ON rooms.room_entity_site_id = sites.site_id";
								$sth = $db->prepare($query);
								$sth->execute();
								$datas = $sth->fetchAll();

								if(count($datas) > 0) {

									// début de l'affichage du tableau de consolidation des données

									echo "<div class=\"table\"><table border=\"1\"><tr><th> Entité </th><th> Site </th><th> Salle </th><th> Surface totale </th><th> Surface utile </th><th> Surface disponible </th><th> Nombre de baies possible </th><th> Puissance électrique encore disponible </th><th> Consommation électrique moyenne par baie </th><th> Nombre de baie pouvant être installées </th><th> Taux de remplissage moyen des baies </th><th> Commentaire sur la salle </th></tr>";

									foreach($datas as $data) {

										$rooms = new RoomArray(); // création du tableau d'objet room
										$rooms->setRoomData($data['room_id'], $data['room_name'], $data['site_short_name'], $data['short_name'], $data['room_total_area'], $data['room_unusable_area'], $data['room_baie_area'], $data['room_usable_power'], $data['room_used_power'], $data['nbr_total_baie'], $data['taux_moyen_remplissage'], $data['nbr_baies_possible'], $data['room_baie_mean_power'], $data['nbr_baies_installable'], $data['room_comment']); // On affecte les données aux attributs de chaque objet
									}

									$rooms->showRecapData($db); // On affiche les données des objets, un objet étant une ligne du tableau

									echo "</table></div>";
								}
							}
							else // Sinon si la requête retourne plusieurs (ou aucun) résultat on affiche un message d'erreur car on ne sait pas quelle valeur utiliser
							echo "Plusieurs (ou aucune) baies sont utilisés comme modèle de base pour de future installation, cela peut fausser les calculs (contactez l'administrateur de l'application)";
						}
						else {

							$query = "SELECT * FROM archives_rooms WHERE archives_room_date LIKE :consult_date";
							$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
							$sth->execute(array(':consult_date' => ($consult_date . "%")));
							$datas = $sth->fetchAll();

							echo "<div class=\"table\"><table border=\"1\"><tr><th> Entité </th><th> Site </th><th> Salle </th><th> Surface totale </th><th> Surface utile </th><th> Surface disponible </th><th> Nombre de baies possible </th><th> Puissance électrique encore disponible </th><th> Consommation électrique moyenne par baie </th><th> Nombre de baie pouvant être installées </th><th> Taux de remplissage moyen des baies </th><th> Commentaire sur la salle </th></tr>";

							foreach($datas as $data) {

								$rooms = new RoomArray(); // création du tableau d'objet room
								$rooms->setRoomData($data['archives_room_id'], $data['archives_room_room_name'], $data['archives_room_site_name'], $data['archives_room_entity_name'], $data['archives_room_surface_totale'], $data['archives_room_surface_unusable'], $data['archives_room_surface_baie'], $data['archives_room_puissance_totale'], $data['archives_room_puissance_utilise'], null, $data['archives_room_taux_moyen_remplissage'], $data['archives_room_nbr_baies_possible'], $data['archives_room_puissance_moyenne_baie'], $data['archives_room_nbr_baie_installable'], $data['archives_room_comment']); // On affecte les données aux attributs de chaque objet
							}

							$rooms->showRecapData($db); // On affiche les données des objets, un objet étant une ligne du tableau
						
							echo "</table></div>";						
						}
					}
				}
			}
			catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
				require('error_log.php');
			}

		?>

		<div id="body_content">
			<div class="body">
				-><a href="index.php"> retour</a>
			</div>
		</div>
		<?php require('footer.php'); ?>
	</body>
</html>
