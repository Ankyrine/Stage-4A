<?php require('session.php'); // Inclusion de la session utilisateur, et de la connection à la BDD ?>
<?php require('class.rooms.php'); ?>
<?php require('class.entity.php'); ?>
<?php require('class.sites.php'); ?>
<?php 
	if($user->userAuth() == 2) { // Si l'utilisateur est un administrateur

		echo"<html>
			<head>
				<title> Gestion des salles </title>
				<link rel=\"stylesheet\" href=\"css/style_2.css\" />
				<meta charset=\"UTF-8\">
			</head>
			<body>";
		require('top.php');
		echo"<div id=\"body_content\">
			<div class=\"body\">";

		/* Gestion des données du formulaire */

		if(isset($_POST['entity']) && isset($_POST['name']) && isset($_GET['modif_id'])) {

			$array = explode(" - ", $_POST['entity']); // On sépare la chaine de caractere "entité - site" du formulaire

			$entity = $array[0];
			$site = $array[1];
			$name = htmlentities($_POST['name']); // traitement contre les injections de javascript/HTML
			$modif_id = $_GET['modif_id'];

			$entity_obj = new Entity(0, null, $entity);
			$entity_id = $entity_obj->retrieveEntityID($db); // On récupère l'id de l'entité en BDD

			$site_obj = new Site(0, null, $site, null);
			$site_id = $site_obj->retrieveSiteID($db); // On récupère l'id du site en BDD

			$room = new Room($modif_id, $name, $site_id, $entity_id, null, null, null, null, null, null, null, null, null, null, null); // création de l'objet room
			$room->modifRoom($db); // méthode de modification des données de la salle de l'objet room
		}

		/* Gestion du lien pour action sur l'employé */

		else if(isset($_GET['action']) && isset($_GET['id'])) {

			$id_room = $_GET['id'];
			$action_room = $_GET['action'];

			if($action_room == "supprimer") { // si le lien est rooms.php?action=supprimer


				echo "<font color=\"FE2E2E\">Voulez-vous réellement supprimer cette salle et les données associés à la salle ?</font><br />";
				echo "<a href=\"rooms.php?action=supprimer&id=$id_room&confirmer=oui\">Oui</a>\r\n<a href=\"rooms.php\">Annuler</a>";

				if(isset($_GET['confirmer'])) {

					if($_GET['confirmer'] == "oui") {

						$room = new Room($id_room, null, null, null, null, null, null, null, null, null, null, null, null, null, null); // création de l'objet room avec l'id de la salle en BDD
						$room->dropRoom($db); // méthode de suppression d'une salle en BDD
					}
				}
			}
			else if($action_room == "modifier") { // si le lien est rooms.php?action=modifier

				try {
					if($db != null) {

						// Récupération des données actuelles de la salle donné

						$query = "SELECT * FROM rooms LEFT JOIN entity ON rooms.room_entity_id = entity.id LEFT JOIN sites ON rooms.room_entity_site_id = sites.site_id WHERE room_id=:id";
						$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
						$sth->execute(array(':id' => $id_room));

						$datas = $sth->fetchAll();

						if(count($datas) > 0) {

							foreach($datas as $data) {

								$room = new Room($data['room_id'], $data['room_name'], $data['site_short_name'], $data['short_name'], null, null, null, null, null, null, null, null, null, null, null); // Création de l'objet room à modifier
							}

							$room->showModif($db);  // affichage du formulaire de modification du site avec les valeurs actuelles en défaut
						}
						else
							echo "<font color=\"FE2E2E\">Salle introuvable</font>";
					}
				}
				catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
					require('error_log.php');
				}
			}
			else // si le lien passe une commande inconnue
				echo "<font color=\"FE2E2E\">Commande inconnue</font>";
		}
		else { // Sinon affichage de la liste des sites et de leurs informations

			try {
				if($db != null) {

					// Récupération de la liste des sites et des informations correspondantes

					$query = "SELECT * FROM rooms LEFT JOIN entity ON rooms.room_entity_id = entity.id LEFT JOIN sites ON rooms.room_entity_site_id = sites.site_id ORDER BY short_name ASC, site_short_name ASC, room_name ASC";
					$sth = $db->prepare($query);
					$sth->execute();
					$datas = $sth->fetchAll();

					if(count($datas) > 0) {

						/* début d'affichage du tableau */

						echo "<div class=\"table\"><table border=\"1\"><tr><th> Entité </th><th> Site </th><th> Salle </th></tr>";

						foreach($datas as $data) {

							$rooms_array = new RoomArray(); // création du tableau d'objet room
							$rooms_array->setRoomData($data['room_id'], $data['room_name'], $data['site_short_name'], $data['short_name'], null, null, null, null, null, null, null, null, null, null, null); // insertion des données de la BDD en attribut de l'objet
						}

						$rooms_array->showData(); // On affiche le contenu du tableau d'objet room sous forme de ligne du tableau

						echo "</table></div><a href=\"new_room.php\">Créer une nouvelle salle</a>";

						/* Fin d'affichage du tableau */
					}
					else
						echo "<font color=\"FE2E2E\">Aucune salle trouvée</font><br /><a href=\"new_room.php\">Créer une nouvelle salle</a>";
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
	else { // Sinon on refuse l'accès à la page aux non administrateur
		echo"<html><head><title>Erreur</title><link rel=\"stylesheet\" href=\"css/style_2.css\" /><meta charset=\"UTF-8\"></head><body>";
		require('top.php');
		echo"<p><font color=\"FE2E2E\">vous n'avez pas l'autorisation d'accéder à cette page</font></p><br />-><a href=\"index.php\"> retour</a>";
		require('footer.php');
		echo"</body></html>";
	}
?>
