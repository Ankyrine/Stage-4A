<?php

	if(isset($_POST['login']) && isset($_POST['password'])) { // Si le formulaire de connection a été validé

		require('mysql/mysql_connect.php');

		// récupération des donnés via la méthode POST

		$login = $_POST['login'];
		$password = sha1('!§' . $_POST['password']);

		// On vérifie que l'utilisateur est enregistré en base de donné

		try {
			if($db != null) {

				// Récupération des données pour comparaison au des variables transmises par formulaire pour authentification

				$query = "SELECT * FROM entity_user WHERE entity_user_password=:password AND entity_user_email=:login";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':password' => $password, ':login' => $login));
				$datas = $sth->fetchAll();

				if(count($datas) > 0) { // Si on a un résultat

					foreach($datas as $data) {

						$login_info = sha1($login) . "/" . sha1($password); // On concatène le login et mot de passe (qu'on crypte avec l'algorithme SHA1)
						setcookie("login_info", $login_info, time() + 3600, null, null, false, true); // écriture du cookies (les options protèges l'utilisateur des failles XSS)

						header("Location: index.php");
					}
				} 
				else // Sinon, l'utilisateur n'est pas répertorié
					echo "<font color=\"FE2E2E\">Utilisateur non trouvé, ou mot de passe incorrect</font>";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}
?>
