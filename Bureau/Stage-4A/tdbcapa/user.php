<?php require('session.php'); // Inclusion de la session utilisateur, et de la connection à la BDD ?>
<?php require('class.employee.php'); ?>
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

		if(isset($_POST['entity']) && isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email']) && isset($_POST['password']) && isset($_GET['modif_id'])) {

			$entity_shortName = $_POST['entity'];

			$entity = new Entity(0, null, $entity_shortName);
			$entity_id = $entity->retrieveEntityID($db); // On récupère l'id de l'entité en BDD

			$firstname = htmlentities($_POST['firstname']); // traitement contre les injections de javascript/HTML
			$lastname = htmlentities($_POST['lastname']);
			$email = htmlentities($_POST['email']);
			$password = $_POST['password'];
			$modif_id = $_GET['modif_id'];

			$employee = new Employee($modif_id, $email, $firstname, $lastname, $password, $entity_id); // Création de l'objet employé
			$employee->modifEmployee($db); // On lance la méthode de modification sur l'objet employé en passant la BDD en paramètre
		}

		/* Gestion du lien pour action sur l'employé */

		else if (isset($_GET['id']) && isset($_GET['action'])) {

			$id_user = $_GET['id'];
			$action_user = $_GET['action'];

			if($action_user == "supprimer") { // si le lien est user.php?action=supprimer

				$employee = new Employee($id_user, null, null, null, null, null); // création de l'objet employé avec l'id de l'employé en BDD
				$employee->dropEmployee($db); // méthode suppression de l'employé en BDD
			}
			else if($action_user == "modifier") { // si le lien est user.php?action=modifier

				try {
					if($db != null) {

						// Récupération des données actuelles de l'utilisateur donné

						$query = "SELECT * FROM entity_user LEFT JOIN entity ON entity_user.entity_user_id = entity.id WHERE user_id=:id";
						$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
						$sth->execute(array(':id' => $id_user));

						$datas = $sth->fetchAll();

						if(count($datas) > 0) {

							foreach($datas as $data) {

								$employee = new Employee($data['user_id'], $data['entity_user_email'], $data['entity_user_firstname'], $data['entity_user_lastname'], $data['entity_user_password'], $data['short_name']); // création de l'objet employé a modifier
							}

							$employee->showModif($db); // affichage du formulaire de modification de l'employé avec les valeurs actuelles en défaut
						}
						else
							echo "<font color=\"FE2E2E\">Employé introuvable</font>";
					}
				}
				catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
					require('error_log.php');
				}			
			}
			else // si le lien passe une commande inconnue
				echo "<font color=\"FE2E2E\">Commande inconnue</font>";
		}
		else { // Sinon affichage de la liste des employés et de leurs informations

			try {
				if($db != null) {

					// Récupération de la liste des utilisateurs et des informations correspondantes

					$query = "SELECT * FROM entity_user LEFT JOIN entity ON entity_user.entity_user_id = entity.id ORDER BY short_name ASC, entity_user_lastname ASC";
					$sth = $db->prepare($query);
					$sth->execute();
					$datas = $sth->fetchAll();

					if(count($datas) > 0) {

						/* début d'affichage du tableau */

						echo "<div class=\"table\"><table border=\"1\"><tr><th>Nom de l'entité</th><th>Nom</th><th>Prénom</th><th>e-mail</th></tr>";

						foreach($datas as $data) {

							$employee_array = new EmployeeArray(); // création du tableau d'objet employé
							$employee_array->setEmployeeData($data['user_id'], $data['entity_user_email'], $data['entity_user_firstname'], $data['entity_user_lastname'], $data['entity_user_password'], $data['short_name']); // insertion des données de la BDD en attribut de l'objet
						}

						$employee_array->showData(); // On affiche le contenu du tableau d'objet employé sous forme de ligne du tableau

						echo "</table></div><a href=\"new_user.php\">créer un nouvel utilisateur</a>";

						/* Fin d'affichage du tableau */
					} 
					else
						echo "<font color=\"FE2E2E\">Aucun utilisateur trouvé</font><br /><a href=\"new_user.php\">créer un nouvel utilisateur</a>";
				}
			}
			catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
				require('error_log.php');
			}
		}
	
		echo"</div>
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
