<?php

/********************************************************/
/* Classe de définition d'un Employé dans l'application */
/********************************************************/
/* Fonction disponible :
- newEmployee : création d'un employé en BDD (tout les attributs sont necessaire lors de la création de l'objet au préalable)
- dropEmployee : suppression d'un employé en BDD (uniquement l'attribut "_id" est necessaire pour la réalisation de cette fonction)
- showModif : affichage d'un formulaire de modification d'un employé, avec valeur actuelle par défaut (tout les attributs sont necessaire lors de la création de l'objet au préalable)
- modifEmployee : modification d'un employé en BDD, avec les données du formulaire précédent (tout les attributs sont necessaire lors de la création de l'objet au préalable) */

/******************************/
/* Tableau d'objet "Employee" */
/******************************/
/* Fonction disponible :
- addBaie : ajoute un objet "Employee" au tableau des employés
- setBaieData : ajoute les valeurs des paramètres aux attributs de l'objet
- showData : Affiche le contenu du tableau d'objet "Employee" sous forme de ligne d'un tableau */

class Employee {

	/*** Attributs de l'objet Employee ***/

	public $_id;
	public $_login;
	public $_firstName;
	public $_lastName;
	public $_password;
	public $_entityShortName;

	public function __construct($id, $login, $firstName, $lastName, $password, $entityShortName) {

		$this->_id = $id;
		$this->_login = $login;
		$this->_firstName = $firstName;
		$this->_lastName = $lastName;
		$this->_password = $password;
		$this->_entityShortName = $entityShortName;
	}

	/*** Création d'un nouvel employé en BDD, objet crée et attribut ajouté au préalable ***/

