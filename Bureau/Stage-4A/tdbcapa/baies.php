<?php require('session.php'); ?>
<?php require('class.baies.php'); ?>
<?php
	if($user->userAuth() == 2) { // Niveau d'utilisation Administrateur de l'application
 
		// Affichage du header

		echo"<html>
			<head>
				<title> Gestion des Baies </title>
				<link rel=\"stylesheet\" href=\"css/style_2.css\" />
				<meta charset=\"UTF-8\">
			</head>
			<body>";
		require('top.php');
		echo"<div id=\"body_content\">
			<div class=\"body\">";

		// Si les données envoyé par le formulaire de modification ont été "set"

		if(isset($_POST['type_baie']) && isset($_POST['width']) && isset($_POST['depth']) && isset($_POST['groundArea']) && isset($_POST['default']) && isset($_GET['modif_id'])) {
			// Récupération des données grâce à la méthode POST
			
			$type_baie = htmlentities($_POST['type_baie']); // traitement contre les injections de javascript/HTML
			$width = $_POST['width'];
			$depth = $_POST['depth'];
			$groundArea = $_POST['groundArea'];
			$base_model = $_POST['default'];
			$id = $_GET['modif_id']; // Ligne de la table "baie" à modifier

			// Création d'un objet de type Baie

			$baie = new Baie($id, $type_baie, $width, $depth, $groundArea, $base_model); // $base_model étant un binaire, 1 pour l'utilisation de ce modèle de baie pour de future installation, 0 sinon
			$baie->modifBaie($db); // On lance la fonction de modification en BDD
		}	

		// Sinon si on reçoit les données via la méthode GET
	
		else if (isset($_GET['id']) && isset($_GET['action'])) {

			$id_baie = $_GET['id']; // Comprend la ligne de la table "Baie" sur laquelle effectuer l'action
			$action_baie = $_GET['action']; // Comprend l'action à effectuer

			if($action_baie == "supprimer") { // Si l'action est de supprimer

				$baie = new Baie($id_baie, null, null, null, null, null); // On créer un objet avec uniquement l'ID de la Baie.
				$baie->dropBaie($db); // On lance la suppression en base de donnée.
			}
			else if($action_baie == "modifier") { // Si l'action est de modifier

				try {

					if($db != null) {

						$query = "SELECT * FROM baies WHERE baie_id=:id"; // On récupère les informations sur la Baie selectionnée en base de donnée 
						$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); // On prépare la requête
						$sth->execute(array(':id' => $id_baie)); // On injecte l'ID de la Baie dans le requête SQL pour éviter les injections SQL.

						$datas = $sth->fetchAll(); // Récupération des résultats

						if(count($datas) > 0) { // Si il existe un résultat (ID de Baie unique)

							foreach($datas as $data) {

								$baie = new Baie($data['baie_id'], $data['baie_type'], $data['width'], $data['depth'], $data['ground_area'], $data['base_model']); // On créer un objet de type Baie
							}

							$baie->showModif(); // On affiche le formulaire de modification de cette ligne SQL
						}
						else
							echo "<font color=\"FE2E2E\">Baie introuvable</font>"; // Si on obtient aucun résultat il y a une erreur sur l'ID de la baie
						}
				}
				catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
					require('error_log.php');
				}
			}
			else // Sinon on ne reconnait pas la commande, on évite une modification de la variable "action" dans l'url
				echo "<font color=\"FE2E2E\">Commande inconnue</font>";
		}

		// Sinon on affiche le contenu de la table "Baie" avec les options "créer", "modifier" ou "supprimer"

		else {

			try {
				$query = "SELECT base_model FROM baies WHERE base_model='1'"; // Requête pour vérifier qu'un utilise bien qu'un seul type de baie en tant que baie par déaut pour de future installation
				$sth = $db->prepare($query);
				$sth->execute();
				$datas = $sth->fetchAll();

				if(count($datas) > 1 || count($datas) == 0) // Si nous avons 0 ou plus d'un résultat, au niveau du calcul des capacités (en volume) d'acceuil des salles sera compromise.
					echo "<font color=\"FE2E2E\">Attention vous avez plusieurs (ou aucun) type de baie par défaut, cela peut géner les données sur la capacité d'acceuil des salles</font>";
			}
			catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
				require('error_log.php');
			}

			try {

				$query = "SELECT * FROM baies ORDER BY baie_type ASC"; // On récupère le contenu de la table "Baie"
				$sth = $db->prepare($query);
				$sth->execute();
				$datas = $sth->fetchAll();

				if(count($datas) > 0) { // Si il y a des résultats

					echo "<div class=\"table\"><table border=\"1\"><tr><th> Modèle de baie </th><th> Largeur </th><th> Profondeur </th><th> Surface au sol </th><th> Baie par défaut </th></tr>";

					foreach($datas as $data) {

						// Récupération des données et création du tableau d'objet de type "Baie"

						$baie_array = new BaieArray;
						$baie_array->setBaieData($data['baie_id'], $data['baie_type'], $data['width'], $data['depth'], $data['ground_area'], $data['base_model']);
					}

					$baie_array->showData(); // Affichage complet du tableau

					echo "</table></div>";
				} 
				else
					echo "<font color=\"FE2E2E\">Aucune baie présente </font><br />";
			}
			catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
				require('error_log.php');
			}

			echo "<a href=\"new_baie.php\">créer un nouveau type de baie</a>"; // Création d'une nouvelle baie
		}

		// Affichage de la fin de la page HTML

		echo "</div>
		</div>";
		require('footer.php');
		echo"</body></html>";
	}
	else { // Si l'utilisateur n'est pas administrateur
		echo"<html><head><title>Erreur</title><link rel=\"stylesheet\" href=\"css/style_2.css\" /><meta charset=\"UTF-8\"></head><body>";
		require('top.php');
		echo"<p><font color=\"FE2E2E\">vous n'avez pas l'autorisation d'accéder à cette page</font></p><br />-><a href=\"index.php\"> retour</a>"; // Il n'a pas le droit d'accéder à cette page, redirection possible vers l'index du site
		require('footer.php');
		echo"</body></html>";
	}
?>
