<?php

/******************************************************/
/* Classe de définition d'une entité de l'application */
/******************************************************/
/* Fonction disponible :
- newEntity : création d'une entité en BDD (tout les attributs sont necessaire lors de la création de l'objet au préalable)
- dropEntity : suppression d'une entité en BDD (uniquement l'attribut "_id" est necessaire pour la réalisation de cette fonction)
- showModif : affichage d'un formulaire de modification d'entité, avec valeur actuelle par défaut (tout les attributs sont necessaire lors de la création de l'objet au préalable)
- modifEntity : modification d'une entité en BDD, avec les données du formulaire précédent (tout les attributs sont necessaire lors de la création de l'objet au préalable)
- retrieveEntityID : retourne l'ID en BDD correspondant au nom court de l'entité (uniquement l'attribut "_shortName" est necessaire pour la réalisation de cette fonction) */

/****************************/
/* Tableau d'objet "Entity" */
/****************************/
/* Fonction disponible :
- addEntity : ajoute un objet "Entity" au tableau d'entité
- setEntityData : ajoute les valeurs des paramètres aux attributs de l'objet
- showData : Affiche le contenu du tableau d'objet "Entity" sous forme de ligne d'un tableau
- showModif : affiche un bandeau déroulant avec la liste des entités (avec l'entité actuelle (d'une salle ou site) en défaut. Permet la selection d'une entité lors du choix de l'entité de rattachement d'une site ou d'une salle */


class Entity {

	/*** Attribut de l'objet entity ***/

	public $_id;
	public $_name;
	public $_shortName;
	public $_nbrBaiesInstalable;
	public $_meanPowerConsumption;

	public function __construct($id, $name, $shortName) {

		$this->_id = $id;
		$this->_name = $name;
		$this->_shortName = $shortName;
	}

	/*** Fonction de création d'entité en BDD, l'objet et ces attributs on été crée au préalable ***/

	public function newEntity($db) {

		try {
			if($db != null) {

				$query = "SELECT id FROM entity WHERE short_name=:short_name"; // On vérifie qu'une entité ne possède pas déjà ce nom court
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':short_name' => $this->_shortName));
			
				if(count($datas) == 0) { // Si c'est le cas

					/*** Ajout de l'entité en BDD ***/

					$query = "INSERT INTO entity (name, short_name) VALUES (:complete_name, :short_name)";
					$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':complete_name' => $this->_name, ':short_name' => $this->_shortName));

