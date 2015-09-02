<?php require('session.php'); // Inclusion de la session utilisateur, et de la connection à la BDD ?>
<?php require('class.sites.php'); ?>
<?php require('class.entity.php'); ?>
<?php 
	if($user->userAuth() == 2) { // Si l'utilisateur est un administrateur
 
		echo"<html>
			<head>
				<title> Gestion des sites - Nouveau site </title>
				<link rel=\"stylesheet\" href=\"css/style_2.css\" />
				<meta charset=\"UTF-8\">
			</head>
			<body>";
		require('top.php');

		/* Gestion des données du formulaire */

		if(isset($_POST['complete_name']) && isset($_POST['short_name']) && isset($_POST['entity'])) {

			$entity = new Entity(0, null, $_POST['entity']);
			$entity_id = $entity->retrieveEntityID($db); // On récupère l'id de l'entité en BDD

			$complete_name = htmlentities($_POST['complete_name']); // traitement contre les injections de javascript/HTML
			$short_name = htmlentities($_POST['short_name']);

			$site = new Site(0, $complete_name, $short_name, $entity_id); // création de l'objet site
			$site->newSite($db); // méthode de création d'un nouveau site
		}
		else // Affichage du formulaire de création
			echo "<font color=\"FE2E2E\">Vous devez remplir tout les champs</font>";
		echo"<div id=\"body_content\">
			<div class=\"body\">
				<p> Création d'un nouveau site </p>";

				try {

				echo "<form action=\"\" method=\"POST\">";

					if($db != null) {

						/* On récupère la liste des entités existante pour associer le site à une entité */

						$query = "SELECT short_name FROM entity";
						$sth = $db->prepare($query);
						$sth->execute();

						$datas = $sth->fetchAll();

						if(count($datas) > 0) {

							// Affichage du bandeau déroulant de selection de l'entité de rattachement

							echo "Entité de rattachement du site : <select name=\"entity\" class=\"entity\">";

							foreach($datas as $data) {

								if($data['short_name'] == $employee->_entityShortName)
									echo "<option selected=\"selected\">" . $data['short_name'] . "</option>";
								else
									echo "<option>" . $data['short_name'] . "</option>";
							}

							echo "</select><br />";

							// Fin affichage bandeau déroulant
						}

						// Affichage du reste du formulaire de création d'utilisateur

						echo "<label>Nom du site : </label><input name=\"complete_name\" type=\"text\" class=\"complete_name\" placeholder=\"Nom Complet\" required=\"required\" title=\"Nom complet du site\"><br />
							<label>Nom court du site : </label><input name=\"short_name\" type=\"text\" class=\"short_name\" placeholder=\"Nom Court\" required=\"required\" title=\"Nom court du site\"><br />
							<input type=\"submit\" value=\"Créer\">
						</form>";
					}
				}
				catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
					require('error_log.php');
				}
				echo "-><a href=\"sites.php\"> retour</a>
			</div>
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
