<?php require('session.php'); // Inclusion de la session utilisateur, et de la connection à la BDD ?>
<?php require('class.employee.php'); ?>
<?php require('class.entity.php'); ?>
<?php 
	if($user->userAuth() == 2) { // Si l'utilisateur est un administrateur
 
		echo"<html>
			<head>
				<title> Gestion des entités - Nouvelle entité </title>
				<link rel=\"stylesheet\" href=\"css/style_2.css\" />
				<meta charset=\"UTF-8\">
			</head>
			<body>";
		require('top.php');

		/* Gestion des données du formulaire */

		if(isset($_POST['entity']) && isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email']) && isset($_POST['password'])) {

			$entity_shortName = $_POST['entity'];

			$entity = new Entity(0, null, $entity_shortName);
			$entity_id = $entity->retrieveEntityID($db); // On récupère l'id de l'entité en BDD

			$firstname = htmlentities($_POST['firstname']); // traitement contre les injections de javascript/HTML
			$lastname = htmlentities($_POST['lastname']);
			$email = htmlentities($_POST['email']);
			$password = sha1('!§' . $_POST['password']);

			$employee = new Employee($entity_id, $email, $firstname, $lastname, $password, $entity_id); // création de l'objet employé
			$employee->newEmployee($db); // méthode de création d'un nouveau employé
		}
		else // Affichage du formulaire de création
			echo "Vous devez remplir tout les champs";
		echo"<div id=\"body_content\">
			<div class=\"body\">
				<p> Création d'un nouvel utilisateur </p>";

				try {
					echo "<form action=\"\" method=\"POST\">";

					if($db != null) {

						/* On récupère la liste des entités existante pour associer l'utilisateur à une entité */

						$query = "SELECT short_name FROM entity";
						$sth = $db->prepare($query);
						$sth->execute();

						$datas = $sth->fetchAll();

						if(count($datas) > 0) {

							// Affichage du bandeau déroulant de selection de l'entité de rattachement

							echo "Entité de rattachement de l'utilisateur : <select name=\"entity\" class=\"entity\">";

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

						echo "<label>Prénom : </label><input name=\"firstname\" type=\"text\" class=\"firstname\" placeholder=\"Prénom\" required=\"required\" title=\"Prénom de l'utilisateur\"><br />
						<label>Nom : </label><input name=\"lastname\" type=\"text\" class=\"lastname\" placeholder=\"Nom de Famille\" required=\"required\" title=\"Nom de famille de l'utilisateur\"><br />
						<label>e-mail : </label><input name=\"email\" type=\"text\" class=\"email\" placeholder=\"e-mail\" required=\"required\" title=\"e-mail de service de l'utilisateur\"><br />
						<label>Mot de passe : </label><input name=\"password\" type=\"password\" placeholder=\"Password\" required=\"required\" pattern=\".{7,}\" title=\"Minimum 7 caractères\" autocomplete=\"off\"><br />
						<input type=\"submit\" value=\"Créer\">
					</form>";
					}
				}
				catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
					require('error_log.php');
				}
				echo "-><a href=\"user.php\"> retour</a>
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