					echo "<p> Création réussie </p>";
					header("Location: entity.php");
				}
				else // Sinon affichage de l'erreur
					echo "<font color=\"FE2E2E\">Ce nom d'entité existe déjà</font>";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Fonction de suppression d'entité en BDD, l'objet et ces attributs on été crée au préalable (uniquement l'ID de l'entité) ***/

	public function dropEntity($db) {

		try {
			if($db != null) {

				$query = "DELETE FROM entity WHERE id=:id";
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':id' => $this->_id));

				header("Location: entity.php");	
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Fonction d'affichage du formulaire de modification d'une entité, l'objet et ces attributs on été crée au préalable ***/

	public function showModif() {

		/*** On a récupéré les données actuelles de l'entité pour afficher les valeurs actuelle en défaut dans le formulaire ***/

		echo"<div id=\"body_content\">
					<div class=\"body\">
					<p> Modification de l'entité : " . $this->_shortName . "</p>
					<form action=\"entity.php?modif_id=" . $this->_id . "\" method=\"POST\">
						<label>Nom de l'entité : </label><input name=\"complete_name\" type=\"text\" class=\"complete_name\" placeholder=\"Nom Complete\" value=\"" . $this->_name . "\" required=\"required\" title=\"Nom complet de l'entité\"><br />
						<label>Nom court de l'entité : </label><input name=\"short_name\" type=\"text\" class=\"short_name\" placeholder=\"Nom Court\" value=\"" . $this->_shortName . "\" required=\"required\" title=\"Nom court de l'entité\"><br />
						<input type=\"submit\" value=\"Modifier\">
					</form>

				-><a href=\"entity.php\"> retour</a>
					</div>
				</div>";
	}

	/*** Fonction de modification d'entité en BDD, l'objet et ces attributs on été crée au préalable ***/

	public function modifEntity($db) {

		try {
			if($db != null) {

				$query = "SELECT id FROM entity WHERE short_name=:short_name AND id!=:id"; // Si le nom court d'entité n'existe pas déjà (sauf sur la ligne correspondant à l'ID actuelle de l'entité)
				$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':short_name' => $this->_shortName, ':id' => $this->_id));
			

				if(count($datas) == 0) {

					/*** On modifie l'entité ***/

					$query = "UPDATE entity SET name=:complete_name, short_name=:short_name WHERE id=:id";
					$sth = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$sth->execute(array(':complete_name' => $this->_name, ':short_name' => $this->_shortName, ':id' => $this->_id));

					header("Location: entity.php");
				}
				else
					echo "<font color=\"FE2E2E\">Ce nom d'entité existe déjà</font>";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}

	/*** Fonction pour récupérer l'ID de l'entité en BDD correspondant au nom court de l'entité
	     On passe donc le nom court de l'entité voulue en attribut de l'objet, on retournera l'ID
	     correspondante ***/

	public function retrieveEntityID($db) {

		try {
			if($db != null) {

				$query_id_entity = "SELECT id FROM entity WHERE short_name=:entity";
				$sth = $db->prepare($query_id_entity, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				$sth->execute(array(':entity' => $this->_shortName));
				$datas = $sth->fetchAll();

				if(count($datas) > 0) {

					foreach($datas as $data) {

						return $data['id'];		
					}
				}
				else
					echo "<font color=\"FE2E2E\">l'entité mentionné n'est pas présente dans la base</font>";
			}
		}
		catch(Exception $e) { // Catch des erreurs et écriture dans le fichier de log
				
			require('error_log.php');
		}
	}
}

class EntityArray {

	public static $entity_array = array(); // Création du tableau d'objet

	public static function addEntity(Entity $entity) { // Ajout d'une entité au tableau des entités

		if(!isset(self::$entity_array[$entity->_id])) {

			self::$entity_array[$entity->_id] = $entity;
		}
	}

	public static function setEntityData($id, $name, $shortName) { // On ajoute les données aux attributs de l'objet

		$entity = new Entity($id, $name, $shortName);  // création de l'objet et de ses attributs
		self::addEntity($entity); // et on ajoute l'objet au tableau
	}

	public static function showData() { // Affichage du tableau d'objet

		foreach(self::$entity_array as $id => $entity) {

			// Pour chaque ligne on affiche chaque attribut correspondant au colonne du tableau

			echo "<tr><td>" . $entity->_name . "</td><td>" . $entity->_shortName . "</td><td><a href=\"entity.php?action=modifier&id=" . $entity->_id . "\">modifier</a></td><td><a href=\"entity.php?action=supprimer&id=" . $entity->_id . "\">supprimer</a></td></tr>";

		}
	}

	public static function showDataOneRow($id_entity) {

		foreach(self::$entity_array as $id => $entity) {

			if($entity->_id == $id_entity) {

				echo "<tr><td>" . $entity->_name . "</td><td>" . $entity->_shortName . "</td><td><a href=\"entity.php?action=modifier&id=" . $entity->_id . "\">modifier</a></td><td><a href=\"entity.php?action=supprimer&id=" . $entity->_id . "\">supprimer</a></td></tr>";
			}
		}
	}

	public static function showModif($db, $entityShortName) {

		foreach(self::$entity_array as $id => $entity) { // Pour chaque entité du tableau d'entité

			if($entity->_shortName == $entityShortName) // Si l'entité actuelle du tableau correspond à l'entité passé en paramètre, on met la met en valeur par défaut du bandeau déroulant
				echo "<option selected=\"selected\">" . $entity->_shortName . "</option>";
			else
				echo "<option>" . $entity->_shortName. "</option>"; // Sinon en option simple du bandeau déroulant
		}
	}
}

?>