	public function newEmployee($db) {

		try {
			if($db != null) {

				$query = "SELECT * FROM entity_user WHERE entity_user_email=:email"; // On vérifie qu'un employé n'a pas déjà cette adresse e-mail
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':email' => $this->_login));
				$datas = $sth->fetchAll();

				if(count($datas) == 0) { // Si l'e-mail est unique

					if($this->_entityShortName == 1) // On donne les droit d'administrateur (entité d'ID 1 = entité d'administration
						$query = "INSERT INTO entity_user (entity_user_id, entity_user_firstname, entity_user_lastname, entity_user_email, entity_user_password, entity_user_auth) VALUES (:entity_id, :firstname, :lastname, :email, :password, 2)";
					else // Sinon utilisateur de gestion (entity_user_auth = 1)
						$query = "INSERT INTO entity_user (entity_user_id, entity_user_firstname, entity_user_lastname, entity_user_email, entity_user_password, entity_user_auth) VALUES (:entity_id, :firstname, :lastname, :email, :password, 1)";

					$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		
					$sth->execute(array(':entity_id' => $this->_entityShortName, ':firstname' => $this->_firstName, ':lastname' => $this->_lastName, ':email' => $this->_login, ':password' => $this->_password));
					header("Location: user.php");
				}
				else
					echo "<font color=\"FE2E2E\">Un utilisateur existe déjà sous cette adresse e-mail</font>";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}	
	}

	/*** Suppression d'un employé en BDD, objet crée et attribut ajouté au préalable (Seulement l'ID de l'employé est nécessaire) ***/

	public function dropEmployee($db) {

		try {
			if($db != null) {

				$query = "DELETE FROM entity_user WHERE user_id=:id";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':id' => $this->_id));

				header("Location: user.php");
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}
	
	/*** Modification d'un employé en BDD, objet crée et attribut ajouté au préalable ***/

	public function modifEmployee($db) {

		try {
			if($db != null) {

				$query = "SELECT * FROM entity_user WHERE entity_user_email=:email AND user_id!=:id"; // Si un autre utilisateur ne possède pas déjà cette adresse e-mail
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':email' => $this->_login, ':id' => $this->_id));
				$datas = $sth->fetchAll();

				if(count($datas) == 0) {

					/*** Modification de l'employé ***/

					$query = "UPDATE entity_user SET entity_user_id=:entity_id, entity_user_firstname=:firstname, entity_user_lastname=:lastname, entity_user_email=:email, entity_user_password=:password WHERE user_id=:id";
					$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':entity_id' => $this->_entityShortName, ':firstname' => $this->_firstName, ':lastname' => $this->_lastName, ':email' => $this->_login, ':password' => $this->_password, ':id' => $this->_id));

					header("Location: user.php");
				}
				else
					echo "<font color=\"FE2E2E\">Un utilisateur existe déjà avec cette adresse e-mail</font>";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Affichage du formulaire de modification de l'employé, les valeurs actuelles sont chargées en attributs de l'objet au préalable ***/

	public function showModif($db) {

		echo"<div id=\"body_content\">
			<div class=\"body\">
			<p> Modification de l'utilisateur : " . $this->_lastName . " " . $this->_firstName . " </p>";

		try {
			echo "<form action=\"user.php?modif_id=" . $this->_id . "\" method=\"POST\">";

			if($db != null) {

				$query = "SELECT * FROM entity";
				$sth = $db->prepare($query);
				$sth->execute();

				$datas = $sth->fetchAll();

				if(count($datas) > 0) {

					echo "Entité de rattachement de l'utilisateur : <select name=\"entity\" class=\"entity\">";

					foreach($datas as $data) {

						$entity_array = new EntityArray;
						$entity_array->setEntityData($data['id'], null, $data['short_name']);
					}
				
					$entity_array->showModif($db, $this->_entityShortName); // Affichage du bandeau déroulant pour selectionner l'entité de rattachement

					echo "</select><br />";
					echo "<label>Prénom : </label><input name=\"firstname\" type=\"text\" class=\"firstname\" placeholder=\"Prénom\"  value=\"" . $this->_firstName . "\" required=\"required\" title=\"Prénom de l'utilisateur\"><br />
						<label>Nom : </label><input name=\"lastname\" type=\"text\" class=\"lastname\" placeholder=\"Nom de Famille\" value=\"" . $this->_lastName . "\" required=\"required\" title=\"Nom de famille de l'utilisateur\"><br />
						<label>e-mail : </label><input name=\"email\" type=\"text\" class=\"email\" placeholder=\"e-mail\" value=\"" . $this->_login . "\" required=\"required\" title=\"e-mail de service de l'utilisateur\"><br />
						<label>Mot de passe : </label><input name=\"password\" type=\"text\" placeholder=\"Password\" value=\"" . $this->_password . "\" required=\"required\" autocomplete=\"off\"><span class=\"marge\"></span><a href=\"mdp.php\" target=\"_blank\">Hashez le nouveau mot de passe </a><br />
						<input type=\"submit\" value=\"Créer\">
					</form>

					-><a href=\"user.php\"> retour</a>
					</div>
				</div>";
				}
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}
}

class EmployeeArray {

	public static $employee_array = array(); // Création du tableau d'objet

	public static function addEmployee(Employee $employee) { // Ajout d'un employé au tableau des employés

		if(!isset(self::$employee_array[$employee->_id])) {

			self::$employee_array[$employee->_id] = $employee;
		}
	}

	public static function setEmployeeData($id, $login, $firstname, $lastname, $password, $entityShortName) { // On ajoute les données aux attributs de l'objet

		$employee = new Employee($id, $login, $firstname, $lastname, $password, $entityShortName); // création de l'objet et de ses attributs
		self::addEmployee($employee); // et on ajoute l'objet au tableau
	}

	public static function showData() { // Affichage du tableau d'objet

		foreach(self::$employee_array as $id => $employee) { // Pour chaque ligne on affiche chaque attribut correspondant au colonne du tableau

			if($employee->_entityShortName == null) // Gestion du cas où une entité a été supprimée mais pas l'employé de l'entité
				echo "<tr><td>null</td><td>" . $employee->_lastName . "</td><td>" . $employee->_firstName . "</td><td>" . $employee->_login . "</td><td><a href=\"user.php?action=modifier&id=" . $employee->_id . "\">modifier</a></td><td><a href=\"user.php?action=supprimer&id=" . $id . "\">supprimer</a></td></tr>";
			else
				echo "<tr><td>" . $employee->_entityShortName . "</td><td>" . $employee->_lastName . "</td><td>" . $employee->_firstName . "</td><td>" . $employee->_login . "</td><td><a href=\"user.php?action=modifier&id=" . $employee->_id . "\">modifier</a></td><td><a href=\"user.php?action=supprimer&id=" . $id . "\">supprimer</a></td></tr>";
		}
	}

	public static function showDataOneRow($id_employee) {

		foreach(self::$employee_array as $id => $employee) {

			if($employee->_id == $id_employee) {

				echo "<tr><td>" . $employee->_name . "</td><td>" . $employee->_shortName . "</td><td><a href=\"employee.php?action=modifier&id=" . $employee->_id . "\">modifier</a></td><td><a href=\"employee.php?action=supprimer&id=" . $employee->_id . "\">supprimer</a></td></tr>";
			}
		}
	}
}

?>
