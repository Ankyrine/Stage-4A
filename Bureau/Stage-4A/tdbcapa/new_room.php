<?php require('session.php'); // Inclusion de la session utilisateur, et de la connection à la BDD ?>
<?php require('class.rooms.php'); ?>
<?php 
	if($user->userAuth() == 2) { // Si l'utilisateur est un administrateur
 
		echo"<html>
			<head>
				<title> Gestion des salles - Nouvelle salle </title>
				<link rel=\"stylesheet\" href=\"css/style_2.css\" />
				<meta charset=\"UTF-8\">
			</head>
			<body>";
		require('top.php');

		/* Gestion des données du formulaire */

		if(isset($_POST['entity']) && isset($_POST['name'])) {

			$array = explode(" - ", $_POST['entity']); // On sépare les données qui étaient sous la forme "entité - site" du formulaire
			
			$entity = $array[0];
			$site = $array[1];
			$name = htmlentities($_POST['name']); // traitement contre les injections de javascript/HTML

			$room = new Room(0, $name, $site, $entity, null, null, null, null, null, null, null, null, null, null, null); // création de l'objet room
			$room->newRoom($db); // méthode de création d'une nouvelle salle
		}
		else {
			/* Affichage du formulaire de création */

			try {
				if($db != null) {

					// Récupération des données en base de donnée pour récupérer les couples "entité - site" pour selection de l'entité et le site de rattachement de la salle à créer

					$query = "SELECT * FROM sites INNER JOIN entity ON sites.site_entity_id = entity.id ORDER BY id ASC, site_id ASC";
					$sth = $db->prepare($query);
					$sth->execute();

					$datas = $sth->fetchAll();

					echo "<form action=\"\" method=\"POST\">";

					if(count($datas) > 0) {

						// Affichage du bandeau déroulant de selection de l'entité et le site de rattachement

						echo "Entité et site de rattachement : <select name=\"entity\" class=\"entity\">";

						foreach($datas as $data) {

							echo "<option>" . $data['short_name'] . " - " . $data['site_short_name'] . "</option>";
						}

						echo "</select><br />";

						// Fin affichage bandeau déroulants
						// Affichage du reste du formulaire de création d'utilisateur

						echo "<label>Nom de la salle : </label><input name=\"name\" type=\"text\" class=\"name\" placeholder=\"Nom de la Salle\" required=\"required\" title=\"Nom de la salle\"><br /><input type=\"submit\" value=\"Créer\">
						</form>";
					}

					echo "-><a href=\"rooms.php\"> retour</a>";

				}
			}
			catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
				require('error_log.php');
			}
		}
		
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
