<?php require('session.php'); ?>
<?php 
	if($user->userAuth() == 2 || $user->userAuth() == 1) { // Si le profil de l'utilisateur est un employé de l'entité ou un administrateur

		// On lui donne accès au script de modification du mot de passe personnel

		echo"<html>
			<head>
				<title> Changer de mot de passe </title>
				<link rel=\"stylesheet\" href=\"css/style_2.css\" />
				<meta charset=\"UTF-8\">
			</head>
			<body>";
		require('top.php');
		echo"<div id=\"body_content\">
			<div class=\"body\">";

		// Si le formulaire a été envoyé

		if(isset($_POST['actual_password']) && isset($_POST['new_password']) && isset($_POST['new_password_verif'])) {

			// On récupère les données via la méthode POST

			$actual_pwd = $_POST['actual_password'];
			$new_pwd = $_POST['new_password'];
			$verif_pwd = $_POST['new_password_verif'];

			if($new_pwd == $verif_pwd) { // Si le nouveau mot de passe et la confirmation de nouveau mot de passe sont égaux

				$user_login = $user->retrieveLogin(); // On récupère le login de l'utilisateur

				// On récupère l'identifiant de l'utilisateur et son mot de passe en fonction de son login

				try {
					if($db != null) {

						// Récupération des données actuelles pour comparaison

						$query = "SELECT user_id, entity_user_password FROM entity_user WHERE entity_user_email=:login";
						$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
						$sth->execute(array(':login' => $user_login));
						$datas = $sth->fetchAll();

						if(count($datas) > 0) { // Si on a un résultat

							foreach($datas as $data) {

								if(strcmp(sha1($actual_pwd), $data['entity_user_password'])) { // On compare le hash du mot de passe rentré et celui stocké en BDD

									// S'il y a correspondance sur l'ancien mot de passe
			
									$new_pwd = sha1("!§" . $verif_pwd); // préparation du hash du nouveau mot de passe

									// Ecriture du nouveau mot de passe en base de donné

									$query = "UPDATE entity_user SET entity_user_password=:new_pwd WHERE user_id=:id";
									$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
									$sth->execute(array(':new_pwd' => $new_pwd, ':id' => $data['user_id']));

									// Réecriture du cookies pour éviter à l'utilisateur qui change on mot de passe d'avoir à se reconnecter après modification de son mot de passe.

									$login_info = sha1($user_login) . "/" . sha1($new_pwd);
									setcookie("login_info", $login_info, time() + 3600, null, null, false, true);

									header("index.php");
								}
								else
									echo "<font color=\"FE2E2E\">Mot de passe actuel incorrect</font>"; 
							}
						}
						else // Sinon l'utilisateur est introuvable en BDD
							"<font color=\"FE2E2E\">Utilisateur introuvable</font>";
					}
				}
				catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
					require('error_log.php');
				}
			}
			else
				echo "<font color=\"FE2E2E\">Les nouveaux mot de passe ne correspondent pas</font>";

			
		}
		else { // Sinon, affichage du formulaire de changement de mot de passe
			echo "<div id=\"body_content\">
				<div class=\"body\">
				<p> Modification de votre mot de passe </p>
				<form action=\"\" method=\"POST\"><label>Mot de passe actuel : </label><input name=\"actual_password\" type=\"password\" class=\"actual_password\" placeholder=\"Mot de passe actuel\" required=\"required\" title=\"Mot de passe actuel\"><br />
				<label>Nouveau mot de passe : </label><input name=\"new_password\" type=\"password\" class=\"new_password\" placeholder=\"Nouveau mot de passe\" required=\"required\" title=\"Nouveau mot de passe\"><br />
				<label>Vérification : </label><input name=\"new_password_verif\" type=\"password\" class=\"new_password_verif\" placeholder=\"Nouveau mot de passe\" required=\"required\" title=\"Nouveau mot de passe\"><br />
				<input type=\"submit\" value=\"Modifier\">
			</form>

				-><a href=\"index.php\"> retour</a>
			</div>
		</div>";
		}	
		echo"</div>
		</div>";
		require('footer.php');
		echo"</body></html>";
	}
	else { // Si l'utilisateur à le profil "invité" on affiche pas le formulaire ni l'accès au script PHP
		echo"<html><head><title>Erreur</title><link rel=\"stylesheet\" href=\"css/style_2.css\" /><meta charset=\"UTF-8\"></head><body>";
		require('top.php');
		echo"<p><font color=\"FE2E2E\">vous devez être connecté pour changer votre mot de passe</font></p><br />-><a href=\"index.php\"> retour</a>";
		require('footer.php');
		echo"</body></html>";
	}
?>
