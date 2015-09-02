<?php require('session.php'); // Inclusion de la session utilisateur, et de la connection à la BDD ?>
<?php require('class.sites.php'); ?>
<?php require('class.entity.php'); ?>
<?php 
	if($user->userAuth() == 2) { // Si l'utilisateur est un administrateur
 
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

		/* Gestion des données du formulaire */

		if(isset($_POST['complete_name']) && isset($_POST['short_name']) && isset($_GET['modif_id']) && isset($_POST['entity'])) {

			$entity = $_POST['entity'];

			$entity = new Entity(0, null, $entity);
			$entity_id = $entity->retrieveEntityID($db); // On récupère l'id de l'entité en BDD

			$complete_name = htmlentities($_POST['complete_name']); // traitement contre les injections de javascript/HTML
			$short_name = htmlentities($_POST['short_name']);
			$id = $_GET['modif_id'];

			$site = new Site($id, $complete_name, $short_name, $entity_id); // Création de l'objet site
			$site->modifSite($db); // On lance la méthode de modification sur l'objet site en passant la BDD en paramètre
		}

		/* Gestion du lien pour action sur l'employé */
		
		else if (isset($_GET['id']) && isset($_GET['action'])) {

			$id_site = $_GET['id']; 
			$action_site = $_GET['action'];

			if($action_site == "supprimer") { // si le lien est sites.php?action=supprimer

				$site = new Site($id_site, null, null, null); // création de l'objet site avec l'id du site en BDD
				$site->dropSite($db); // méthode de suppression d'un site en BDD
			}
			else if($action_site == "modifier") { // si le lien est sites.php?action=modifier

				try {
					if($db != null) {

						// Récupération des données actuelles du site donné

						$query = "SELECT * FROM sites LEFT JOIN entity ON sites.site_entity_id = entity.id WHERE site_id=:id";
						$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
						$sth->execute(array(':id' => $id_site));

						$datas = $sth->fetchAll();

						if(count($datas) > 0) {

							foreach($datas as $data) {

								$site = new Site($data['site_id'], $data['site_name'], $data['site_short_name'], $data['short_name']); // Création de l'objet site à modifier
							}

							$site->showModif($db); // affichage du formulaire de modification du site avec les valeurs actuelles en défaut
						}
						else
							echo "<font color=\"FE2E2E\">Site introuvable</font>";
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

					$query = "SELECT * FROM sites LEFT JOIN entity ON sites.site_entity_id = entity.id ORDER BY short_name ASC, site_short_name ASC";

					$sth = $db->prepare($query);
					$sth->execute();

					$datas = $sth->fetchAll();

					if(count($datas) > 0) {

						/* début d'affichage du tableau */

						echo "<div class=\"table\"><table border=\"1\"><tr><th> Entité </th><th> Nom du site </th><th> Nom court du site</th></tr>";

						foreach($datas as $data) {

							$sites_array = new SiteArray; // création du tableau d'objet site
							$sites_array->setSiteData($data['site_id'], $data['site_name'], $data['site_short_name'], $data['short_name']); // insertion des données de la BDD en attribut de l'objet
						}

						$sites_array->showData(); // On affiche le contenu du tableau d'objet sites sous forme de ligne du tableau

						echo "</table></div><a href=\"new_sites.php\">Créer un nouveau site pour l'entité</a>";

						/* Fin d'affichage du tableau */
					} 
					else {
						echo "<font color=\"FE2E2E\">Aucun site trouvé</font><br /><a href=\"new_sites.php\">Créer un nouveau site pour l'entité</a>";
					}
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
