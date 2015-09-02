<?php
	require('class.user.php');
	require('mysql/mysql_connect.php');

	/* Ce script permet la récupération des informations du cookies de l'utilisateur pour l'authentification à l'application */

	if(isset($_COOKIE['login_info'])) { // Si il existe un cookies de connection

		$array = explode("/", $_COOKIE['login_info']); // On explose les données du cookies

		// Récupération des données respectives

		$user_login = $array[0];
		$user_password = $array[1];

		// On interroge la BDD sur le login du visiteur
		try {
			if($db != null) {

				$query = "SELECT * FROM entity_user LEFT JOIN entity ON entity_user.entity_user_id = entity.id WHERE SHA1(`entity_user_email`)=:user_login";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':user_login' => $user_login));

				$datas = $sth->fetchAll();

				if(count($datas) > 0) { // Si il y a un résultat

					foreach($datas as $data) {

						if(sha1($data['entity_user_password']) == $user_password) { // On vérifie la correspondante des mot de passe

							// S'il y a correspondance

							$user = new User; // Création de l'objet "Utilisateur" de l'application

							$user->setUserData($data['entity_user_email'], $data['entity_user_firstname'], $data['entity_user_lastname'], $data['entity_user_auth'], $data['entity_user_password'], $data['short_name'], 1); // On définie les données d'utilisateur en fonction du résultat de la requête SQL
						}
						else { // Sinon mot de passe incorrect
							$user = new User;
							echo "<font color=\"FE2E2E\">Les données du cookies ne correspondent pas aux données de la base de donnée. Tentez de vous reconnecter</font>";
						}
					}
				}
				else { // Si le login n'existe pas en BDD
					$user = new User; // On défini l'utilisateur comme étant un simple visiteur
				}
			}
			else { // Si la connexion à la BDD a échouée
				$user = new User; // On défini l'utilisateur comme étant un simple visiteur
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}
	else { // Si le visiteur ne possède pas de cookies de connection
		$user = new User; // On défini l'utilisateur comme étant un simple visiteur
	}
?>
