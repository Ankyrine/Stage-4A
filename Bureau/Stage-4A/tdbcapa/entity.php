<?php require('session.php'); // Inclusion de la session utilisateur, et de la connection à la BDD ?> 
<?php require('class.entity.php'); ?>
<?php 
	if($user->userAuth() == 2) { // Niveau d'utilisation Administrateur de l'application
 
		echo"<html>
			<head>
				<title> Gestion des entités </title>
				<link rel=\"stylesheet\" href=\"css/style_2.css\" />
				<meta charset=\"UTF-8\">
			</head>
			<body>";
		require('top.php');
		echo"<div id=\"body_content\">
			<div class=\"body\">";

		// Si les données envoyé par le formulaire de modification ont été "set"

		if(isset($_POST['complete_name']) && isset($_POST['short_name']) && isset($_GET['modif_id'])) {

			// Récupération des données grâce à la méthode POST

			$complete_name = htmlentities($_POST['complete_name']); // traitement contre les injections de javascript/HTML
			$short_name = htmlentities($_POST['short_name']);
			$id = $_GET['modif_id'];

			$entity = new Entity($id, $complete_name, $short_name); // création de l'objet entité
			$entity->modifEntity($db); // méthode de modification de l'objet entité en BDD
		}

		// Sinon si on reçoit les données via la méthode GET
	
		else if (isset($_GET['id']) && isset($_GET['action'])) {

			$id_entity = $_GET['id']; // Comprend la ligne de la table "Entity" sur laquelle effectuer l'action
			$action_entity = $_GET['action'];

			if($action_entity == "supprimer") { // Si la page est entity.php?action=supprimer

				$entity = new Entity($id_entity, null, null); // On créer l'objet entité avec l'id de l'entité à supprimer en BDD
				$entity->dropEntity($db); // méthode de suppression de l'entité en BDD
			}
			else if($action_entity == "modifier") { // Si la page est entity.php?action=modifier

				try {
					if($db != null) {

						// Récupération des données relative à l'id de l'entité passé en GET
					
						$query = "SELECT * FROM entity WHERE id=:id";
						$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
						$sth->execute(array(':id' => $id_entity));

						$datas = $sth->fetchAll();

						if(count($datas) > 0) {

							foreach($datas as $data) {
	
								$entity = new Entity($data['id'], $data['name'], $data['short_name']); // création de l'objet entité
								$entity->showModif($db); // affichage du formulaire de modification avec les valeurs actuelles par défaut
							}
						}
						else
							echo "<font color=\"FE2E2E\">Entité introuvable</font>";
					}
				}
				catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
					require('error_log.php');
				}
			}
			else // Sinon la commande passé en GET n'existe pas
				echo "<font color=\"FE2E2E\">Commande inconnue</font>";
		}
		else { // On affiche le tableau d'entité existante

			try {
				// Récupération des entités existante en BDD

				$query = "SELECT * FROM entity ORDER BY short_name ASC";
				$sth = $db->prepare($query);
				$sth->execute();
				$datas = $sth->fetchAll();

				if(count($datas) > 0) {

					// Début de l'affichage du tableau des entité

					echo "<div class=\"table\"><table border=\"1\"><tr><th> Nom Complet </th><th> Nom Court </th></tr>";

					foreach($datas as $data) {

						$entity_array = new EntityArray; // création du tableau d'objet entité
						$entity_array->setEntityData($data['id'], $data['name'], $data['short_name']); // affectation des attributs de l'objet avec les valeurs existantes en BDD
					}

					$entity_array->showData(); // On affiche chaque objet du tableau, chaque objet correspondant à une ligne du tableau

					echo "</table></div><a href=\"new_entity.php\">créer une nouvelle entité</a>";
				} 
				else
					echo "<font color=\"FE2E2E\">Requête échoué</font>";
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
	else { // Sinon on refuse l'accès à cette page si l'utilisateur n'est pas administrateur
		echo"<html><head><title>Erreur</title><link rel=\"stylesheet\" href=\"css/style_2.css\" /><meta charset=\"UTF-8\"></head><body>";
		require('top.php');
		echo"<p><font color=\"FE2E2E\">vous n'avez pas l'autorisation d'accéder à cette page</font></p><br />-><a href=\"index.php\"> retour</a>";
		require('footer.php');
		echo"</body></html>";
	}
?>
