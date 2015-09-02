<?php require('session.php'); // Inclusion de la session utilisateur, et de la connection à la BDD ?>
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

		if(isset($_POST['complete_name']) && isset($_POST['short_name'])) {

			$complete_name = htmlentities($_POST['complete_name']); // traitement contre les injections de javascript/HTML
			$short_name = htmlentities($_POST['short_name']);

			$entity = new Entity(0, $complete_name, $short_name); // création de l'objet entité
			$entity->newEntity($db); // méthode de création d'une nouvelle entité
		}

		/* Affichage du formulaire de création */

		echo"<div id=\"body_content\">
			<div class=\"body\">
				<p> Création d'une nouvelle entité </p>
				<form action=\"\" method=\"POST\">
				<label>Nom de l'entité : </label><input name=\"complete_name\" type=\"text\" class=\"complete_name\" placeholder=\"Nom Complet\" required=\"required\" title=\"Nom complet de l'entité\"><br />
				<label>Nom court de l'entité : </label><input name=\"short_name\" type=\"text\" class=\"short_name\" placeholder=\"Nom Court\" required=\"required\" title=\"Nom court de l'entité\"><br />
				<input type=\"submit\" value=\"Créer\">
			</form>

				-><a href=\"entity.php\"> retour</a>
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
