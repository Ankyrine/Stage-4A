<!DOCTYPE html>
<?php require('session.php'); ?>
<html>
	<head>
		<title> Accueil de l'application </title>
		<link rel="stylesheet" href="css/style_2.css" />
		<meta charset="UTF-8">
	</head>
	<body>
		<?php require('connected.php'); // On vérifie si l'utilisateur est connecté
 
		require('top.php'); // On affiche la possibilité de l'utilisateur (Administration: gestion des références, consultation des données; Hebergeur: Gestion des salles, consultation des données; Visiteur: consultation des données

		if(!$user->isConnected()) // Si l'utilisateur n'est pas connecté
			require('login.php'); // Affichage du formulaire de connection

		require('footer.php'); ?>
	</body>
</html>
