<?php

/**********************************************************/
/* Classe de définition d'un utilisateur de l'application */
/**********************************************************/

class User {

	/* Attributs de l'objet User */

	private $_login; // adresse e-mail utilisée pour l'authentification
	private $_firstName;
	private $_lastName;
	private $_auth; // Le degré d'autorisation de l'utilisateur (2 = admin, 1 = employé entité de gestion, 0 = visiteur simple)
	private $_password;
	private $_entityShortName; // l'entité de rattachement de l'utilisateur
	private $_isConnected = 0; // Si l'utilisateur est connecté ou non

	/*** Affectation des valeurs des attributs ***/

	public function setUserData($login, $firstName, $lastName, $auth, $password, $entityShortName, $isConnected) {

		$this->_login = $login;
		$this->_firstName = $firstName;
		$this->_lastName = $lastName;
		$this->_auth = $auth;
		$this->_password = $password;
		$this->_entityShortName = $entityShortName;
		$this->_isConnected = $isConnected;
	}

	/*** Affichage en en-tête de la page HTML des informations sur l'utilisateur authentifié ***/

	public function showData() {

		echo "<p>" . $this->_firstName . " " . $this->_lastName . " - " . $this->_entityShortName . " - <a href=\"logout.php\">Se déconnecter</a>- <a href=\"edit_mdp.php\">Changer de mot de passe</a></p>";
	}

	/*** Permet de savoir si l'utilisateur est bel et bien connecté en fonction de la valeur de l'attribut _isConnected ***/

	public function isConnected() {

		if($this->_isConnected == 0)
			return false;
		else
			return true; 
	}

	/*** Retourne le niveau d'accès de l'utilisateur qui est connecté, 2 = admin, 1 = employé entité de gestion, 0 (par défaut) = visiteur ***/

	public function userAuth() {

		if($this->_auth == 1)
			return 1;
		else if($this->_auth == 2)
			return 2;
	}

	/*** Retourne l'entité de rattachement de l'utilisateur connecté ***/

	public function returnShortName() {

		return $this->_entityShortName;
	}

	/*** Affiche le menu d'action en fonction du niveau d'accès de l'utilisateur ***/

	public function showPossibleAction() {

		if($this->userAuth() == 2) {

			echo "<p><a href=\"entity.php\">Gérer les entités</a> - <a href=\"sites.php\">Gérer les sites</a> - <a href=\"rooms.php\">Gérer les salles</a> - <a href=\"user.php\">Gérer les utilisateurs</a> - <a href=\"baies.php\">Gérer les types de baies</a> - <a href=\"user_logs.php\">Consulter les logs utilisateurs</a> - <a href=\"error_logs.php\">Consulter les logs d'erreurs</a>";
		}
		else if($this->userAuth() == 1) {

			echo "<p><a href=\"manage_rooms.php\">Gérer vos salles</a></p>"; 
		}
	}

	/*** Retourne l'adresse mail de l'utilisateur ***/

	public function retrieveLogin() {

		return $this->_login;
	}

	/*** Retourne le prénom de l'utilisateur ***/

	public function retrieveFirstname() {

		return $this->_firstName;
	}

	/*** Retourne le nom de famille de l'utilisateur ***/

	public function retrieveLastname() {

		return $this->_lastName;
	}
}

?>
