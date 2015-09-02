<?php require('session.php'); // Inclusion de la session utilisateur, et de la connection à la BDD ?>
<?php require('class.baies.php'); ?>
<?php 
	if($user->userAuth() == 2) { // Si l'utilisateur est un administrateur
 
		echo"<html>
			<head>
				<title> Gestion des Baies - Nouvelle Baie </title>
				<link rel=\"stylesheet\" href=\"css/style_2.css\" />
				<meta charset=\"UTF-8\">
			</head>
			<body>";
		require('top.php');

		/* Gestion des données du formulaire */

		if(isset($_POST['type_baie']) && isset($_POST['width']) && isset($_POST['depth']) && isset($_POST['groundArea']) && isset($_POST['default'])) {

			$type_baie = htmlentities($_POST['type_baie']); // traitement contre les injections de javascript/HTML
			$width = $_POST['width'];
			$depth = $_POST['depth'];
			$groundArea = $_POST['groundArea'];
			$base_model = $_POST['default'];

			$baie = new Baie(0, $type_baie, $width, $depth, $groundArea, $base_model); // création de l'objet baie
			$baie->newBaie($db); // méthode de création d'un nouveau site
		}
		else
			echo "<font color=\"FE2E2E\">Vous devez remplir tout les champs</font>";

		/* Affichage du formulaire de création de baie */

		echo"<div id=\"body_content\">
			<div class=\"body\">
				<p> Création d'un nouveau type de baie </p>
				<form action=\"\" method=\"POST\">
				<label>Type de la Baie : </label><input name=\"type_baie\" type=\"text\" class=\"type_baie\" placeholder=\"Type de Baie\" required=\"required\" title=\"Type de Baie\"><br />
				<label>Largeur de la baie : </label><input name=\"width\" type=\"text\" class=\"width\" placeholder=\"Largeur\" pattern=\"[0-9]+[.]{1}[0-9]+\" required=\"required\" title=\"Largeur de la Baie : Utiliser un point et non une virgule, ajouter .0 dans le cas d'un entier\"><br />
				<label>Profondeur de la baie : </label><input name=\"depth\" type=\"text\" class=\"depth\" placeholder=\"Profondeur\" pattern=\"[0-9]+[.]{1}[0-9]+\" required=\"required\" title=\"Profondeur de la Baie : Utiliser un point et non une virgule, ajouter .0 dans le cas d'un entier\"><br />
				<label>Surface au sol de la baie : </label><input name=\"groundArea\" type=\"text\" class=\"groundArea\" placeholder=\"Surface au Sol\" pattern=\"[0-9]+[.]{1}[0-9]+\" required=\"required\" title=\"Surface au sol de la Baie : Utiliser un point et non une virgule, ajouter .0 dans le cas d'un entier\"><br /><br />
				Surface de base retenue pour de nouvelles baies ? :<br />
				<input type=\"radio\" required=\"required\" name=\"default\" value=\"1\">Oui
				<input type=\"radio\" required=\"required\" name=\"default\" value=\"0\">Non<br />
				<input type=\"submit\" value=\"Créer\">
			</form>

				-><a href=\"baies.php\"> retour</a>
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
